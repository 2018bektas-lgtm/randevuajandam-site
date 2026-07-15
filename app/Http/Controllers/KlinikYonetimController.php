<?php

namespace App\Http\Controllers;

use App\Models\Klinik;
use App\Models\Paket;
use App\Models\Yonetici;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KlinikYonetimController extends Controller
{
    /**
     * Display a listing of the clinics.
     */
    public function index()
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();
        $klinikler = Klinik::with('sahipDoktor', 'paket', 'il', 'ilce')->orderBy('id', 'desc')->get();

        return view('yonetim.klinikler.index', compact('yonetici', 'klinikler'));
    }

    /**
     * Show the form for editing the specified clinic.
     */
    public function edit($id)
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();
        $klinik = Klinik::findOrFail($id);
        $paketler = Paket::klinik()->where('aktif_mi', true)->get();

        return view('yonetim.klinikler.duzenle', compact('yonetici', 'klinik', 'paketler'));
    }

    /**
     * Update the specified clinic in storage.
     */
    public function update(Request $request, $id)
    {
        $klinik = Klinik::findOrFail($id);

        $request->validate([
            'ad' => 'required|string|max:255',
            'paket_id' => 'required|exists:paketler,id',
            'uyelik_baslangic' => 'nullable|date',
            'uyelik_bitis' => 'nullable|date',
            'max_doktor_sayisi' => 'required|integer|min:1',
            'telefon' => 'nullable|string|max:50',
            'e_posta' => 'nullable|email|max:100',
            'adres' => 'nullable|string',
        ]);

        $klinik->update([
            'ad' => $request->ad,
            'paket_id' => $request->paket_id,
            'uyelik_baslangic' => $request->uyelik_baslangic,
            'uyelik_bitis' => $request->uyelik_bitis,
            'max_doktor_sayisi' => $request->max_doktor_sayisi,
            'telefon' => $request->telefon,
            'e_posta' => $request->e_posta,
            'adres' => $request->adres,
            'aktif_mi' => $request->has('aktif_mi'),
        ]);

        return redirect()->route('yonetim.klinikler.index')->with('basarili', 'Klinik bilgileri başarıyla güncellendi.');
    }

    /**
     * Remove the specified clinic from storage.
     */
    public function destroy($id)
    {
        $klinik = Klinik::findOrFail($id);

        // Detach all doctors from this clinic (turn them back to individual status)
        $klinik->doktorlar()->update([
            'klinik_id' => null,
            'klinik_rolu' => null,
            'klinik_katilma_tarihi' => null,
            'klinik_aktif_mi' => null,
        ]);

        // Deactivate clinic staff
        $klinik->personeller()->update([
            'aktif_mi' => false,
        ]);

        // Soft delete the clinic
        $klinik->delete();

        return redirect()->route('yonetim.klinikler.index')->with('basarili', 'Klinik başarıyla silindi. Bağlı doktorlar bireysel duruma getirildi.');
    }

    /**
     * Toggle the clinic status.
     */
    public function toggleDurum($id)
    {
        $klinik = Klinik::findOrFail($id);
        $klinik->update([
            'aktif_mi' => ! $klinik->aktif_mi,
        ]);

        return redirect()->route('yonetim.klinikler.index')->with('basarili', 'Klinik durumu güncellendi.');
    }

    /**
     * Remove a doctor from the clinic.
     */
    public function cikar($klinikId, $doktorId)
    {
        $klinik = Klinik::findOrFail($klinikId);
        $doktor = $klinik->doktorlar()->findOrFail($doktorId);

        $doktor->update([
            'klinik_id' => null,
            'klinik_rolu' => null,
            'klinik_katilma_tarihi' => null,
            'klinik_aktif_mi' => null,
        ]);

        return back()->with('basarili', 'Hekim klinikten başarıyla çıkarıldı.');
    }
}
