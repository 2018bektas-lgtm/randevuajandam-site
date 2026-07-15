<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KlinikLimitMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $tur): Response
    {
        $user = auth('doktor')->user();

        if (! $user || ! $user->klinikSahibiMi()) {
            abort(403, 'Bu işlem için yetkiniz bulunmamaktadır.');
        }

        $klinik = $user->klinik;

        if (! $klinik) {
            abort(404, 'Klinik bulunamadı.');
        }

        if ($tur === 'doktor' && $klinik->doktorLimitiDolduMu()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paketinizdeki doktor limitine ulaştınız. Yeni doktor davet edebilmek için paketinizi yükseltmelisiniz.',
                ], 422);
            }

            return back()->with('error', 'Paketinizdeki doktor limitine ulaştınız. Yeni doktor davet edebilmek için paketinizi yükseltmelisiniz.');
        }

        if ($tur === 'personel' && $klinik->personelLimitiDolduMu()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paketinizdeki personel limitine ulaştınız. Yeni personel ekleyebilmek için paketinizi yükseltmelisiniz.',
                ], 422);
            }

            return back()->with('error', 'Paketinizdeki personel limitine ulaştınız. Yeni personel ekleyebilmek için paketinizi yükseltmelisiniz.');
        }

        return $next($request);
    }
}
