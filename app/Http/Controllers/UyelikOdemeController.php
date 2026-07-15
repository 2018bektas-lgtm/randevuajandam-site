<?php

namespace App\Http\Controllers;

use App\Models\Il;
use App\Models\Ilce;
use App\Models\Klinik;
use App\Models\UyelikOdeme;
use App\Models\Yonetici;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UyelikOdemeController extends Controller
{
    public function index(Request $request)
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();
        $query = UyelikOdeme::with(['doktor', 'paket', 'onaylayanYonetici'])->latest();

        if (in_array($request->input('durum'), ['beklemede', 'onaylandi'], true)) {
            $query->where('durum', $request->input('durum'));
        }

        $odemeler = $query->paginate(20)->withQueryString();

        return view('yonetim.uyelik_odemeleri.index', compact('yonetici', 'odemeler'));
    }

    public function onayla(int $id)
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();

        DB::transaction(function () use ($id, $yonetici): void {
            $odeme = UyelikOdeme::query()
                ->with(['doktor', 'paket'])
                ->lockForUpdate()
                ->findOrFail($id);

            if ($odeme->durum !== 'beklemede') {
                abort(422, 'Bu havale başvurusu daha önce işleme alınmış.');
            }

            $doktor = $odeme->doktor;
            $paket = $odeme->paket;
            $kurulum = $odeme->kurulum_verisi ?? [];
            $baslangic = now();
            $bitis = $odeme->odeme_periyodu === 'aylik' ? now()->addMonth() : now()->addYear();

            if ($paket->klinikPaketiMi()) {
                $il = Il::find($kurulum['il_id'] ?? null);
                $ilce = Ilce::query()
                    ->where('il_id', $il?->id)
                    ->where('ad', $kurulum['ilce_id'] ?? '')
                    ->first();

                $klinik = Klinik::create([
                    'ad' => $kurulum['klinik_adi'],
                    'sahip_doktor_id' => $doktor->id,
                    'paket_id' => $paket->id,
                    'telefon' => $kurulum['telefon'],
                    'e_posta' => $kurulum['e_posta'] ?? null,
                    'adres' => $kurulum['adres'],
                    'il_id' => $il?->id,
                    'ilce_id' => $ilce?->id,
                    'odeme_periyodu' => $odeme->odeme_periyodu,
                    'uyelik_baslangic' => $baslangic,
                    'uyelik_bitis' => $bitis,
                    'max_doktor_sayisi' => $paket->max_doktor_sayisi ?? 3,
                    'aktif_mi' => true,
                ]);

                $doktor->update([
                    'klinik_id' => $klinik->id,
                    'klinik_rolu' => 'sahip',
                    'klinik_katilma_tarihi' => now(),
                    'klinik_aktif_mi' => true,
                    'tur' => 'klinik',
                ]);
            } else {
                $doktor->update(['tur' => 'bireysel']);
            }

            $doktor->update([
                'paket_id' => $paket->id,
                'odeme_periyodu' => $odeme->odeme_periyodu,
                'uyelik_baslangic' => $baslangic,
                'uyelik_bitis' => $bitis,
                'iyzico_subscription_reference_code' => null,
                'iyzico_subscription_status' => 'BANK_TRANSFER_CONFIRMED',
            ]);

            $odeme->update([
                'durum' => 'onaylandi',
                'onaylandi_at' => now(),
                'onaylayan_yonetici_id' => $yonetici->id,
            ]);
        });

        return back()->with('basarili', 'Havale onaylandı ve üyelik aktifleştirildi.');
    }
}
