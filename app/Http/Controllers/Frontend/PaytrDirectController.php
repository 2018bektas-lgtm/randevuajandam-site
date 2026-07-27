<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Eski Direct API (sitede kart formu) — bilerek kapalı.
 * Aktif akış: PayTR iFrame + notify (PaytrCallbackController).
 */
class PaytrDirectController extends Controller
{
    /**
     * Siteden kartlı Direct charge — 410 Gone (iframe-only politika).
     */
    public function charge(Request $request)
    {
        Log::info('PayTR Direct charge reddedildi — iframe-only politika', [
            'ip' => $request->ip(),
            'doktor_id' => Auth::guard('doktor')->id(),
        ]);

        return response()->json([
            'error' => 'Kartlı ödeme yalnızca PayTR güvenli ekranı (iFrame) ile yapılır. Lütfen paket ödeme sayfasından “Ödemeyi tamamla” ile devam edin.',
            'code' => 'paytr_iframe_only',
        ], 410);
    }

    /**
     * Eski 3D modal dönüş URL'leri — artık kullanılmıyor; yönlendir.
     */
    public function threeDOk(Request $request)
    {
        return redirect()->route('frontend.odeme.paytr.ok');
    }

    public function threeDFail(Request $request)
    {
        return redirect()->route('frontend.odeme.paytr.fail');
    }
}
