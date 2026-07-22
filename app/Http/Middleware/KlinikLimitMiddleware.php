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
                    'message' => 'Hekim limitiniz doldu. Ek hekim koltuğu satın alabilir veya paketinizi yükseltebilirsiniz.',
                    'limit' => $klinik->efektifDoktorLimiti(),
                    'dahil_limit' => $klinik->dahilDoktorLimiti(),
                    'ek_koltuk' => (int) $klinik->ek_doktor_koltuk_sayisi,
                    'mevcut' => $klinik->doktorlar()->count(),
                ], 422);
            }

            return back()->with('error', 'Hekim limitiniz doldu. Ek hekim koltuğu satın alabilir veya paketinizi yükseltebilirsiniz.');
        }

        if ($tur === 'personel' && $klinik->personelLimitiDolduMu()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paketinizdeki personel limitine ulaştınız. Yeni personel ekleyebilmek için paketinizi yükseltmelisiniz.',
                    'limit' => $klinik->paket?->max_personel_sayisi,
                    'mevcut' => $klinik->personeller()->count(),
                ], 422);
            }

            return back()->with('error', 'Paketinizdeki personel limitine ulaştınız. Yeni personel ekleyebilmek için paketinizi yükseltmelisiniz.');
        }

        return $next($request);
    }
}
