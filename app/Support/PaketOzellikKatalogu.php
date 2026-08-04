<?php

namespace App\Support;

use App\Models\PaketOzelligi;
use Illuminate\Support\Collection;

class PaketOzellikKatalogu
{
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
