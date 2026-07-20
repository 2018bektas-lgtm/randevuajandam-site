<?php

namespace App\Services\Meslek;

use App\Models\Brans;
use App\Models\MeslekProgramEsleme;
use App\Models\Unvan;
use Illuminate\Support\Str;

class MeslekEslesmeService
{
    public function __construct(
        protected float $adEsik = 0.85
    ) {}

    /**
     * TC + ad-soyad + program eşlemesi.
     *
     * @param  array<string, mixed>  $parsed  YokMezunBelgesiParser çıktısı
     * @return array{
     *   tc_ok:bool, ad_ok:bool, ad_skor:float, program_esleme:?MeslekProgramEsleme,
     *   auto_onay_uygun:bool, onerilen_unvan:?string, onerilen_brans:?string,
     *   onerilen_brans_id:?int, nedenler:array<int,string>
     * }
     */
    public function eslestir(string $formAdSoyad, string $formTc, array $parsed): array
    {
        $formTc = preg_replace('/\D/', '', $formTc) ?? '';
        $belgeTc = preg_replace('/\D/', '', (string) ($parsed['tc'] ?? '')) ?? '';
        $tcOk = $formTc !== '' && $formTc === $belgeTc;

        $adSkor = $this->adBenzerlik($formAdSoyad, (string) ($parsed['ad_soyad'] ?? ''));
        $adOk = $adSkor >= $this->adEsik;

        $program = (string) ($parsed['program'] ?? '');
        $esleme = MeslekProgramEsleme::matchProgram($program);

        $unvan = $esleme?->unvan_ad;
        $bransAd = $esleme?->brans_ad;
        $bransId = null;
        if ($bransAd) {
            $brans = Brans::query()
                ->whereRaw('LOWER(ad) = ?', [mb_strtolower($bransAd)])
                ->orWhere('ad', 'like', '%'.$bransAd.'%')
                ->first();
            $bransId = $brans?->id;
            if ($brans) {
                $bransAd = $brans->ad;
            }
        }

        // Unvan tablosunda varsa tam ad
        if ($unvan) {
            $u = Unvan::query()->where('ad', $unvan)->orWhere('ad', 'like', $unvan.'%')->first();
            if ($u) {
                $unvan = $u->ad;
            }
        }

        $nedenler = [];
        $kontroller = [];

        if ($belgeTc === '') {
            $kontroller[] = ['key' => 'tc', 'ok' => false, 'label' => 'T.C. kimlik', 'detay' => 'Belgeden TC okunamadı. 1. adımdaki TC ile manuel kontrol edilecek.'];
            $nedenler[] = 'Belgeden T.C. kimlik okunamadı; kayıt sonrası ekibimiz formdaki TC ile belgeyi karşılaştıracak.';
        } elseif (! $tcOk) {
            $kontroller[] = ['key' => 'tc', 'ok' => false, 'label' => 'T.C. kimlik', 'detay' => 'Formdaki TC ile belgedeki TC uyuşmuyor.'];
            $nedenler[] = 'T.C. kimlik numaranız belgedeki ile uyuşmuyor. Lütfen 1. adımdaki TC’yi kontrol edin.';
        } else {
            $kontroller[] = ['key' => 'tc', 'ok' => true, 'label' => 'T.C. kimlik', 'detay' => 'Formdaki TC ile belge eşleşti.'];
        }

        if (trim((string) ($parsed['ad_soyad'] ?? '')) === '') {
            $kontroller[] = ['key' => 'ad', 'ok' => false, 'label' => 'Ad soyad', 'detay' => 'Belgeden ad soyad okunamadı.'];
            $nedenler[] = 'Belgeden ad soyad okunamadı; kayıt sonrası ekibimiz formdaki ad ile belgeyi karşılaştıracak.';
        } elseif (! $adOk) {
            $kontroller[] = ['key' => 'ad', 'ok' => false, 'label' => 'Ad soyad', 'detay' => 'Benzerlik skoru: '.number_format($adSkor * 100, 0).'% (eşik %'.(int) ($this->adEsik * 100).').'];
            $nedenler[] = 'Ad soyad belgedeki ile yeterince uyuşmuyor (benzerlik %'.number_format($adSkor * 100, 0).'). 1. adımdaki ad soyadı kontrol edin.';
        } else {
            $kontroller[] = ['key' => 'ad', 'ok' => true, 'label' => 'Ad soyad', 'detay' => 'Formdaki ad soyad ile belge eşleşti (%'.number_format($adSkor * 100, 0).').'];
        }

        if (! $esleme) {
            $kontroller[] = ['key' => 'program', 'ok' => false, 'label' => 'Meslek / branş', 'detay' => 'Program platform branş listesinde otomatik eşleşmedi.'];
            $nedenler[] = 'Mezuniyet programınız otomatik branş listemizde net eşleşmedi. Yine de kayda devam edebilirsiniz; talebiniz incelenecektir.';
        } elseif (! $esleme->auto_onay) {
            $kontroller[] = ['key' => 'program', 'ok' => false, 'label' => 'Meslek / branş', 'detay' => 'Program eşleşti ancak otomatik onay kapalı.'];
            $nedenler[] = 'Bu program için otomatik onay kapalı; talebiniz incelenecektir.';
        } else {
            $kontroller[] = ['key' => 'program', 'ok' => true, 'label' => 'Meslek / branş', 'detay' => 'Program branş listemizde: '.($bransAd ?: $unvan ?: 'uygun')];
        }

        $auto = $tcOk && $adOk && $esleme && $esleme->auto_onay;

        return [
            'tc_ok' => $tcOk,
            'ad_ok' => $adOk,
            'ad_skor' => round($adSkor, 4),
            'program_esleme' => $esleme,
            'auto_onay_uygun' => $auto,
            'onerilen_unvan' => $unvan,
            'onerilen_brans' => $bransAd,
            'onerilen_brans_id' => $bransId,
            'nedenler' => $nedenler,
            'kontroller' => $kontroller,
            'sonuc_baslik' => $auto
                ? 'Doğrulama başarılı — otomatik onay uygun'
                : 'Kayıt tamamlanabilir; talebiniz incelenecek',
            'sonuc_ozet' => $auto
                ? 'TC, ad soyad ve mezuniyet programınız uyumlu. Kaydı tamamladığınızda meslek onayı otomatik verilir; paket ödemesine geçebilirsiniz.'
                : 'Bazı kontroller otomatik geçilemedi veya e-Devlet anlık yanıt vermedi. Yine de kaydı tamamlayabilirsiniz. Belgeleriniz ekibimiz tarafından incelenecek; onay sonrası paket ödemesine yönlendirilirsiniz.',
        ];
    }

