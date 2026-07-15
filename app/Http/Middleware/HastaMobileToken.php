<?php

namespace App\Http\Middleware;

use App\Models\HastaApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HastaMobileToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = (string) $request->bearerToken();
        if ($header === '') {
            $header = (string) $request->header('X-Hasta-Token', '');
        }

        if ($header === '') {
            return response()->json(['success' => false, 'message' => 'Oturum gerekli.'], 401);
        }

        $row = HastaApiToken::query()->where('token', $header)->with('hasta')->first();
        if (! $row || ! $row->isValid() || ! $row->hasta || ! $row->hasta->aktif_mi) {
            return response()->json(['success' => false, 'message' => 'Geçersiz veya süresi dolmuş oturum.'], 401);
        }

        $row->forceFill(['last_used_at' => now()])->save();
        $request->attributes->set('auth_hasta', $row->hasta);
        $request->attributes->set('auth_hasta_token', $row);

        return $next($request);
    }
}
