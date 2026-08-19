<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Klinik;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Model B — hekim / klinik kendi WhatsApp numarasini Embedded Signup ile baglar.
 *
 * NOT: Tech Provider onayi tamamlanmadan bu akis calismaz. WHATSAPP_APP_ID ve
 * WHATSAPP_EMBEDDED_SIGNUP_CONFIG_ID .env'de bos ise 503 doner.
 */
class KlinikWhatsAppController extends Controller
{
    /**
     * Meta popup'i tamamlandiktan sonra frontend'in POST ettigi endpoint.
     * body: { code: string }
     */
    public function baglan(Request $request): JsonResponse
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor?->klinik;
        if (! $klinik) {
            return response()->json(['success' => false, 'message' => 'Klinik bulunamadi.'], 404);
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
            Log::warning('WhatsApp Embedded Signup hata', [
                'klinik_id' => $klinik->id,
                'hata' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $klinik->update([
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

    /**
     * Klinigin baglanti bilgisini kaldirir (Model A'ya dus).
     */
    public function ayir(): JsonResponse
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor?->klinik;
        if (! $klinik) {
            return response()->json(['success' => false, 'message' => 'Klinik bulunamadi.'], 404);
        }

        $klinik->update([
            'whatsapp_config' => null,
            'whatsapp_baglandi_at' => null,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Meta OAuth code -> long-lived token + WABA/phone_number bilgisi.
     *
     * @return array{token: string, waba_id: ?string, phone_number_id: ?string, display_name: ?string}
     */
    private function codeToConfig(string $code, string $appId, string $appSecret): array
    {
        $sur = (string) config('whatsapp.api_version');
        $base = rtrim((string) config('whatsapp.base_url'), '/');

        // 1) code -> access_token
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

        // 2) debug_token -> WABA ID
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

        // 3) WABA -> ilk phone_number
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
