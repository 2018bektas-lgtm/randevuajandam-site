<?php
namespace App\Http\Middleware;
use App\Models\DoktorApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DoktorMobileToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = (string) $request->bearerToken();
        if ($header === '') $header = (string) $request->header('X-Doktor-Token', '');
        if ($header === '') return response()->json(['success' => false, 'message' => 'Oturum gerekli.'], 401);
        $row = DoktorApiToken::findByPlainToken($header);
        if (! $row) return response()->json(['success' => false, 'message' => 'Geçersiz veya süresi dolmuş oturum.'], 401);
        $row->loadMissing('doktor');
        $doktor = $row->doktor;
        if (! $row->isValid() || ! $doktor || ! $doktor->aktif_mi) {
            return response()->json(['success' => false, 'message' => 'Geçersiz veya süresi dolmuş oturum.'], 401);
        }
        $row->forceFill(['last_used_at' => now()])->save();
        $request->attributes->set('auth_doktor', $doktor);
        $request->attributes->set('auth_doktor_token', $row);
        return $next($request);
    }
}
