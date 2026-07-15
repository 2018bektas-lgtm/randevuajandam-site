<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KlinikPersonelMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $modul = null): Response
    {
        $personel = auth('personel')->user();

        if (! $personel || ! $personel->aktif_mi) {
            if (auth('personel')->check()) {
                auth('personel')->logout();
            }
            abort(403, 'Bu panele erişim yetkiniz bulunmamaktadır veya hesabınız askıya alınmıştır.');
        }

        // Şifre değiştirme yönlendirmesi
        if (! $personel->sifre_degistirildi_mi && ! $request->routeIs('personel.sifre-degistir') && ! $request->routeIs('personel.cikis')) {
            return redirect()->route('personel.sifre-degistir');
        }

        if ($modul && ! $personel->yetkisiVarMi($modul)) {
            abort(403, 'Bu modüle erişim yetkiniz bulunmamaktadır.');
        }

        return $next($request);
    }
}
