<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\PullGoogleBloklariJob;
use App\Models\Doktor;
use App\Models\Klinik;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Google Calendar push notification'i (watch channel).
 * Google -> POST /api/google-calendar/webhook (headers: X-Goog-Channel-*).
 * Body genelde bostur; degisiklik detayi yerine sadece "bir sey degisti"
 * sinyali verir. Biz de ilgili hekim(ler) icin PullGoogleBloklariJob dispatch ederiz.
 */
class GoogleCalendarWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $token = (string) $request->header('X-Goog-Channel-Token', '');
        $expected = (string) config('google_calendar.webhook_token', '');

        if ($expected === '' || ! hash_equals($expected, $token)) {
            return response('unauthorized', 401);
        }

        $channelId = (string) $request->header('X-Goog-Channel-ID', '');
        $resourceId = (string) $request->header('X-Goog-Resource-ID', '');
        $state = (string) $request->header('X-Goog-Resource-State', '');

        // Ilk kayit onayi
        if ($state === 'sync') {
            return response('ok', 200);
        }

        if ($channelId === '') {
            return response('ok', 200);
        }

        // channel_id'yi kim tuttuysa o ilgili hekim(ler)
        $doktor = Doktor::query()
            ->whereNotNull('google_calendar_config')
            ->get()
            ->first(function (Doktor $d) use ($channelId) {
                $cfg = $d->google_calendar_config ?? [];
                return ($cfg['channel_id'] ?? null) === $channelId;
            });

        if ($doktor) {
            PullGoogleBloklariJob::dispatch($doktor->id);
            return response('ok', 200);
        }

        // Klinik channel'i? -> altındaki tum hekimler icin dispatch
        $klinik = Klinik::query()
            ->whereNotNull('google_calendar_config')
            ->get()
            ->first(function (Klinik $k) use ($channelId) {
                $cfg = $k->google_calendar_config ?? [];
                return ($cfg['channel_id'] ?? null) === $channelId;
            });

        if ($klinik) {
            foreach ($klinik->doktorlar()->where('aktif_mi', true)->pluck('id') as $doktorId) {
                PullGoogleBloklariJob::dispatch((int) $doktorId);
            }
            return response('ok', 200);
        }

        Log::info('Google webhook: eslesen channel yok', ['channel_id' => $channelId, 'resource_id' => $resourceId]);
        return response('ok', 200);
    }
}
