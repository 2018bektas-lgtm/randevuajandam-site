<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Doktor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class IyzicoWebhookController extends Controller
{
    /**
     * Handle iyzico webhook requests.
     */
    public function handle(Request $request)
    {
        if (! $this->isValidSignature($request)) {
            Log::warning('Iyzico Webhook: invalid or missing signature', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $eventType = $request->input('eventType') ?? $request->input('iyziEventType');
        $subRef = $request->input('subscriptionReferenceCode')
            ?? $request->input('token')
            ?? data_get($request->all(), 'data.referenceCode');
        $eventId = (string) ($request->input('iyziEventId')
            ?? $request->input('eventId')
            ?? $request->header('X-Iyzico-Event-Id')
            ?? md5($request->getContent().'|'.$eventType.'|'.$subRef));

        // Idempotency — retries must not re-extend membership
        $idemKey = 'iyzico_webhook:'.$eventId;
        if (Cache::has($idemKey)) {
            Log::info('Iyzico Webhook: duplicate ignored', ['eventId' => $eventId]);

            return response()->json(['status' => 'success', 'duplicate' => true]);
        }

        Log::info('Iyzico Webhook received', [
            'eventType' => $eventType,
            'subscriptionReferenceCode' => $subRef,
            'eventId' => $eventId,
        ]);

        if (! $subRef) {
            return response()->json(['status' => 'error', 'message' => 'Missing subscriptionReferenceCode'], 400);
        }

        $doktor = Doktor::where('iyzico_subscription_reference_code', $subRef)->first();

        if (! $doktor) {
            Log::warning("Iyzico Webhook: Doctor not found for subscription reference code: {$subRef}");

            return response()->json(['status' => 'error', 'message' => 'Doctor not found'], 404);
        }

        switch ($eventType) {
            case 'subscription.order.success':
            case 'SUBSCRIPTION_PAYMENT_SUCCESS':
            case 'subscription.order.success.with.trial':
                $this->handlePaymentSuccess($doktor);
                break;

            case 'subscription.order.failure':
            case 'SUBSCRIPTION_PAYMENT_FAILURE':
                $this->handlePaymentFailure($doktor);
                break;

            case 'subscription.canceled':
            case 'subscription.cancelled':
            case 'SUBSCRIPTION_CANCELED':
            case 'SUBSCRIPTION_CANCELLED':
                $this->handleSubscriptionCancelled($doktor);
                break;

            default:
                Log::info('Iyzico Webhook: unhandled event type', ['eventType' => $eventType]);
                break;
        }

        Cache::put($idemKey, true, now()->addDays(7));

        return response()->json(['status' => 'success']);
    }

    /**
     * Validate webhook authenticity (header HMAC preferred).
     */
    protected function isValidSignature(Request $request): bool
    {
        $secret = (string) config('services.iyzico.webhook_secret', '');

        if ($secret === '') {
            // Never accept unsigned webhooks outside local/testing.
            return app()->environment('local', 'testing');
        }

        // Prefer headers only (no query-string secret in production)
        $provided = $request->header('X-Iyzico-Signature')
            ?? $request->header('X-Iyzico-Signature-V2')
            ?? $request->header('X-Webhook-Secret');

        // Local/testing may also accept body secret for manual curl tests
        if ((! is_string($provided) || $provided === '') && app()->environment('local', 'testing')) {
            $provided = $request->input('secret');
        }

        if (! is_string($provided) || $provided === '') {
            return false;
        }

        if (hash_equals($secret, $provided)) {
            return true;
        }

        $raw = $request->getContent();
        $hmac = hash_hmac('sha256', $raw, $secret);
        if (hash_equals($hmac, $provided)) {
            return true;
        }

        $hmacB64 = base64_encode(hash_hmac('sha256', $raw, $secret, true));

        return hash_equals($hmacB64, $provided);
    }

    protected function handlePaymentSuccess(Doktor $doktor): void
    {
        $periyot = $doktor->odeme_periyodu ?? 'aylik';
        // Extend from current end date if still active, else from now
        $base = $doktor->uyelik_bitis && $doktor->uyelik_bitis->isFuture()
            ? $doktor->uyelik_bitis->copy()
            : now();
        $bitis = $periyot === 'aylik' ? $base->copy()->addMonth() : $base->copy()->addYear();

        $doktor->update([
            'iyzico_subscription_status' => 'ACTIVE',
            'uyelik_bitis' => $bitis,
            'aktif_mi' => true,
        ]);

        if ($doktor->klinikSahibiMi() && $doktor->klinik) {
            $doktor->klinik->update([
                'uyelik_bitis' => $bitis,
                'aktif_mi' => true,
            ]);
        }

        Log::info("Iyzico Webhook: payment success doctor #{$doktor->id}, end={$bitis}");
    }

    protected function handlePaymentFailure(Doktor $doktor): void
    {
        $doktor->update([
            'iyzico_subscription_status' => 'UNPAID',
            'aktif_mi' => false,
        ]);

        if ($doktor->klinikSahibiMi() && $doktor->klinik) {
            $doktor->klinik->update(['aktif_mi' => false]);
        }

        Log::info("Iyzico Webhook: payment failure doctor #{$doktor->id}");
    }

    protected function handleSubscriptionCancelled(Doktor $doktor): void
    {
        $doktor->update([
            'iyzico_subscription_status' => 'CANCELLED',
            'aktif_mi' => false,
        ]);

        if ($doktor->klinikSahibiMi() && $doktor->klinik) {
            $doktor->klinik->update(['aktif_mi' => false]);
        }

        Log::info("Iyzico Webhook: cancelled doctor #{$doktor->id}");
    }
}
