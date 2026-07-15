<?php

namespace App\Services;

use App\Models\Randevu;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Platform online görüşme odası.
 * Varsayılan: WebRTC (hesap yok). İsteğe bağlı harici Jitsi.
 */
class MeetingRoomService
{
    public function ensureRoom(Randevu $randevu): Randevu
    {
        if (! $randevu->isOnline()) {
            return $randevu;
        }

        $provider = (string) config('gorusme.provider', 'platform');
        if (! in_array($provider, ['platform', 'webrtc', 'jitsi'], true)) {
            $provider = 'platform';
        }
        if ($provider === 'webrtc') {
            $provider = 'platform';
        }

        $roomId = $randevu->meeting_room_id ?: $this->makeRoomId($randevu);
        $token = $randevu->meeting_join_token ?: Str::random(48);

        // Token önce kaydedilsin ki platformJoinUrl çalışsın
        $randevu->forceFill([
            'meeting_provider' => $provider,
            'meeting_room_id' => $roomId,
            'meeting_join_token' => $token,
        ])->save();
        $randevu = $randevu->fresh() ?? $randevu;

        if ($provider === 'jitsi') {
            $base = rtrim((string) config('gorusme.jitsi_base_url', 'https://framatalk.org'), '/');
            $url = $base.'/'.$roomId;
        } else {
            $url = $this->platformJoinUrl($randevu) ?: ('/gorusme/'.$token);
        }

        if ((string) $randevu->meeting_url !== $url || $randevu->meeting_provider !== $provider) {
            $randevu->forceFill([
                'meeting_provider' => $provider,
                'meeting_url' => $url,
            ])->save();
        }

        return $randevu->fresh() ?? $randevu;
    }

    public function isPlatformProvider(?Randevu $randevu = null): bool
    {
        $p = $randevu?->meeting_provider ?: config('gorusme.provider', 'platform');

        return in_array($p, ['platform', 'webrtc'], true)
            || ! in_array($p, ['jitsi'], true) && config('gorusme.provider', 'platform') !== 'jitsi';
    }

    /**
     * @deprecated Jitsi only — platform modunda null
     */
    public function directRoomUrl(Randevu $randevu, ?string $displayName = null): ?string
    {
        $randevu = $this->ensureRoom($randevu);
        if (($randevu->meeting_provider ?? '') !== 'jitsi' || ! $randevu->meeting_url) {
            return null;
        }

        $name = trim((string) $displayName) ?: 'Katilimci';
        $hash = 'userInfo.displayName="'.rawurlencode($name).'"'
            .'&config.prejoinPageEnabled=false'
            .'&config.prejoinConfig.enabled=false'
            .'&config.requireDisplayName=false'
            .'&config.disableDeepLinking=true';

        return rtrim((string) $randevu->meeting_url, '#').'#'.$hash;
    }

    public function makeRoomId(Randevu $randevu): string
    {
        $secret = (string) config('app.key');
        $hash = substr(hash_hmac('sha256', 'meet|'.$randevu->id, $secret), 0, 16);

        return 'ra'.$randevu->id.'-'.$hash;
    }

    public function platformJoinUrl(Randevu $randevu): ?string
    {
        if (! $randevu->meeting_join_token) {
            return null;
        }

        try {
            return route('frontend.gorusme.join', ['token' => $randevu->meeting_join_token]);
        } catch (\Throwable) {
            $site = rtrim((string) (env('SITE_URL') ?: config('app.url')), '/');

            return $site.'/gorusme/'.$randevu->meeting_join_token;
        }
    }

    /**
     * @return array{0: Carbon, 1: Carbon}|null
     */
    public function joinWindow(Randevu $randevu): ?array
    {
        if (! $randevu->tarih || ! $randevu->saat) {
            return null;
        }

        if ($randevu->meeting_baslangic_at && $randevu->meeting_bitis_at) {
            return [
                Carbon::parse($randevu->meeting_baslangic_at),
                Carbon::parse($randevu->meeting_bitis_at),
            ];
        }

        $tarih = $randevu->tarih instanceof \DateTimeInterface
            ? $randevu->tarih->format('Y-m-d')
            : substr((string) $randevu->tarih, 0, 10);
        $saat = substr((string) $randevu->saat, 0, 5);
        $start = Carbon::parse($tarih.' '.$saat);
        $early = (int) config('gorusme.join_early_minutes', 15);
        $late = (int) config('gorusme.join_late_minutes', 120);

        return [$start->copy()->subMinutes($early), $start->copy()->addMinutes($late)];
    }

