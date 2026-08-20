<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\UyelikOdeme;
use Illuminate\Support\Facades\Auth;

class HekimFaturaController extends Controller
{
    /**
     * Hekimin paket/üyelik ödemelerinin faturalarını listeler.
     * Yönetici bir ödemeye fatura yükledikten sonra burada indirilebilir.
     */
    public function index()
    {
        $doktor = Auth::guard('doktor')->user();

        $odemeler = UyelikOdeme::where('doktor_id', $doktor->id)
            ->with('paket:id,ad')
            ->orderByDesc('created_at')
            ->paginate(20);

        // Üst özet
        $ozet = [
            'toplam_odeme'      => $odemeler->total(),
            'fatura_kesilen'    => UyelikOdeme::where('doktor_id', $doktor->id)
                                    ->whereNotNull('fatura_url')
                                    ->count(),
            'fatura_bekleyen'   => UyelikOdeme::where('doktor_id', $doktor->id)
                                    ->where('durum', 'onaylandi')
                                    ->whereNull('fatura_url')
                                    ->count(),
            'toplam_tutar'      => (float) UyelikOdeme::where('doktor_id', $doktor->id)
                                    ->where('durum', 'onaylandi')
                                    ->sum('tutar'),
        ];

        return view('hekim.faturalarim', compact('doktor', 'odemeler', 'ozet'));
    }
}
