<?php

namespace App\Http\Controllers;

use App\Models\Doktor;
use App\Models\EkUrunOdeme;
use App\Models\Klinik;
use App\Models\Paket;
use App\Models\UyelikOdeme;
use App\Models\Yonetici;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class YonetimRaporController extends Controller
{
    public function paketDonusum()
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();

        $vitrinIds = Paket::query()
            ->where('tur', 'bireysel')
            ->where(function ($q) {
                $q->where('aylik_fiyat', '<=', 0)
                    ->orWhere('ad', 'like', '%Vitrin%')
                    ->orWhere('ad', 'like', '%Ücretsiz%');
            })
            ->pluck('id');

        $ucretsizAktif = Doktor::query()
            ->where('aktif_mi', true)
            ->whereIn('paket_id', $vitrinIds)
            ->count();

        $ucretliAktif = Doktor::query()
            ->where('aktif_mi', true)
            ->whereNotNull('paket_id')
            ->whereNotIn('paket_id', $vitrinIds)
            ->count();

        $toplamAktif = max(1, $ucretsizAktif + $ucretliAktif);
        $donusumOrani = round(100 * $ucretliAktif / $toplamAktif, 1);

        // Son 90 günde ilk ücretli ödemesi olan (Vitrin'den gelen kabaca)
        $son90 = now()->subDays(90);
        $yeniUcretli = UyelikOdeme::query()
            ->where('durum', 'onaylandi')
            ->where('onaylandi_at', '>=', $son90)
            ->whereNotNull('doktor_id')
            ->whereHas('paket', fn ($p) => $p->where('aylik_fiyat', '>', 0))
            ->distinct('doktor_id')
            ->count('doktor_id');

        $paketDagilim = Doktor::query()
            ->where('aktif_mi', true)
            ->whereNotNull('paket_id')
            ->select('paket_id', DB::raw('count(*) as c'))
            ->groupBy('paket_id')
            ->get()
            ->map(function ($row) {
                $row->paket = Paket::find($row->paket_id);

                return $row;
            });

        // Yükseltme proxy: son 90 günde birden fazla onaylı ödeme farklı paket
        $yukseltme = UyelikOdeme::query()
            ->where('durum', 'onaylandi')
            ->where('onaylandi_at', '>=', $son90)
            ->select('doktor_id', DB::raw('COUNT(DISTINCT paket_id) as paket_say'))
            ->groupBy('doktor_id')
            ->having('paket_say', '>', 1)
            ->count();

        $smsSatis = EkUrunOdeme::query()
            ->where('tip', 'sms_kontor')
            ->where('durum', 'odendi')
            ->where('onaylandi_at', '>=', $son90)
            ->selectRaw('COUNT(*) as adet, COALESCE(SUM(tutar),0) as ciro')
            ->first();

        $personelSatis = EkUrunOdeme::query()
            ->where('tip', 'personel_koltuk')
            ->where('durum', 'odendi')
            ->where('onaylandi_at', '>=', $son90)
            ->selectRaw('COUNT(*) as adet, COALESCE(SUM(tutar),0) as ciro')
            ->first();

        $hedefMin = 8;
        $hedefMax = 10;
        $hedefDurum = $donusumOrani >= $hedefMin
            ? ($donusumOrani >= $hedefMax ? 'hedef_ustu' : 'hedefte')
            : 'hedef_alti';

        $klinikSayisi = Klinik::where('aktif_mi', true)->count();

        return view('yonetim.raporlar.paket-donusum', compact(
            'yonetici',
            'ucretsizAktif',
            'ucretliAktif',
            'donusumOrani',
            'yeniUcretli',
            'paketDagilim',
            'yukseltme',
            'smsSatis',
            'personelSatis',
            'hedefMin',
            'hedefMax',
            'hedefDurum',
            'klinikSayisi'
        ));
    }
}
