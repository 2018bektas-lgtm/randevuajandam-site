<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\WhatsAppWebhookIsle;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WhatsAppWebhookController extends Controller
{
    /**
     * Meta ilk kayit anindaki dogrulama isteği (GET).
     */
    public function verify(Request $request): Response
    {
        $mode = $request->query('hub_mode');
        $verifyToken = (string) config('whatsapp.webhook_verify_token');
        $challenge = (string) $request->query('hub_challenge', '');

        if (
            $mode === 'subscribe'
            && $verifyToken !== ''
            && hash_equals($verifyToken, (string) $request->query('hub_verify_token'))
        ) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    /**
     * Meta olay bildirimi (POST). HMAC-SHA256 imza dogrulanmadan islenmez.
     * Meta 20 saniye icinde 200 bekler; islemi kuyruga at.
     */
    public function handle(Request $request): Response
    {
        $secret = (string) config('whatsapp.app_secret');
        if ($secret === '') {
            return response('Not configured', 500);
        }

        $header = (string) $request->header('X-Hub-Signature-256', '');
        $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);

        if (! hash_equals($expected, $header)) {
            return response('Invalid signature', 403);
        }

        $payload = $request->all();
        if (! is_array($payload) || $payload === []) {
            return response('OK', 200);
        }

        WhatsAppWebhookIsle::dispatch($payload);

        return response('OK', 200);
    }
}
