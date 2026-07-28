<?php

namespace App\Http\Controllers;

use App\Http\Requests\Yonetim\PaketStoreRequest;
use App\Models\Doktor;
use App\Models\DomainOrder;
use App\Models\Klinik;
use App\Models\Paket;
use App\Models\UyelikOdeme;
use App\Models\Yonetici;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class PaketController extends Controller
{
    /**
     * Display a listing of the subscription packages.
     */
    public function index()
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();
        $paketler = Paket::orderBy('id', 'desc')->get();

        return view('yonetim.paketler.index', compact('yonetici', 'paketler'));
    }

    /**
     * Show the form for creating a new subscription package.
     */
    public function create()
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();

        return view('yonetim.paketler.ekle', compact('yonetici'));
    }

    /**
     * Store a newly created subscription package in storage.
     */
    public function store(PaketStoreRequest $request)
    {
        $ozellikler = array_values(array_filter($request->input('ozellikler', [])));

        Paket::create([
            'ad' => $request->ad,
            'tur' => $request->tur,
            'aciklama' => $request->aciklama,
            'aylik_fiyat' => $request->aylik_fiyat,
            'aylik_indirimli_fiyat' => $request->aylik_indirimli_fiyat,
            'yillik_fiyat' => $request->yillik_fiyat,
            'yillik_indirimli_fiyat' => $request->yillik_indirimli_fiyat,
            'ek_doktor_aylik_fiyat' => $request->tur === 'klinik' ? $request->ek_doktor_aylik_fiyat : null,
            'ek_doktor_yillik_fiyat' => $request->tur === 'klinik' ? $request->ek_doktor_yillik_fiyat : null,
            'ozellikler' => $ozellikler,
            'aktif_mi' => $request->has('aktif_mi'),
            'max_doktor_sayisi' => $request->tur === 'klinik' ? $request->max_doktor_sayisi : null,
            'max_personel_sayisi' => $request->tur === 'klinik' ? $request->max_personel_sayisi : null,
            'merkezi_finans_mi' => $request->tur === 'klinik' && $request->has('merkezi_finans_mi'),
            'toplu_randevu_mi' => $request->tur === 'klinik' && $request->has('toplu_randevu_mi'),
            'raporlama_mi' => $request->tur === 'klinik' && $request->has('raporlama_mi'),
            'hasta_havuzu_mi' => $request->tur === 'klinik' && $request->has('hasta_havuzu_mi'),
            'sira' => $request->sira ?? 0,
            'one_cikan_mi' => $request->boolean('one_cikan_mi'),
            'etiket' => $request->filled('etiket') ? trim((string) $request->etiket) : null,
            'etiket_stil' => $request->input('etiket_stil') ?: null,
        ]);

        return redirect()->route('yonetim.paketler.index')->with('basarili', 'Paket başarıyla oluşturuldu.');
    }

    /**
     * Show the form for editing the specified subscription package.
     */
    public function edit($id)
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();
        $paket = Paket::findOrFail($id);

        return view('yonetim.paketler.duzenle', compact('yonetici', 'paket'));
    }

    /**
     * Update the specified subscription package in storage.
     */
    public function update(PaketStoreRequest $request, $id)
    {
        $paket = Paket::findOrFail($id);

        $ozellikler = array_values(array_filter($request->input('ozellikler', [])));

        $paket->update([
            'ad' => $request->ad,
            'tur' => $request->tur,
            'aciklama' => $request->aciklama,
            'aylik_fiyat' => $request->aylik_fiyat,
            'aylik_indirimli_fiyat' => $request->aylik_indirimli_fiyat,
            'yillik_fiyat' => $request->yillik_fiyat,
            'yillik_indirimli_fiyat' => $request->yillik_indirimli_fiyat,
            'ek_doktor_aylik_fiyat' => $request->tur === 'klinik' ? $request->ek_doktor_aylik_fiyat : null,
            'ek_doktor_yillik_fiyat' => $request->tur === 'klinik' ? $request->ek_doktor_yillik_fiyat : null,
            'ozellikler' => $ozellikler,
            'aktif_mi' => $request->has('aktif_mi'),
            'max_doktor_sayisi' => $request->tur === 'klinik' ? $request->max_doktor_sayisi : null,
            'max_personel_sayisi' => $request->tur === 'klinik' ? $request->max_personel_sayisi : null,
            'merkezi_finans_mi' => $request->tur === 'klinik' && $request->has('merkezi_finans_mi'),
            'toplu_randevu_mi' => $request->tur === 'klinik' && $request->has('toplu_randevu_mi'),
            'raporlama_mi' => $request->tur === 'klinik' && $request->has('raporlama_mi'),
            'hasta_havuzu_mi' => $request->tur === 'klinik' && $request->has('hasta_havuzu_mi'),
            'sira' => $request->sira ?? 0,
            'iyzico_plan_aylik' => $request->input('iyzico_plan_aylik') ?: null,
            'iyzico_plan_yillik' => $request->input('iyzico_plan_yillik') ?: null,
            'deneme_gun' => $request->filled('deneme_gun') ? (int) $request->deneme_gun : null,
            'domain_dahil_mi' => $request->boolean('domain_dahil_mi'),
            'domain_dahil_yil' => (int) ($request->input('domain_dahil_yil') ?: 1),
            'domain_dahil_tlds' => $request->filled('domain_dahil_tlds')
                ? array_values(array_filter(array_map('trim', explode(',', (string) $request->domain_dahil_tlds))))
                : null,
            'one_cikan_mi' => $request->boolean('one_cikan_mi'),
            'etiket' => $request->filled('etiket') ? trim((string) $request->etiket) : null,
            'etiket_stil' => $request->input('etiket_stil') ?: null,
        ]);

        return redirect()->route('yonetim.paketler.index')->with('basarili', 'Paket başarıyla güncellendi.');
    }

    /**
     * Remove the specified subscription package from storage.
     *
     * Ödeme geçmişi (uyelik_odemeleri) pakete FK ile bağlıdır; kayıt varken silinemez.
     * Bu durumda 500 yerine anlaşılır uyarı + pasife alma önerisi gösterilir.
     */
    public function destroy($id)
    {
        $paket = Paket::findOrFail($id);

        $engeller = [];

        $doktorSayisi = Doktor::where('paket_id', $paket->id)->count();
        if ($doktorSayisi > 0) {
            $engeller[] = "{$doktorSayisi} doktor bu pakete bağlı";
        }

        $kayitNiyet = Doktor::where('kayit_paket_id', $paket->id)->count();
        if ($kayitNiyet > 0) {
            $engeller[] = "{$kayitNiyet} doktor kayıt sırasında bu paketi seçmiş";
        }

        $klinikSayisi = Klinik::where('paket_id', $paket->id)->count();
        if ($klinikSayisi > 0) {
            $engeller[] = "{$klinikSayisi} klinik bu pakete bağlı";
        }

        $odemeSayisi = UyelikOdeme::where('paket_id', $paket->id)->count();
        if ($odemeSayisi > 0) {
            $engeller[] = "{$odemeSayisi} üyelik ödemesi kaydı var (geçmiş silinemez)";
        }

        if (Schema::hasTable('domain_orders')) {
            $domainSayisi = DomainOrder::where('paket_id', $paket->id)->count();
            if ($domainSayisi > 0) {
                $engeller[] = "{$domainSayisi} domain siparişi bu pakete bağlı";
            }
        }

        if ($engeller !== []) {
            return back()->withErrors([
                'hata' => 'Bu paket silinemez: '.implode('; ', $engeller)
                    .'. Ödeme ve üyelik geçmişi korunur. Satışta göstermemek için paketi «Pasif» yapın.',
            ]);
        }

        try {
            $paket->delete();
        } catch (QueryException $e) {
            report($e);

            return back()->withErrors([
                'hata' => 'Paket silinemedi: veritabanında bağlı kayıtlar var. Paketi pasife almanız yeterli.',
            ]);
        }

        return redirect()->route('yonetim.paketler.index')->with('basarili', 'Paket başarıyla silindi.');
    }

    /**
     * Toggle the subscription package status.
     */
    public function toggleDurum($id)
    {
        $paket = Paket::findOrFail($id);
        $paket->update([
            'aktif_mi' => ! $paket->aktif_mi,
        ]);

        return redirect()->route('yonetim.paketler.index')->with('basarili', 'Paket durumu güncellendi.');
    }
}
