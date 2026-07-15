<?php

namespace App\Http\Controllers;

use App\Http\Requests\Yonetim\YoneticiStoreRequest;
use App\Http\Requests\Yonetim\YoneticiUpdateRequest;
use App\Models\Yonetici;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class YoneticiController extends Controller
{
    /**
     * Display a listing of the administrators.
     */
    public function index()
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();
        $yoneticiler = Yonetici::orderBy('id', 'desc')->get();

        return view('yonetim.yoneticiler.index', compact('yonetici', 'yoneticiler'));
    }

    /**
     * Show the form for creating a new administrator.
     */
    public function create()
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();

        return view('yonetim.yoneticiler.ekle', compact('yonetici'));
    }

    /**
     * Store a newly created administrator in storage.
     */
    public function store(YoneticiStoreRequest $request)
    {
        Yonetici::create([
            'ad_soyad' => $request->ad_soyad,
            'e_posta' => $request->e_posta,
            'sifre' => Hash::make($request->sifre),
            'telefon' => $request->telefon,
            'aktif_mi' => $request->has('aktif_mi'),
        ]);

        return redirect()->route('yonetim.yoneticiler.index')->with('basarili', 'Yönetici başarıyla eklendi.');
    }

    /**
     * Show the form for editing the specified administrator.
     */
    public function edit($id)
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();
        $hedefYonetici = Yonetici::findOrFail($id);

        return view('yonetim.yoneticiler.duzenle', compact('yonetici', 'hedefYonetici'));
    }

    /**
     * Update the specified administrator in storage.
     */
    public function update(YoneticiUpdateRequest $request, $id)
    {
        $hedefYonetici = Yonetici::findOrFail($id);

        $data = [
            'ad_soyad' => $request->ad_soyad,
            'e_posta' => $request->e_posta,
            'telefon' => $request->telefon,
        ];

        // Do not allow self-deactivation
        if ($hedefYonetici->id === Auth::guard('yonetici')->id()) {
            $data['aktif_mi'] = true;
        } else {
            $data['aktif_mi'] = $request->has('aktif_mi');
        }

        if ($request->filled('sifre')) {
            $data['sifre'] = Hash::make($request->sifre);
        }

        $hedefYonetici->update($data);

        return redirect()->route('yonetim.yoneticiler.index')->with('basarili', 'Yönetici başarıyla güncellendi.');
    }

    /**
     * Remove the specified administrator from storage.
     */
    public function destroy($id)
    {
        $hedefYonetici = Yonetici::findOrFail($id);

        // Do not allow self-deletion
        if ($hedefYonetici->id === Auth::guard('yonetici')->id()) {
            return back()->withErrors(['hata' => 'Kendi yöneticiliğinizi silemezsiniz!']);
        }

        $hedefYonetici->delete();

        return redirect()->route('yonetim.yoneticiler.index')->with('basarili', 'Yönetici başarıyla silindi.');
    }

    /**
     * Toggle the administrator status.
     */
    public function toggleDurum($id)
    {
        $hedefYonetici = Yonetici::findOrFail($id);

        // Do not allow self-deactivation
        if ($hedefYonetici->id === Auth::guard('yonetici')->id()) {
            return back()->withErrors(['hata' => 'Kendi yöneticiliğinizi askıya alamazsınız!']);
        }

        $hedefYonetici->update([
            'aktif_mi' => ! $hedefYonetici->aktif_mi,
        ]);

        return redirect()->route('yonetim.yoneticiler.index')->with('basarili', 'Yönetici durumu güncellendi.');
    }
}
