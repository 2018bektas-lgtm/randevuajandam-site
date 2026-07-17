<?php

namespace App\Services;

use App\Models\Doktor;
use App\Models\DoktorDeviceToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Expo Push API (works with Expo Go and EAS builds using expo-notifications).
 * @see https://docs.expo.dev/push-notifications/sending-notifications/
 */
class ExpoPushService
{
    public function enabled(): bool
    {
        return (bool) config('services.expo_push.enabled', true);
    }

    public function sendToDoktor(Doktor $doktor, string $title, string $body, array $data = []): int
    {
        if (! $this->enabled()) {
            return 0;
        }

        $tokens = DoktorDeviceToken::query()
            ->where('doktor_id', $doktor->id)
            ->pluck('token')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($tokens === []) {
            return 0;
        }

        $sent = 0;
        foreach (array_chunk($tokens, 100) as $chunk) {
            $sent += $this->sendChunk($chunk, $title, $body, $data);
        }

        return $sent;
    }

    /**
     * @param  array<int, string>  $tokens
     */
    protected function sendChunk(array $tokens, string $title, string $body, array $data): int
    {
        $messages = [];
        foreach ($tokens as $token) {
            // Expo Push: title+body = "notification" mesajı (kilit ekranı / tray).
            // Sadece data gönderilirse Android'de sessiz kalabilir — title/body zorunlu.
            $messages[] = [
                'to' => $token,
                'sound' => 'default',
                'title' => $title,
                'body' => $body !== '' ? $body : $title,
                'data' => $data,
                // high = FCM priority high → Doze / kilit ekranı için gerekli
                'priority' => 'high',
                // Mobilde setNotificationChannelAsync('randevu') ile eşleşmeli
                'channelId' => (string) ($data['channelId'] ?? 'randevu'),
                'ttl' => 3600,
                // iOS için; Android'de zararsız
                'mutableContent' => true,
            ];
        }

        try {
            $res = Http::acceptJson()
                ->timeout(12)
                ->post('https://exp.host/--/api/v2/push/send', $messages);

            if (! $res->successful()) {
                Log::warning('Expo push failed', ['status' => $res->status(), 'body' => $res->json()]);

                return 0;
            }

            $json = $res->json();
            $dataRows = $json['data'] ?? $json;
            if (! is_array($dataRows)) {
                return 0;
            }

            $ok = 0;
            foreach ($dataRows as $i => $row) {
                $status = is_array($row) ? ($row['status'] ?? null) : null;
                if ($status === 'ok') {
                    $ok++;
                    continue;
                }
                $details = is_array($row) ? ($row['details']['error'] ?? $row['message'] ?? null) : null;
                if (in_array($details, ['DeviceNotRegistered', 'InvalidCredentials'], true)
                    || (is_string($details) && str_contains(strtolower((string) $details), 'not registered'))) {
                    $bad = $tokens[$i] ?? null;
                    if ($bad) {
                        DoktorDeviceToken::where('token', $bad)->delete();
                    }
                }
            }

            return $ok;
        } catch (\Throwable $e) {
            Log::error('Expo push exception: '.$e->getMessage());

            return 0;
        }
    }
}
