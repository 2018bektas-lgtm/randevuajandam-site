<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Doktor;
use App\Models\Yorum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HekimYorumController extends Controller
{
    /**
     * Display the doctor's reviews list.
     */
    public function index(Request $request)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $query = $doktor->yorumlar()->with('hasta', 'randevu.hizmet')->latest();

        if ($request->filled('durum')) {
            $query->where('onay_durumu', $request->durum);
        }

        if ($request->filled('puan')) {
            $query->where('puan', $request->puan);
        }

        $yorumlar = $query->paginate(15)->withQueryString();

        $istatistikler = [
            'toplam' => $doktor->yorumlar()->count(),
            'beklemede' => $doktor->yorumlar()->beklemede()->count(),
            'onaylandi' => $doktor->yorumlar()->onaylandi()->count(),
            'ortalama_puan' => $doktor->ortalama_puan,
        ];

        return view('hekim.yorum.index', compact('yorumlar', 'istatistikler'));
    }

    /**
     * Reply to a review.
     */
    public function yanitla(Request $request, int $id)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $request->validate([
            'doktor_yaniti' => ['required', 'string', 'min:5', 'max:500', new \App\Rules\NoProfanity],
        ], [
            'doktor_yaniti.required' => 'Yanıt alanı zorunludur.',
            'doktor_yaniti.min' => 'Yanıt en az 5 karakter olmalıdır.',
            'doktor_yaniti.max' => 'Yanıt en fazla 500 karakter olabilir.',
        ]);

        $yorum = $doktor->yorumlar()->findOrFail($id);

        $yorum->update([
            'doktor_yaniti' => $request->input('doktor_yaniti'),
        ]);

        return redirect()->back()->with('basarili', 'Yanıtınız başarıyla kaydedildi.');
    }
}
