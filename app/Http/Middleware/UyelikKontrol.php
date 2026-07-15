<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UyelikKontrol
{
    /**
     * Check if the authenticated doctor's subscription is still active.
     * Redirects to login with an error message if the subscription has expired.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $doktor = Auth::guard('doktor')->user();

        if ($doktor) {
            // Bypass for logout and package selection/payment routes
            if ($request->routeIs('hekim.cikis') || 
                $request->routeIs('frontend.hekim.paket_sec') || 
                $request->routeIs('frontend.hekim.paket_ode') || 
                $request->routeIs('frontend.hekim.paket_ode.post')) {
                return $next($request);
            }

            // Redirect if no package is chosen yet and not in a clinic
            if (is_null($doktor->paket_id) && !$doktor->klinikteMi() && !app()->environment('testing')) {
                return redirect()->route('frontend.hekim.paket_sec');
            }

            if ($doktor->klinikteMi()) {
                $klinik = $doktor->klinik;
                if ($klinik) {
                    if (!$klinik->aktif_mi) {
                        // Avoid redirect loop if we are already on panel/logout/etc.
                        if (!$request->routeIs('hekim.panel')) {
                            return redirect()->route('hekim.panel')
                                ->with('hata', 'Kliniğinizin üyeliği pasiftir. Lütfen klinik yöneticisiyle iletişime geçin.');
                        }
                    }
                    if ($klinik->uyelik_bitis && $klinik->uyelik_bitis->isPast()) {
                        if (!$request->routeIs('hekim.panel')) {
                            return redirect()->route('hekim.panel')
                                ->with('hata', 'Kliniğinizin üyelik süresi dolmuştur. Lütfen klinik yöneticisiyle iletişime geçin.');
                        }
                    }
                }
            } else {
                // Redirect if subscription has expired
                if ($doktor->uyelik_bitis && $doktor->uyelik_bitis->isPast()) {
                    return redirect()->route('frontend.hekim.paket_sec')
                        ->with('hata', 'Üyelik süreniz dolmuştur. Lütfen devam etmek için yeni bir paket seçin.');
                }
            }
        }

        return $next($request);
    }
}
