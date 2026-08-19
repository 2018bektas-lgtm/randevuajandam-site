<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Model B — bireysel hekim kendi WhatsApp numarasini Embedded Signup ile baglar.
 * Klinik altindaysa (klinik_id dolu) baglanti klinige aittir; oraya
 * KlinikWhatsAppController'i kullanin.
 *
 * NOT: Tech Provider onayi tamamlanmadan bu akis calismaz.
 */
class HekimWhatsAppController extends Controller
{
    public function baglan(Request $request): JsonResponse
    {
        $doktor = Auth::guard('doktor')->user();
        if (! $doktor) {
            return response()->json(['success' => false, 'message' => 'Oturum gerekli.'], 401);
        }

        // Klinik altindaki hekim kendi numarasini bagliyamaz — klinik ayarindan yapmali.
        if ($doktor->klinik_id) {
            return response()->json([
                'success' => false,
                'message' => 'Klinigde calisan hekim WhatsApp numarasini klinik ayarlarindan baglar.',
            ], 422);
        }

        $appId = (string) config('whatsapp.app_id');
        $appSecret = (string) config('whatsapp.app_secret');
        if ($appId === '' || $appSecret === '') {
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp Embedded Signup henuz aktif degil.',
            ], 503);
        }

        $data = $request->validate([
            'code' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $ayar = $this->codeToConfig($data['code'], $appId, $appSecret);
        } catch (RuntimeException $e) {
            Log::warning('Hekim WhatsApp Embedded Signup hata', [
                'doktor_id' => $doktor->id,
                'hata' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $doktor->update([
            'whatsapp_config' => $ayar,
            'whatsapp_baglandi_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'display_name' => $ayar['display_name'] ?? null,
                'phone_number_id' => $ayar['phone_number_id'] ?? null,
            ],
        ]);
    }

    public function ayir(): JsonResponse
    {
        $doktor = Auth::guard('doktor')->user();
        if (! $doktor) {
            return response()->json(['success' => false, 'message' => 'Oturum gerekli.'], 401);
        }

        $doktor->update([
            'whatsapp_config' => null,
            'whatsapp_baglandi_at' => null,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * @return array{token: string, waba_id: ?string, phone_number_id: ?string, display_name: ?string}
     */
    private function codeToConfig(string $code, string $appId, string $appSecret): array
    {
        $sur = (string) config('whatsapp.api_version');
        $base = rtrim((string) config('whatsapp.base_url'), '/');

        $tokenRes = Http::acceptJson()
            ->timeout(20)
            ->get("{$base}/{$sur}/oauth/access_token", [
                'client_id' => $appId,
                'client_secret' => $appSecret,
                'code' => $code,
            ]);

        if (! $tokenRes->successful()) {
            throw new RuntimeException('Token alinamadi: '.($tokenRes->json('error.message') ?? $tokenRes->body()));
        }

        $token = (string) $tokenRes->json('access_token');
        if ($token === '') {
            throw new RuntimeException('Meta token bos dondu.');
        }

        $debug = Http::withToken($token)
            ->timeout(20)
            ->acceptJson()
            ->get("{$base}/{$sur}/debug_token", ['input_token' => $token]);

        $granular = (array) $debug->json('data.granular_scopes', []);
        $wabaId = null;
        foreach ($granular as $scope) {
            if (($scope['scope'] ?? null) === 'whatsapp_business_management') {
                $wabaId = ($scope['target_ids'][0] ?? null);
                break;
            }
        }

        $phoneNumberId = null;
        $displayName = null;
        if ($wabaId) {
            $phoneRes = Http::withToken($token)
                ->timeout(20)
                ->acceptJson()
                ->get("{$base}/{$sur}/{$wabaId}/phone_numbers");

            $ilk = (array) ($phoneRes->json('data.0') ?? []);
            $phoneNumberId = $ilk['id'] ?? null;
            $displayName = $ilk['verified_name'] ?? null;
        }

        return [
            'token' => $token,
            'waba_id' => $wabaId,
            'phone_number_id' => $phoneNumberId,
            'display_name' => $displayName,
        ];
    }
}
