<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * iyzico abonelik webhook — devre dışı.
 * Kartlı ödeme yalnızca PayTR; yeni iyzico olayı işlenmez.
 */
class IyzicoWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Iyzico webhook rejected — payment driver is PayTR only', [
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'disabled',
            'message' => 'iyzico kapalı. Ödeme sağlayıcısı: PayTR.',
        ], 410);
    }
}
