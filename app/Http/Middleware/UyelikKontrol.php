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
            // Bypass for logout and package selection/payment/onboarding routes
            if ($request->routeIs('hekim.cikis')
                || $request->routeIs('frontend.hekim.paket_sec')
                || $request->routeIs('frontend.hekim.paket_ode')
                || $request->routeIs('frontend.hekim.paket_ode.post')
                || $request->routeIs('frontend.hekim.paket_deneme')
                || $request->routeIs('frontend.hekim.onboarding.*')
                || $request->routeIs('frontend.hekim.basarili')) {
                return $next($request);
            }

            // Redirect if no package is chosen yet and not in a clinic
            if (is_null($doktor->paket_id) && ! $doktor->klinikteMi() && ! app()->environment('testing')) {
                return redirect()->route('frontend.hekim.paket_sec')
                    ->with('hata', 'Devam etmek için bir paket seçin. Başlangıç paketinde 14 gün ücretsiz deneme var.');
            }

            if ($doktor->klinikteMi()) {
                $klinik = $doktor->klinik;
                if ($klinik) {
                    if (! $klinik->aktif_mi) {
                        // Avoid redirect loop if we are already on panel/logout/etc.
                        if (! $request->routeIs('hekim.panel')) {
                            return redirect()->route('hekim.panel')
                                ->with('hata', 'Kliniğinizin üyeliği pasiftir. Lütfen klinik yöneticisiyle iletişime geçin.');
                        }
                    }
                    if ($klinik->uyelik_bitis && $klinik->uyelik_bitis->isPast()) {
                        if (! $request->routeIs('hekim.panel')) {
                            return redirect()->route('hekim.panel')
                                ->with('hata', 'Kliniğinizin üyelik süresi dolmuştur. Lütfen klinik yöneticisiyle iletişime geçin.');
                        }
                    }
                }
            } else {
                // Deneme veya ücretli üyelik süresi doldu → paket seç / öde
                if ($doktor->uyelik_bitis && $doktor->uyelik_bitis->isPast()) {
                    $wasTrial = $doktor->odeme_periyodu === 'deneme';

                    return redirect()->route('frontend.hekim.paket_sec')
                        ->with(
                            'hata',
                            $wasTrial
                                ? '14 günlük ücretsiz denemeniz sona erdi. Devam etmek için bir paket seçip ödemeyi tamamlayın.'
                                : 'Üyelik süreniz dolmuştur. Lütfen devam etmek için bir paket seçin ve ödemeyi tamamlayın.'
                        );
                }
            }
        }

        return $next($request);
    }
}