    public function canJoin(Randevu $randevu, ?Carbon $now = null): bool
    {
        if (! $randevu->isOnline()) {
            return false;
        }
        if ($randevu->durum !== 'onaylandi') {
            return false;
        }
        if (! $randevu->meeting_room_id || ! $randevu->meeting_join_token) {
            return false;
        }

        $now = $now ?: now();
        $window = $this->joinWindow($randevu);
        if (! $window) {
            return false;
        }

        return $now->betweenIncluded($window[0], $window[1]);
    }

    protected function signalKey(string $roomId): string
    {
        return 'gorusme-signal:'.$roomId;
    }

    /**
     * @return array{hekim: array, hasta: array, updated_at: string|null}
     */
    public function getSignalState(string $roomId): array
    {
        $ttl = (int) config('gorusme.signal_ttl', 7200);
        $state = Cache::get($this->signalKey($roomId));
        if (! is_array($state)) {
            $state = [
                'hekim' => ['joined' => false, 'offer' => null, 'answer' => null, 'ice' => [], 'name' => null],
                'hasta' => ['joined' => false, 'offer' => null, 'answer' => null, 'ice' => [], 'name' => null],
                'updated_at' => null,
            ];
            Cache::put($this->signalKey($roomId), $state, $ttl);
        }

        return $state;
    }

    /**
     * @param  array{type: string, sdp?: string|null, candidate?: array|null, name?: string|null}  $payload
     * @return array{hekim: array, hasta: array, updated_at: string|null}
     */
    public function applySignal(string $roomId, string $role, array $payload): array
    {
        $role = $role === 'hekim' ? 'hekim' : 'hasta';
        $other = $role === 'hekim' ? 'hasta' : 'hekim';
        $ttl = (int) config('gorusme.signal_ttl', 7200);
        $state = $this->getSignalState($roomId);
        $type = (string) ($payload['type'] ?? '');

        $state[$role]['joined'] = true;
        if (! empty($payload['name'])) {
            $state[$role]['name'] = (string) $payload['name'];
        }

        if ($type === 'ping') {
            // presence only
        } elseif ($type === 'offer' && ! empty($payload['sdp'])) {
            $state[$role]['offer'] = (string) $payload['sdp'];
            $state[$role]['answer'] = null;
            $state[$role]['ice'] = [];
            // new offer → clear other side answer
            $state[$other]['answer'] = null;
        } elseif ($type === 'answer' && ! empty($payload['sdp'])) {
            $state[$role]['answer'] = (string) $payload['sdp'];
        } elseif ($type === 'ice' && ! empty($payload['candidate'])) {
            $cand = $payload['candidate'];
            if (is_array($cand)) {
                $state[$role]['ice'][] = $cand;
                // keep last 40
                $state[$role]['ice'] = array_slice($state[$role]['ice'], -40);
            }
        } elseif ($type === 'hangup') {
            $state[$role] = ['joined' => false, 'offer' => null, 'answer' => null, 'ice' => [], 'name' => $state[$role]['name'] ?? null];
        } elseif ($type === 'reset') {
            $state = [
                'hekim' => ['joined' => false, 'offer' => null, 'answer' => null, 'ice' => [], 'name' => null],
                'hasta' => ['joined' => false, 'offer' => null, 'answer' => null, 'ice' => [], 'name' => null],
                'updated_at' => null,
            ];
        }

        $state['updated_at'] = now()->toIso8601String();
        Cache::put($this->signalKey($roomId), $state, $ttl);

        return $state;
    }

    /**
     * @return list<array{urls: string|array, username?: string, credential?: string}>
     */
    public function iceServers(): array
    {
        $servers = config('gorusme.ice_servers', []);
        if (! is_array($servers) || $servers === []) {
            return [
                ['urls' => 'stun:stun.l.google.com:19302'],
                [
                    'urls' => [
                        'turn:openrelay.metered.ca:80',
                        'turn:openrelay.metered.ca:443',
                    ],
                    'username' => 'openrelayproject',
                    'credential' => 'openrelayproject',
                ],
            ];
        }

        return array_values($servers);
    }
}
