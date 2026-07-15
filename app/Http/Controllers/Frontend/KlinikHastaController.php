<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Odeme;
use App\Models\Randevu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KlinikHastaController extends Controller
{
    /**
     * Display the shared clinic patient pool.
     */
    public function index(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;
        if (! $klinik) {
            return redirect()->route('hekim.panel')->with('hata', 'Kliniğiniz bulunamadı.');
        }

        $q = $request->input('q');
        $doktorIds = $klinik->doktorlar()->pluck('id');

        $query = $klinik->hastalar();

        if (! empty($q)) {
            $query->where(function ($sub) use ($q) {
                $sub->where('ad', 'like', "%{$q}%")
                    ->orWhere('soyad', 'like', "%{$q}%")
                    ->orWhere('e_posta', 'like', "%{$q}%")
                    ->orWhere('telefon', 'like', "%{$q}%");
            });
        }

        $hastalar = $query->orderBy('ad')->orderBy('soyad')->paginate(25);

        // Fetch last appointment and count for each patient
        foreach ($hastalar as $hasta) {
            $hasta->son_randevu = Randevu::whereIn('doktor_id', $doktorIds)
                ->where('hasta_id', $hasta->id)
                ->with('doktor')
                ->orderBy('tarih', 'desc')
                ->orderBy('saat', 'desc')
                ->first();

            $hasta->toplam_randevu = Randevu::whereIn('doktor_id', $doktorIds)
                ->where('hasta_id', $hasta->id)
                ->count();
        }

        return view('klinik.hastalar.index', compact('klinik', 'hastalar', 'q'));
    }

    /**
     * Display detailed patient history in the clinic.
     */
    public function show($id)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;
        if (! $klinik) {
            return redirect()->route('hekim.panel')->with('hata', 'Kliniğiniz bulunamadı.');
        }

        $hasta = $klinik->hastalar()->findOrFail($id);
        $doktorIds = $klinik->doktorlar()->pluck('id');

        $randevular = Randevu::whereIn('doktor_id', $doktorIds)
            ->where('hasta_id', $id)
            ->with('doktor', 'hizmet')
            ->orderBy('tarih', 'desc')
            ->orderBy('saat', 'desc')
            ->get();

        $odemeler = Odeme::whereIn('doktor_id', $doktorIds)
            ->where('hasta_id', $id)
            ->with('doktor')
            ->orderBy('odeme_tarihi', 'desc')
            ->get();

        return view('klinik.hastalar.detay', compact('klinik', 'hasta', 'randevular', 'odemeler'));
    }

    /**
     * Update the clinic-wide private note for the patient.
     */
    public function notGuncelle(Request $request, $id)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;
        if (! $klinik) {
            return redirect()->route('hekim.panel')->with('hata', 'Kliniğiniz bulunamadı.');
        }

        $hasta = $klinik->hastalar()->findOrFail($id);

        $request->validate([
            'notlar' => 'nullable|string|max:1000',
        ]);

        $klinik->hastalar()->updateExistingPivot($id, [
            'notlar' => $request->notlar,
        ]);

        return redirect()->back()->with('basari', 'Klinik notu başarıyla güncellendi.');
    }
}
