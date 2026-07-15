<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Hasta;
use App\Models\Randevu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PersonelHastaController extends Controller
{
    /**
     * Display the shared clinic patient pool.
     */
    public function index(Request $request)
    {
        $personel = Auth::guard('personel')->user();
        $klinik = $personel->klinik;
        $q = $request->input('q');

        $query = $klinik->hastalar();

        if (! empty($q)) {
            $query->where(function ($sub) use ($q) {
                $sub->where('ad', 'like', "%{$q}%")
                    ->orWhere('soyad', 'like', "%{$q}%")
                    ->orWhere('e_posta', 'like', "%{$q}%")
                    ->orWhere('telefon', 'like', "%{$q}%");
            });
        }

        $hastalar = $query
            ->orderBy('ad')
            ->orderBy('soyad')
            ->paginate(20);

        return view('personel.hastalar.index', compact('personel', 'klinik', 'hastalar', 'q'));
    }

    /**
     * Create a new patient and attach to clinic.
     */
    public function store(Request $request)
    {
        $request->validate([
            'ad_soyad' => 'required|string|max:100',
            'e_posta' => 'required|email|unique:hastalar,e_posta',
            'telefon' => 'required|string',
            'sifre' => 'nullable|string|min:6',
        ], [
            'ad_soyad.required' => 'Ad soyad alanı zorunludur.',
            'e_posta.required' => 'E-posta alanı zorunludur.',
            'e_posta.unique' => 'Bu e-posta adresi zaten kullanımda.',
            'telefon.required' => 'Telefon alanı zorunludur.',
        ]);

        $parts = preg_split('/\s+/', trim($request->ad_soyad), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $soyad = count($parts) > 1 ? array_pop($parts) : '';
        $ad = implode(' ', $parts);
        if ($ad === '') {
            $ad = $request->ad_soyad;
        }

        $geciciSifre = $request->filled('sifre') ? $request->sifre : Str::password(10);

        $hasta = Hasta::create([
            'ad' => $ad,
            'soyad' => $soyad,
            'e_posta' => $request->e_posta,
            'telefon' => $request->telefon,
            'sifre' => $geciciSifre,
            'aktif_mi' => true,
        ]);

        $personel = Auth::guard('personel')->user();
        $personel->klinik->hastalar()->syncWithoutDetaching([$hasta->id => ['kayit_tarihi' => now()]]);

        $mesaj = 'Hasta başarıyla ortak havuza kaydedildi.';
        if (! $request->filled('sifre')) {
            $mesaj .= ' Geçici şifre: '.$geciciSifre;
        }

        return redirect()->route('personel.hastalar.index')->with('basari', $mesaj);
    }

    /**
     * Display patient profile details and appointment history in the clinic.
     */
    public function detay($id)
    {
        $personel = Auth::guard('personel')->user();
        $klinik = $personel->klinik;

        // Ensure patient is in this clinic
        $hasta = $klinik->hastalar()->findOrFail($id);

        // Fetch patient appointments with clinic doctors only
        $randevular = Randevu::where('hasta_id', $id)
            ->whereHas('doktor', function ($q) use ($klinik) {
                $q->where('klinik_id', $klinik->id);
            })
            ->with('doktor', 'hizmet')
            ->orderBy('tarih', 'desc')
            ->orderBy('saat', 'desc')
            ->get();

        return view('personel.hastalar.detay', compact('personel', 'klinik', 'hasta', 'randevular'));
    }
}
