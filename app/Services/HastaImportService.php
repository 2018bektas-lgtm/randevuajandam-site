<?php

namespace App\Services;

use App\Models\Doktor;
use App\Models\Hasta;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Toplu hasta import servisi (CSV).
 *
 * Kabul edilen kolonlar (basliklar case-insensitive, TR karakter tolerans):
 *   ad*, soyad*, telefon*, e-posta (opsiyonel), notlar (opsiyonel)
 *
 * Kurallar:
 *  - En fazla 1000 satir tek dosyada
 *  - E-posta unique — mukerrer bulunan hasta gecerli mail ile eslesirse iliskilendirilir (yeni yaratılmaz)
 *  - Telefon normalize edilir: sadece rakam, 11 haneli 0'la baslayan format
 *  - Ad+Soyad zorunlu, en az 2 karakter
 *  - Bos satirlar atlanır
 *  - Hata satirlari raporlanir, gecerli satirlar kaydedilir (partial success)
 */
class HastaImportService
{
    /** @var array<int, string> */
    private const KABUL_EDILEN_HEADERS = ['ad', 'soyad', 'telefon', 'e_posta', 'eposta', 'e-posta', 'email', 'notlar', 'not'];

    public const MAX_SATIR = 1000;

    /**
     * @return array{
     *   toplam: int,
     *   eklendi: int,
     *   guncellendi: int,
     *   atlanan: int,
     *   hatalar: array<int, array{satir: int, mesaj: string, ham: string}>
     * }
     */
    public function importFromCsv(Doktor $doktor, UploadedFile $file): array
    {
        if (! $file->isValid()) {
            throw new InvalidArgumentException('Yüklenen dosya geçersiz.');
        }
        $uzanti = strtolower($file->getClientOriginalExtension());
        if (! in_array($uzanti, ['csv', 'txt'], true)) {
            throw new InvalidArgumentException('Sadece CSV formatı desteklenir. Excel\'de "CSV UTF-8 (Virgülle ayrılmış)" olarak kaydedin.');
        }

        $handle = fopen($file->getRealPath(), 'r');
        if (! $handle) {
            throw new InvalidArgumentException('Dosya okunamadı.');
        }

        // BOM temizle (Excel UTF-8)
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Delimiter tahmini — ilk satir icerigine gore
        $firstLine = (string) fgets($handle);
        rewind($handle);
        if (substr($bom, 0, 3) === "\xEF\xBB\xBF" || ord($firstLine[0] ?? '') === 0xEF) {
            fread($handle, 3);
        }

        $delimiter = ',';
        if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $delimiter = ';';
        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
            $delimiter = "\t";
        }

        // Header oku
        $headerRow = fgetcsv($handle, 0, $delimiter);
        if (! $headerRow) {
            fclose($handle);
            throw new InvalidArgumentException('Dosya başlığı okunamadı.');
        }

        $headerMap = $this->parseHeaders($headerRow);
        if (! isset($headerMap['ad']) || ! isset($headerMap['soyad']) || ! isset($headerMap['telefon'])) {
            fclose($handle);
            throw new InvalidArgumentException('Zorunlu sütunlar eksik: ad, soyad, telefon. Şablonu indirip düzenleyin.');
        }

        $sonuc = [
            'toplam' => 0,
            'eklendi' => 0,
            'guncellendi' => 0,
            'atlanan' => 0,
            'hatalar' => [],
        ];

        $satirNo = 1; // header 1. satir
        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $satirNo++;
                if ($this->rowEmpty($row)) {
                    continue;
                }
                $sonuc['toplam']++;

                if ($sonuc['toplam'] > self::MAX_SATIR) {
                    $sonuc['hatalar'][] = [
                        'satir' => $satirNo,
                        'mesaj' => 'Bir dosyada en fazla '.self::MAX_SATIR.' satır işlenebilir. Kalan satırlar atlandı.',
                        'ham' => '',
                    ];
                    break;
                }

                $ad = trim((string) ($row[$headerMap['ad']] ?? ''));
                $soyad = trim((string) ($row[$headerMap['soyad']] ?? ''));
                $telefonRaw = trim((string) ($row[$headerMap['telefon']] ?? ''));
                $ePosta = isset($headerMap['e_posta']) ? trim((string) ($row[$headerMap['e_posta']] ?? '')) : '';
                $notlar = isset($headerMap['notlar']) ? trim((string) ($row[$headerMap['notlar']] ?? '')) : '';

                $hata = $this->validateRow($ad, $soyad, $telefonRaw, $ePosta);
                if ($hata) {
                    $sonuc['atlanan']++;
                    $sonuc['hatalar'][] = [
                        'satir' => $satirNo,
                        'mesaj' => $hata,
                        'ham' => implode(' | ', array_map('strval', $row)),
                    ];
                    continue;
                }

                $telefon = $this->normalizePhone($telefonRaw);

