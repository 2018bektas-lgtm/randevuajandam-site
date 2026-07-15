<?php

namespace App\Http\Controllers;

use App\Models\Yorum;
use Illuminate\Http\Request;

class YorumController extends Controller
{
    /**
     * Display the reviews moderation list.
     */
    public function index(Request $request)
    {
        $query = Yorum::with('hasta', 'doktor', 'randevu.hizmet')->latest();

        if ($request->filled('durum')) {
            $query->where('onay_durumu', $request->durum);
        }

        if ($request->filled('puan')) {
            $query->where('puan', $request->puan);
        }

        if ($request->filled('arama')) {
            $arama = $request->arama;
            $query->where(function ($q) use ($arama) {
                $q->whereHas('hasta', function ($sq) use ($arama) {
                    $sq->where('ad', 'like', "%{$arama}%")
                        ->orWhere('soyad', 'like', "%{$arama}%");
                })->orWhereHas('doktor', function ($sq) use ($arama) {
                    $sq->where('ad_soyad', 'like', "%{$arama}%");
                })->orWhere('yorum', 'like', "%{$arama}%");
            });
        }

        $yorumlar = $query->paginate(20)->withQueryString();

        $istatistikler = [
            'toplam' => Yorum::count(),
            'beklemede' => Yorum::where('onay_durumu', 'beklemede')->count(),
            'onaylandi' => Yorum::where('onay_durumu', 'onaylandi')->count(),
            'reddedildi' => Yorum::where('onay_durumu', 'reddedildi')->count(),
        ];

        return view('yonetim.yorumlar.index', compact('yorumlar', 'istatistikler'));
    }

    /**
     * Approve a review.
     */
    public function onayla(int $id)
    {
        $yorum = Yorum::findOrFail($id);
        $yorum->update(['onay_durumu' => 'onaylandi']);

        return redirect()->back()->with('basarili', 'Yorum başarıyla onaylandı ve yayınlandı.');
    }

    /**
     * Reject a review.
     */
    public function reddet(int $id)
    {
        $yorum = Yorum::findOrFail($id);
        $yorum->update(['onay_durumu' => 'reddedildi']);

        return redirect()->back()->with('basarili', 'Yorum reddedildi.');
    }

    /**
     * Delete a review permanently.
     */
    public function sil(int $id)
    {
        $yorum = Yorum::findOrFail($id);
        $yorum->delete();

        return redirect()->back()->with('basarili', 'Yorum başarıyla silindi.');
    }
}
