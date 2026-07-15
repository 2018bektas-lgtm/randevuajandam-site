<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\KlinikDuyuru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KlinikDuyuruController extends Controller
{
    /**
     * Display a listing of clinic announcements.
     */
    public function index()
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        if (!$klinik) {
            return redirect()->route('hekim.panel')->with('hata', 'Kliniğiniz bulunamadı.');
        }

        $duyurular = $klinik->duyurular()->orderBy('created_at', 'desc')->paginate(10);

        return view('klinik.duyurular.index', compact('klinik', 'duyurular'));
    }

    /**
     * Store a newly created announcement.
     */
    public function store(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        if (!$klinik) {
            return back()->with('hata', 'Kliniğiniz bulunamadı.');
        }

        $request->validate([
            'baslik' => 'required|string|max:255',
            'icerik' => 'required|string',
            'onem_derecesi' => 'required|in:genel,onemli,acil',
        ]);

        $duyuru = $klinik->duyurular()->create([
            'baslik' => $request->baslik,
            'icerik' => $request->icerik,
            'onem_derecesi' => $request->onem_derecesi,
            'aktif_mi' => true,
        ]);

        if ($request->onem_derecesi === 'acil') {
            // Notify doctors
            $doktorlar = $klinik->doktorlar()->get();
            \Illuminate\Support\Facades\Notification::send($doktorlar, new \App\Notifications\KlinikDuyuruBildirimi($duyuru));

            // Notify staff
            $personeller = $klinik->personeller()->get();
            \Illuminate\Support\Facades\Notification::send($personeller, new \App\Notifications\KlinikDuyuruBildirimi($duyuru));
        }

        return redirect()->route('hekim.klinik.duyurular.index')->with('basari', 'Duyuru başarıyla oluşturuldu.');
    }

    /**
     * Show the form for editing the specified announcement.
     */
    public function edit($id)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        if (!$klinik) {
            return redirect()->route('hekim.panel')->with('hata', 'Kliniğiniz bulunamadı.');
        }

        $duyuru = $klinik->duyurular()->findOrFail($id);

        return view('klinik.duyurular.duzenle', compact('klinik', 'duyuru'));
    }

    /**
     * Update the specified announcement in storage.
     */
    public function update(Request $request, $id)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        if (!$klinik) {
            return back()->with('hata', 'Kliniğiniz bulunamadı.');
        }

        $duyuru = $klinik->duyurular()->findOrFail($id);

        $request->validate([
            'baslik' => 'required|string|max:255',
            'icerik' => 'required|string',
            'onem_derecesi' => 'required|in:genel,onemli,acil',
        ]);

        $duyuru->update([
            'baslik' => $request->baslik,
            'icerik' => $request->icerik,
            'onem_derecesi' => $request->onem_derecesi,
        ]);

        return redirect()->route('hekim.klinik.duyurular.index')->with('basari', 'Duyuru başarıyla güncellendi.');
    }

    /**
     * Toggle the active status of the announcement.
     */
    public function toggle($id)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        if (!$klinik) {
            return back()->with('hata', 'Kliniğiniz bulunamadı.');
        }

        $duyuru = $klinik->duyurular()->findOrFail($id);
        $duyuru->update(['aktif_mi' => !$duyuru->aktif_mi]);

        return back()->with('basari', 'Duyuru durumu güncellendi.');
    }

    /**
     * Remove the specified announcement from storage.
     */
    public function destroy($id)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        if (!$klinik) {
            return back()->with('hata', 'Kliniğiniz bulunamadı.');
        }

        $duyuru = $klinik->duyurular()->findOrFail($id);
        $duyuru->delete();

        return redirect()->route('hekim.klinik.duyurular.index')->with('basari', 'Duyuru başarıyla silindi.');
    }
}
