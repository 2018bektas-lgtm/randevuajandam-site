<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ?ref=KOD → session + cookie (hekim kayıt referansı).
 */
class CaptureReferansKod
{
    public function handle(Request $request, Closure $next): Response
    {
        $kod = $request->query('ref');
        if (is_string($kod) && preg_match('/^[A-Za-z0-9]{4,16}$/', $kod)) {
            $kod = strtoupper($kod);
            $request->session()->put('ra_ref', $kod);
            $gun = (int) config('referans.cookie_gun', 30);
            cookie()->queue(
                cookie(
                    config('referans.cookie_name', 'ra_ref'),
                    $kod,
                    $gun * 24 * 60,
                    '/',
                    null,
                    $request->isSecure(),
                    true,
                    false,
                    'Lax'
                )
            );
        }

        return $next($request);
    }
}
