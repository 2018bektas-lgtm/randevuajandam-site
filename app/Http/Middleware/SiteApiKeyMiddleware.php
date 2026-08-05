<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SiteApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKeyVal = (string) $request->header('X-Api-Key', $request->query('api_key', ''));
        $secretVal = (string) $request->header('X-Api-Secret', $request->query('api_secret', ''));

        if ($apiKeyVal === '' || $secretVal === '') {
            return response()->json(['success' => false, 'message' => 'API kimlik bilgileri eksik.'], 401);
        }

        /** @var ApiKey|null $apiKey */
        $apiKey = ApiKey::query()->where('api_key', $apiKeyVal)->first();

        if (! $apiKey || ! $apiKey->isActive() || ! $apiKey->verifySecret($secretVal)) {
            return response()->json(['success' => false, 'message' => 'Geçersiz API anahtarı veya secret.'], 401);
        }

        $apiKey->touchUsage();

        // Hekim API key → doktoru doğrudan al
        if ($apiKey->doktor_id) {
            $doktor = $apiKey->doktor()->with([])->first();
            if (! $doktor || ! $doktor->aktif_mi) {
                return response()->json(['success' => false, 'message' => 'Hekim hesabı aktif değil.'], 403);
            }
            $request->attributes->set('site_doktor', $doktor);
            $request->attributes->set('site_klinik', null);
            return $next($request);
        }

        // Klinik API key → sahip hekimi çöz
        if ($apiKey->klinik_id) {
            $klinik = $apiKey->klinik()->with('sahipDoktor')->first();
            if (! $klinik || ! $klinik->aktif_mi) {
                return response()->json(['success' => false, 'message' => 'Klinik hesabı aktif değil.'], 403);
            }
            $doktor = $klinik->sahipDoktor;
            if (! $doktor || ! $doktor->aktif_mi) {
                return response()->json(['success' => false, 'message' => 'Klinik sahibi hekim bulunamadı.'], 403);
            }
            $request->attributes->set('site_doktor', $doktor);
            $request->attributes->set('site_klinik', $klinik);
            return $next($request);
        }

        return response()->json(['success' => false, 'message' => 'API anahtarı bir hesaba bağlı değil.'], 401);
    }
}
