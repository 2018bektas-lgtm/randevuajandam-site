<?php

namespace App\Support;

use App\Models\Paket;
use App\Models\PaketOzelligi;
use Illuminate\Support\Collection;

class PaketOzellikKatalogu
{
    /**
     * Bireysel karşılaştırmada gösterilmeyecek (klinik-özel) kodlar.
     *
     * @return list<string>
     */
    public static function bireyselHaricKodlar(): array
    {
        return ['klinik_web_sitesi'];
    }

    /**
     * Klinik paket bayrakları — sistem_ozellik pivot’undan bağımsız satırlar.
     *
     * @return list<array{kod:string,ad:string,aciklama:?string,grup:string,sira:int}>
     */
    public static function klinikBayrakSatirlari(): array
    {
        return [
            ['kod' => 'flag:hasta_havuzu', 'ad' => 'Ortak hasta havuzu', 'aciklama' => 'Klinik içi ortak hasta kaydı.', 'grup' => 'Klinik paneli', 'sira' => 10],
            ['kod' => 'flag:toplu_randevu', 'ad' => 'Toplu randevu onay / red', 'aciklama' => 'Birden fazla talebi toplu yönetme.', 'grup' => 'Klinik paneli', 'sira' => 20],
            ['kod' => 'flag:merkezi_finans', 'ad' => 'Merkezi finans + muhasebeci', 'aciklama' => 'Klinik geneli gelir-gider ve muhasebeci girişi.', 'grup' => 'Klinik paneli', 'sira' => 30],
            ['kod' => 'flag:raporlama', 'ad' => 'Performans / hakediş raporları', 'aciklama' => 'Klinik raporlama ve hakediş.', 'grup' => 'Klinik paneli', 'sira' => 40],
            ['kod' => 'flag:klinik_web', 'ad' => 'Klinik web sitesi', 'aciklama' => 'Çok hekimli klinik vitrin sitesi.', 'grup' => 'Klinik paneli', 'sira' => 50],
            ['kod' => 'flag:domain', 'ad' => 'Domain dahil', 'aciklama' => 'Web sitesi domaini pakete dahil.', 'grup' => 'Klinik paneli', 'sira' => 60],
        ];
    }

    /**
     * Paket koleksiyonuna göre vitrin matrisi (yalnızca en az bir pakette olan özellikler).
     *
     * @param  Collection<int, Paket>|\Illuminate\Database\Eloquent\Collection<int, Paket>  $paketler
     * @return Collection<string, Collection<int, object>>
     */
    public static function matrisIcinGruplu($paketler, string $tur = 'bireysel'): Collection
    {
        $paketler = collect($paketler);
        $rows = collect();

        if ($tur === 'klinik') {
            foreach (self::klinikBayrakSatirlari() as $flag) {
                // En az bir pakette bayrak açıksa satırı göster
                $any = $paketler->contains(fn (Paket $p) => self::paketBayrakVar($p, $flag['kod']));
                if (! $any) {
                    continue;
                }
                $rows->push((object) [
                    'kod' => $flag['kod'],
                    'ad' => $flag['ad'],
                    'aciklama' => $flag['aciklama'],
                    'grup' => $flag['grup'],
                    'sira' => $flag['sira'],
                    'vitrin_mi' => true,
                    'is_flag' => true,
                ]);
            }
        }

        // Bu tür paketlerde gerçekten atanmış özellik kodları
        $kodlar = $paketler
            ->flatMap(function (Paket $p) {
                if ($p->relationLoaded('sistemOzellikleri')) {
                    return $p->sistemOzellikleri->pluck('kod');
                }

                return $p->sistemOzellikleri()->pluck('kod');
            })
            ->filter()
            ->unique()
            ->values();

        if ($tur === 'bireysel') {
            $kodlar = $kodlar->reject(fn ($k) => in_array($k, self::bireyselHaricKodlar(), true));
        }

        // Klinik matrisinde klinik_web_sitesi zaten bayrak olarak var; çift satır olmasın
        if ($tur === 'klinik') {
            $kodlar = $kodlar->reject(fn ($k) => $k === 'klinik_web_sitesi');
        }

        if ($kodlar->isNotEmpty()) {
            $ozellikler = PaketOzelligi::query()
                ->whereIn('kod', $kodlar->all())
                ->where('vitrin_mi', true)
                ->orderBy('grup')
                ->orderBy('sira')
                ->orderBy('ad')
                ->get();

            foreach ($ozellikler as $o) {
                $rows->push((object) [
                    'kod' => $o->kod,
                    'ad' => $o->ad,
                    'aciklama' => $o->aciklama,
                    'grup' => $o->grup ?: 'Genel',
                    'sira' => (int) $o->sira,
                    'vitrin_mi' => true,
                    'is_flag' => false,
                ]);
            }
        }

        return $rows
            ->groupBy(fn ($r) => $r->grup ?: 'Genel')
            ->map(fn (Collection $g) => $g->sortBy([['sira', 'asc'], ['ad', 'asc']])->values());
    }

