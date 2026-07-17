<?php

namespace App\Http\Middleware;

use App\Models\PersonelApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PersonelMobileToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = (string) $request->bearerToken();
        if ($header === '') {
            $header = (string) $request->header('X-Personel-Token', '');
        }
        if ($header === '') {
            return response()->json(['success' => false, 'message' => 'Oturum gerekli.'], 401);
        }

        $row = PersonelApiToken::findByPlainToken($header);
        if (! $row) {
            return response()->json(['success' => false, 'message' => 'Geçersiz veya süresi dolmuş oturum.'], 401);
        }

        $row->loadMissing('personel.klinik');
        $personel = $row->personel;

        if (! $row->isValid() || ! $personel || ! $personel->aktif_mi) {
            return response()->json(['success' => false, 'message' => 'Geçersiz veya süresi dolmuş oturum.'], 401);
        }

        $row->forceFill(['last_used_at' => now()])->save();
        $request->attributes->set('auth_personel', $personel);
        $request->attributes->set('auth_personel_token', $row);

        return $next($request);
    }
}
