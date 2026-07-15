<?php

namespace App\Http\Controllers;

use App\Http\Requests\Yonetim\DoktorUpdateRequest;
use App\Models\Doktor;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Paket;
use App\Models\Yonetici;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DoktorController extends Controller
{
    /**
     * Display a listing of the doctors.
     */
    public function index()
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();
        $doktorlar = Doktor::with('paket', 'il', 'ilce')->orderBy('id', 'desc')->get();

        return view('yonetim.doktorlar.index', compact('yonetici', 'doktorlar'));
    }

    /**
     * Show the form for editing the specified doctor.
     */
    public function edit($id)
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();
        $doktor = Doktor::with('il', 'ilce')->findOrFail($id);
        $paketler = Paket::where('aktif_mi', true)->get();

        return view('yonetim.doktorlar.duzenle', compact('yonetici', 'doktor', 'paketler'));
    }

    /**
     * Update the specified doctor in storage.
     */
    public function update(DoktorUpdateRequest $request, $id)
    {
        $doktor = Doktor::findOrFail($id);

        $ilModel = Il::where('ad', $request->il)->first();
        $ilceModel = Ilce::where('il_id', $ilModel?->id)->where('ad', $request->ilce)->first();

        $data = [
            'unvan' => $request->unvan,
            'ad_soyad' => $request->ad_soyad,
            'e_posta' => $request->e_posta,
            'telefon' => $request->telefon,
            'il_id' => $ilModel?->id,
            'ilce_id' => $ilceModel?->id,
            'tur' => $request->tur,
            'klinik_adi' => $request->klinik_adi,
            'paket_id' => $request->paket_id,
            'odeme_periyodu' => $request->odeme_periyodu,
            'uyelik_baslangic' => $request->uyelik_baslangic,
            'uyelik_bitis' => $request->uyelik_bitis,
            'aktif_mi' => $request->has('aktif_mi'),
            'platformda_gorunur' => $request->has('platformda_gorunur'),
        ];

        if ($request->filled('sifre')) {
            $data['sifre'] = Hash::make($request->sifre);
        }

        $doktor->update($data);

        return redirect()->route('yonetim.doktorlar.index')->with('basarili', 'Doktor bilgileri başarıyla güncellendi.');
    }

    /**
     * Remove the specified doctor from storage.
     */
    public function destroy($id)
    {
        $doktor = Doktor::findOrFail($id);
        $doktor->delete();

        return redirect()->route('yonetim.doktorlar.index')->with('basarili', 'Doktor hesabı sistemden silindi.');
    }

    /**
     * Toggle the doctor status.
     */
    public function toggleDurum($id)
    {
        $doktor = Doktor::findOrFail($id);
        $doktor->update([
            'aktif_mi' => ! $doktor->aktif_mi,
        ]);

        return redirect()->route('yonetim.doktorlar.index')->with('basarili', 'Doktor durumu güncellendi.');
    }
}
