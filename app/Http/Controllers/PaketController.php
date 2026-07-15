<?php

namespace App\Http\Controllers;

use App\Http\Requests\Yonetim\PaketStoreRequest;
use App\Models\Doktor;
use App\Models\Paket;
use App\Models\Yonetici;
use Illuminate\Support\Facades\Auth;

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
            'ozellikler' => $ozellikler,
            'aktif_mi' => $request->has('aktif_mi'),
            'max_doktor_sayisi' => $request->tur === 'klinik' ? $request->max_doktor_sayisi : null,
            'max_personel_sayisi' => $request->tur === 'klinik' ? $request->max_personel_sayisi : null,
            'merkezi_finans_mi' => $request->tur === 'klinik' && $request->has('merkezi_finans_mi'),
            'toplu_randevu_mi' => $request->tur === 'klinik' && $request->has('toplu_randevu_mi'),
            'raporlama_mi' => $request->tur === 'klinik' && $request->has('raporlama_mi'),
            'hasta_havuzu_mi' => $request->tur === 'klinik' && $request->has('hasta_havuzu_mi'),
            'sira' => $request->sira ?? 0,
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
            'ozellikler' => $ozellikler,
            'aktif_mi' => $request->has('aktif_mi'),
            'max_doktor_sayisi' => $request->tur === 'klinik' ? $request->max_doktor_sayisi : null,
            'max_personel_sayisi' => $request->tur === 'klinik' ? $request->max_personel_sayisi : null,
            'merkezi_finans_mi' => $request->tur === 'klinik' && $request->has('merkezi_finans_mi'),
            'toplu_randevu_mi' => $request->tur === 'klinik' && $request->has('toplu_randevu_mi'),
            'raporlama_mi' => $request->tur === 'klinik' && $request->has('raporlama_mi'),
            'hasta_havuzu_mi' => $request->tur === 'klinik' && $request->has('hasta_havuzu_mi'),
            'sira' => $request->sira ?? 0,
        ]);

        return redirect()->route('yonetim.paketler.index')->with('basarili', 'Paket başarıyla güncellendi.');
    }

    /**
     * Remove the specified subscription package from storage.
     */
    public function destroy($id)
    {
        $paket = Paket::findOrFail($id);

        // Prevent deletion if doctors are subscribed to this package
        $baglıDoktorSayisi = Doktor::where('paket_id', $paket->id)->count();
        if ($baglıDoktorSayisi > 0) {
            return back()->withErrors([
                'hata' => "Bu pakete kayıtlı {$baglıDoktorSayisi} doktor bulunmaktadır. Önce doktorların paketlerini değiştirin.",
            ]);
        }

        $paket->delete();

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