                // Var olan hasta: once e-posta, sonra telefon eslesmesi
                $hasta = null;
                if ($ePosta !== '') {
                    $hasta = Hasta::where('e_posta', $ePosta)->first();
                }
                if (! $hasta && $telefon !== '') {
                    $hasta = Hasta::where('telefon', $telefon)->first();
                }

                if ($hasta) {
                    // Mevcut hastaya doktor pivot'u ekle (idempotent)
                    if (! $doktor->hastalar()->where('hasta_id', $hasta->id)->exists()) {
                        $doktor->hastalar()->attach($hasta->id, [
                            'kayit_tarihi' => now()->toDateString(),
                            'kaynak' => 'toplu',
                            'notlar' => $notlar ?: null,
                        ]);
                        $sonuc['guncellendi']++;
                    } else {
                        $sonuc['atlanan']++;
                    }

                    // Klinik hekimiyse klinik havuzuna da ekle
                    if ($doktor->klinik_id && $doktor->klinik) {
                        $doktor->klinik->hastalar()->syncWithoutDetaching([
                            $hasta->id => ['kayit_tarihi' => now()->toDateString()],
                        ]);
                    }
                    continue;
                }

                // Yeni hasta
                try {
                    $hasta = Hasta::create([
                        'ad' => $ad,
                        'soyad' => $soyad,
                        'telefon' => $telefon,
                        'e_posta' => $ePosta !== '' ? $ePosta : $this->generatePlaceholderEmail($telefon),
                        'sifre' => Str::password(16),
                        'aktif_mi' => true,
                    ]);
                } catch (\Throwable $e) {
                    $sonuc['atlanan']++;
                    $sonuc['hatalar'][] = [
                        'satir' => $satirNo,
                        'mesaj' => 'Hasta oluşturulamadı: '.substr($e->getMessage(), 0, 100),
                        'ham' => implode(' | ', array_map('strval', $row)),
                    ];
                    continue;
                }

                $doktor->hastalar()->attach($hasta->id, [
                    'kayit_tarihi' => now()->toDateString(),
                    'kaynak' => 'toplu',
                    'notlar' => $notlar ?: null,
                ]);
                $sonuc['eklendi']++;

                if ($doktor->klinik_id && $doktor->klinik) {
                    $doktor->klinik->hastalar()->syncWithoutDetaching([
                        $hasta->id => ['kayit_tarihi' => now()->toDateString()],
                    ]);
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);
            throw $e;
        }

        fclose($handle);
        return $sonuc;
    }

    /**
     * Header satirini normalize et: kucuk harf, tr karakter degistir, sinonimleri eslestir.
     *
     * @param  array<int, string>  $headerRow
     * @return array<string, int>
     */
    private function parseHeaders(array $headerRow): array
    {
        $map = [];
        foreach ($headerRow as $idx => $baslik) {
            $normalized = $this->normalizeHeader((string) $baslik);
            if (! in_array($normalized, self::KABUL_EDILEN_HEADERS, true)) {
                continue;
            }
            // Sinonimler
            $key = match ($normalized) {
                'eposta', 'e-posta', 'email' => 'e_posta',
                'not' => 'notlar',
                default => $normalized,
            };
            $map[$key] = $idx;
        }
        return $map;
    }

    private function normalizeHeader(string $s): string
    {
        $s = mb_strtolower(trim($s), 'UTF-8');
        $tr = ['ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u', 'İ' => 'i'];
        return strtr($s, $tr);
    }

    /**
     * @param  array<int, string>  $row
     */
    private function rowEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }
        return true;
    }

    private function validateRow(string $ad, string $soyad, string $telefon, string $ePosta): ?string
    {
        if (mb_strlen($ad) < 2) {
            return 'Ad en az 2 karakter olmalı.';
        }
        if (mb_strlen($soyad) < 2) {
            return 'Soyad en az 2 karakter olmalı.';
        }
        if ($telefon === '') {
            return 'Telefon boş olamaz.';
        }
        $tel = preg_replace('/\D/', '', $telefon) ?: '';
        if (strlen($tel) < 10 || strlen($tel) > 13) {
            return 'Telefon geçersiz format (10-13 rakam bekleniyor).';
        }
        if ($ePosta !== '' && ! filter_var($ePosta, FILTER_VALIDATE_EMAIL)) {
            return 'E-posta formatı geçersiz.';
        }
        return null;
    }

    private function normalizePhone(string $t): string
    {
        $t = preg_replace('/\D/', '', $t) ?: '';
        if (str_starts_with($t, '90') && strlen($t) >= 12) {
            $t = '0'.substr($t, 2);
        }
        if (strlen($t) === 10 && str_starts_with($t, '5')) {
            $t = '0'.$t;
        }
        return $t;
    }

    private function generatePlaceholderEmail(string $telefon): string
    {
        $suffix = $telefon !== '' ? $telefon : Str::lower(Str::random(8));
        $email = 'hasta+'.$suffix.'@randevu.local';
        // Uniqueness guard
        while (Hasta::where('e_posta', $email)->exists()) {
            $email = 'hasta+'.$suffix.'.'.Str::lower(Str::random(4)).'@randevu.local';
        }
        return $email;
    }
}