    public static function paketBayrakVar(Paket $paket, string $kod): bool
    {
        return match ($kod) {
            'flag:hasta_havuzu' => (bool) ($paket->hasta_havuzu_mi ?? false),
            'flag:toplu_randevu' => (bool) ($paket->toplu_randevu_mi ?? false),
            'flag:merkezi_finans' => (bool) ($paket->merkezi_finans_mi ?? false),
            'flag:raporlama' => (bool) ($paket->raporlama_mi ?? false),
            'flag:klinik_web' => (bool) ($paket->domain_dahil_mi ?? false)
                || (method_exists($paket, 'hasFeature') && $paket->hasFeature('klinik_web_sitesi')),
            'flag:domain' => (bool) ($paket->domain_dahil_mi ?? false),
            default => false,
        };
    }

    public static function paketMatrisVar(Paket $paket, object|string $ozellik): bool
    {
        $kod = is_object($ozellik) ? (string) ($ozellik->kod ?? '') : (string) $ozellik;
        if ($kod === '') {
            return false;
        }
        if (str_starts_with($kod, 'flag:')) {
            return self::paketBayrakVar($paket, $kod);
        }

        return method_exists($paket, 'hasFeature')
            ? $paket->hasFeature($kod)
            : false;
    }

    /**
     * Config kataloğunu DB'ye yaz (idempotent).
     *
     * @return Collection<string, PaketOzelligi> kod => model
     */
    public static function sync(): Collection
    {
        $map = collect();
        foreach (config('paket_ozellikleri.katalog', []) as $row) {
            $model = PaketOzelligi::updateOrCreate(
                ['kod' => $row['kod']],
                [
                    'ad' => $row['ad'],
                    'aciklama' => $row['aciklama'] ?? null,
                    'grup' => $row['grup'] ?? 'Genel',
                    'sira' => (int) ($row['sira'] ?? 0),
                    'vitrin_mi' => (bool) ($row['vitrin'] ?? true),
                ]
            );
            $map[$row['kod']] = $model;
        }

        return $map;
    }

    /**
     * Gruplu liste (yönetim formu).
     *
     * @return Collection<string, Collection<int, PaketOzelligi>>
     */
    public static function gruplu(): Collection
    {
        self::sync();

        return PaketOzelligi::query()
            ->orderBy('grup')
            ->orderBy('sira')
            ->orderBy('ad')
            ->get()
            ->groupBy(fn (PaketOzelligi $o) => $o->grup ?: 'Genel');
    }

    /**
     * Seçili kodlardan vitrin özellikleri (metin listesi).
     *
     * @param  list<string>  $kodlar
     * @return list<string>
     */
    public static function vitrinMetinleri(array $kodlar, ?int $smsKontor = null, ?int $maxRandevu = null, ?int $maxHasta = null): array
    {
        $items = [];
        if ($maxRandevu !== null) {
            $items[] = $maxRandevu > 0
                ? "En fazla {$maxRandevu} randevu"
                : 'Randevu limiti yok';
        }
        if ($maxHasta !== null) {
            $items[] = $maxHasta > 0
                ? "En fazla {$maxHasta} hasta"
                : 'Limitsiz hasta';
        }
        if ($smsKontor !== null && $smsKontor > 0) {
            $items[] = 'SMS hatırlatma: aylık '.number_format($smsKontor, 0, ',', '.').' kontör';
        }

        $ozellikler = PaketOzelligi::query()
            ->whereIn('kod', $kodlar)
            ->where('vitrin_mi', true)
            ->orderBy('grup')
            ->orderBy('sira')
            ->get();

        foreach ($ozellikler as $o) {
            $items[] = $o->ad;
        }

        return array_values(array_unique($items));
    }
}
