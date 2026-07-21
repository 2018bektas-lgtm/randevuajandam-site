<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Clinic subscription package flags (boolean columns / feature codes).
 *
 * Usage: middleware('klinik.paket:merkezi_finans')
 * Flags: toplu_randevu, merkezi_finans, raporlama, hasta_havuzu, klinik_web_sitesi
 */
class KlinikPaketOzellikMiddleware
{
    public function handle(Request $request, Closure $next, string $flag): Response
    {
        $user = auth('doktor')->user();
        $klinik = $user?->klinik;

        if (! $user || ! $klinik) {
            abort(403, 'Klinik üyeliği bulunamadı.');
        }

        if (! $klinik->hasPaketFlag($flag)) {
            $messages = [
                'toplu_randevu' => 'Toplu randevu işlemleri bu klinik paketinde yer almıyor. Paketinizi yükseltin.',
                'merkezi_finans' => 'Merkezi finans bu klinik paketinde yer almıyor. Paketinizi yükseltin.',
                'raporlama' => 'Gelişmiş raporlama bu klinik paketinde yer almıyor. Paketinizi yükseltin.',
                'hasta_havuzu' => 'Ortak hasta havuzu bu klinik paketinde yer almıyor. Paketinizi yükseltin.',
                'klinik_web_sitesi' => 'Klinik web sitesi bu pakette yok. Klinik Özel Web Sitesi paketine yükseltin.',
            ];

            $message = $messages[$flag] ?? 'Bu özellik mevcut klinik paketinizde yer almamaktadır.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'feature' => $flag,
                ], 403);
            }

            return redirect()
                ->route('hekim.klinik.yonetim')
                ->with('hata', $message);
        }

        return $next($request);
    }
}
