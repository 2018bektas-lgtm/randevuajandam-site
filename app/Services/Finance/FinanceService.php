<?php

namespace App\Services\Finance;

use App\Models\Doktor;
use Illuminate\Support\Facades\DB;

/**
 * Ortak finans hesapları (hekim / klinik hakediş / özet).
 */
class FinanceService
{
    /**
     * Dönem içinde hekimin tahsil ettiği tutar (iptal hariç).
     * Hakediş ve klinik özet aynı motoru kullanmalı.
     */
    public function doctorCollectedRevenue(int $doktorId, string $baslangic, string $bitis): float
    {
        return (float) DB::table('odemeler')
            ->where('doktor_id', $doktorId)
            ->whereNull('deleted_at')
            ->where('durum', '!=', 'iptal')
            ->whereDate('odeme_tarihi', '>=', $baslangic)
            ->whereDate('odeme_tarihi', '<=', $bitis)
            ->sum('odenen_tutar');
    }

    /**
     * Hakediş satırı için toplam/komisyon/net.
     *
     * @return array{toplam_gelir: float, komisyon_tutari: float, net_hakedis: float}
     */
    public function settlementAmounts(int $doktorId, string $baslangic, string $bitis, float $komisyonOrani): array
    {
        $toplamGelir = $this->doctorCollectedRevenue($doktorId, $baslangic, $bitis);
        $komisyonTutari = round(($toplamGelir * $komisyonOrani) / 100, 2);
        $netHakedis = round($toplamGelir - $komisyonTutari, 2);

        return [
            'toplam_gelir' => $toplamGelir,
            'komisyon_tutari' => $komisyonTutari,
            'net_hakedis' => $netHakedis,
        ];
    }

    /** Legacy gider enum → güvenli değer */
    public function normalizeGiderKategori(?string $kategori): string
    {
        $allowed = ['kira', 'personel', 'malzeme', 'ekipman', 'vergi', 'sigorta', 'diger'];
        $k = strtolower(trim((string) $kategori));

        return in_array($k, $allowed, true) ? $k : 'diger';
    }
}