    public function adBenzerlik(string $a, string $b): float
    {
        $a = $this->normalizeName($a);
        $b = $this->normalizeName($b);
        if ($a === '' || $b === '') {
            return 0.0;
        }
        if ($a === $b) {
            return 1.0;
        }
        similar_text($a, $b, $pct);

        return $pct / 100.0;
    }

    public function normalizeName(string $s): string
    {
        $s = trim($s);
        // Unvan öneklerini at
        $s = preg_replace('/^(PROF\.?\s*DR\.?|DO[CÇ]\.?\s*DR\.?|DR\.?|DT\.?|PSK\.?|DYT\.?|FZT\.?|ECZ\.?)\s+/iu', '', $s) ?? $s;
        $s = strtr(mb_strtoupper($s, 'UTF-8'), [
            'İ' => 'I', 'I' => 'I', 'ı' => 'I', 'i' => 'I',
            'Ş' => 'S', 'ş' => 'S', 'Ğ' => 'G', 'ğ' => 'G',
            'Ü' => 'U', 'ü' => 'U', 'Ö' => 'O', 'ö' => 'O',
            'Ç' => 'C', 'ç' => 'C',
        ]);
        $s = preg_replace('/[^A-Z\s]/', '', $s) ?? $s;
        $s = preg_replace('/\s+/', ' ', $s) ?? $s;

        return trim($s);
    }

    public function mezuniyetSatiri(array $parsed): string
    {
        $parts = array_filter([
            $parsed['universite'] ?? null,
            $parsed['fakulte'] ?? null,
            $parsed['bolum'] ?? ($parsed['program'] ?? null),
            ! empty($parsed['mezuniyet_tarihi'])
                ? (preg_match('/^\d{4}-\d{2}-\d{2}$/', $parsed['mezuniyet_tarihi'])
                    ? date('d.m.Y', strtotime($parsed['mezuniyet_tarihi']))
                    : $parsed['mezuniyet_tarihi'])
                : null,
            ! empty($parsed['diploma_no']) ? 'Dip: '.$parsed['diploma_no'] : null,
        ]);

        return implode(' · ', $parts);
    }
}
