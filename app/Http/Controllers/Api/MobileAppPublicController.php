<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MobileAppPublicController extends Controller
{
    /**
     * In-app low-star feedback (no store redirect). Public + throttled.
     */
    public function ratingFeedback(Request $request): JsonResponse
    {
        $data = $request->validate([
            'yildiz' => ['required', 'integer', 'min:1', 'max:5'],
            'sebep' => ['nullable', 'string', 'max:2000'],
            'platform' => ['nullable', 'string', 'max:20'],
            'app_version' => ['nullable', 'string', 'max:40'],
            'onboarding_cevaplar' => ['nullable', 'array'],
            'e_posta' => ['nullable', 'email', 'max:255'],
        ]);

        if ((int) $data['yildiz'] <= 2 && empty(trim((string) ($data['sebep'] ?? '')))) {
            return response()->json([
                'success' => false,
                'message' => 'Düşük puan için lütfen kısa bir sebep yazın.',
            ], 422);
        }

        if (Schema::hasTable('uygulama_geri_bildirimleri')) {
            DB::table('uygulama_geri_bildirimleri')->insert([
                'yildiz' => (int) $data['yildiz'],
                'sebep' => $data['sebep'] ?? null,
                'platform' => $data['platform'] ?? null,
                'app_version' => $data['app_version'] ?? null,
                'onboarding_cevaplar' => isset($data['onboarding_cevaplar'])
                    ? json_encode($data['onboarding_cevaplar'], JSON_UNESCAPED_UNICODE)
                    : null,
                'e_posta' => $data['e_posta'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Geri bildiriminiz alındı. Teşekkürler.',
        ]);
    }

    /**
     * Public package catalog for mobile onboarding / IAP mapping (no web checkout).
     */
    public function packagesCatalog(): JsonResponse
    {
        if (! class_exists(\App\Models\Paket::class)) {
            return response()->json(['success' => true, 'data' => ['items' => []]]);
        }

        $items = \App\Models\Paket::query()
            ->where('aktif_mi', true)
            ->orderByRaw("CASE WHEN tur = 'bireysel' THEN 0 ELSE 1 END")
            ->orderBy('sira')
            ->orderBy('id')
            ->get()
            ->map(function ($p) {
                $aylik = (float) ($p->aylik_indirimli_fiyat ?? $p->aylik_fiyat ?? 0);
                $yillik = (float) ($p->yillik_indirimli_fiyat ?? $p->yillik_fiyat ?? 0);
                $ozellikler = is_array($p->ozellikler) ? $p->ozellikler : [];

                return [
                    'id' => $p->id,
                    'ad' => $p->ad,
                    'tur' => $p->tur ?? 'bireysel',
                    'aciklama' => $p->aciklama,
                    'ozellikler' => $ozellikler,
                    'aylik_fiyat' => (float) ($p->aylik_fiyat ?? 0),
                    'aylik_indirimli_fiyat' => $p->aylik_indirimli_fiyat !== null ? (float) $p->aylik_indirimli_fiyat : null,
                    'yillik_fiyat' => (float) ($p->yillik_fiyat ?? 0),
                    'yillik_indirimli_fiyat' => $p->yillik_indirimli_fiyat !== null ? (float) $p->yillik_indirimli_fiyat : null,
                    'max_doktor_sayisi' => $p->max_doktor_sayisi,
                    'max_personel_sayisi' => $p->max_personel_sayisi,
                    'max_hasta_sayisi' => $p->max_hasta_sayisi,
                    'max_randevu_sayisi' => $p->max_randevu_sayisi,
                    'ucretsiz_mi' => $aylik <= 0 && $yillik <= 0,
                    // Store product IDs (configure in App Store / Play Console)
                    'iap_product_aylik' => 'com.randevuajandam.doktor.pkg.'.$p->id.'.monthly',
                    'iap_product_yillik' => 'com.randevuajandam.doktor.pkg.'.$p->id.'.yearly',
                ];
            });

        return response()->json(['success' => true, 'data' => ['items' => $items]]);
    }

    /**
     * RevenueCat server webhook — activates doctor package on INITIAL_PURCHASE / RENEWAL.
     * Configure webhook URL: https://randevuajandam.com/api/mobile/v1/app/revenuecat-webhook
     * Authorization: Bearer {REVENUECAT_WEBHOOK_SECRET}
     */
    public function revenueCatWebhook(Request $request): JsonResponse
    {
        $secret = (string) config('services.revenuecat.webhook_secret', '');
        if ($secret !== '') {
            $auth = (string) $request->header('Authorization', '');
            $token = str_starts_with($auth, 'Bearer ') ? substr($auth, 7) : $auth;
            if (! hash_equals($secret, $token)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }
        }

        $event = $request->input('event', $request->all());
        $type = (string) data_get($event, 'type', data_get($event, 'event.type', ''));
        $appUserId = (string) data_get($event, 'app_user_id', data_get($event, 'event.app_user_id', ''));
        $productId = (string) data_get($event, 'product_id', data_get($event, 'event.product_id', ''));
        $transactionId = (string) data_get($event, 'transaction_id', data_get($event, 'event.transaction_id', ''));

        $interesting = [
            'INITIAL_PURCHASE',
            'RENEWAL',
            'UNCANCELLATION',
            'PRODUCT_CHANGE',
            'NON_RENEWING_PURCHASE',
        ];
        if ($type && ! in_array(strtoupper($type), $interesting, true)) {
            return response()->json(['success' => true, 'message' => 'ignored', 'type' => $type]);
        }

        $parsed = \App\Services\MobileIapService::parseProductId($productId);
        if (! $parsed) {
            return response()->json(['success' => true, 'message' => 'unknown_product', 'product_id' => $productId]);
        }

        // app_user_id format: doktor_{id}
        $doktorId = null;
        if (preg_match('/doktor[_-]?(\d+)/i', $appUserId, $m)) {
            $doktorId = (int) $m[1];
        } elseif (ctype_digit($appUserId)) {
            $doktorId = (int) $appUserId;
        }

        if (! $doktorId || ! class_exists(\App\Models\Doktor::class)) {
            return response()->json(['success' => true, 'message' => 'no_doctor']);
        }

        $doktor = \App\Models\Doktor::find($doktorId);
        $paket = \App\Models\Paket::where('aktif_mi', true)->find($parsed['paket_id']);
        if (! $doktor || ! $paket) {
            return response()->json(['success' => true, 'message' => 'not_found']);
        }

        if (method_exists($paket, 'klinikPaketiMi') && $paket->klinikPaketiMi()) {
            return response()->json(['success' => true, 'message' => 'klinik_skip']);
        }

        $iap = app(\App\Services\MobileIapService::class);
        if ($transactionId && $iap->transactionAlreadyUsed($transactionId)) {
            return response()->json(['success' => true, 'message' => 'already']);
        }

        $iap->activate($doktor, $paket, $parsed['period'], [
            'source' => 'revenuecat_webhook',
            'transaction_id' => $transactionId ?: null,
            'product_id' => $productId,
            'event_type' => $type,
        ]);

        return response()->json(['success' => true, 'message' => 'activated']);
    }
}
