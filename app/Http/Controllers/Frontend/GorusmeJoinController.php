<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Randevu;
use App\Services\MeetingRoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

/**
 * Platform online görüşme — WebRTC (hesap yok) veya isteğe bağlı Jitsi.
 */
class GorusmeJoinController extends Controller
{
    public function __construct(protected MeetingRoomService $meetings) {}

    public function join(Request $request, string $token): View|Response|RedirectResponse
    {
        if ($deny = $this->rateLimit($request, $token)) {
            return $deny;
        }

        $randevu = Randevu::with(['doktor:id,ad_soyad,unvan', 'hizmet:id,ad'])
            ->where('meeting_join_token', $token)
            ->first();

        if (! $randevu) {
            abort(404, 'Görüşme bağlantısı geçersiz.');
        }

        return $this->renderJoin($request, $randevu, 'hasta');
    }

    public function hekimJoin(Request $request, int $id): View|Response|RedirectResponse
    {
        $doktor = Auth::guard('doktor')->user();
        if (! $doktor) {
            return redirect()->route('frontend.hekim.giris');
        }

        $randevu = Randevu::with(['doktor', 'hizmet', 'hasta'])
            ->where('doktor_id', $doktor->id)
            ->findOrFail($id);

        return $this->renderJoin($request, $randevu, 'hekim');
    }

    public function signalByToken(Request $request, string $token): JsonResponse
    {
        if ($this->tooMany($request, 'sig:'.$token, 120)) {
            return response()->json(['success' => false, 'message' => 'Çok fazla istek.'], 429);
        }

        $randevu = Randevu::where('meeting_join_token', $token)->first();
        if (! $randevu || ! $randevu->isOnline()) {
            return response()->json(['success' => false, 'message' => 'Geçersiz görüşme.'], 404);
        }

        return $this->handleSignal($request, $randevu, 'hasta');
    }

    public function signalById(Request $request, int $id): JsonResponse
    {
        $doktor = Auth::guard('doktor')->user();
        if (! $doktor) {
            return response()->json(['success' => false, 'message' => 'Oturum gerekli.'], 401);
        }

        $randevu = Randevu::where('doktor_id', $doktor->id)->find($id);
        if (! $randevu || ! $randevu->isOnline()) {
            return response()->json(['success' => false, 'message' => 'Geçersiz görüşme.'], 404);
        }

        return $this->handleSignal($request, $randevu, 'hekim');
    }

    protected function handleSignal(Request $request, Randevu $randevu, string $role): JsonResponse
    {
        $this->meetings->ensureRoom($randevu);
        $randevu->refresh();

        if (! $this->meetings->canJoin($randevu)) {
            return response()->json([
                'success' => false,
                'message' => 'Görüşme penceresi kapalı.',
                'can_join' => false,
            ], 403);
        }

        $roomId = (string) $randevu->meeting_room_id;

        if ($request->isMethod('get')) {
            $state = $this->meetings->getSignalState($roomId);

            return response()->json([
                'success' => true,
                'role' => $role,
                'room' => $roomId,
                'state' => $state,
                'ice_servers' => $this->meetings->iceServers(),
            ]);
        }

        $data = $request->validate([
            'type' => 'required|string|in:ping,offer,answer,ice,hangup,reset',
            'sdp' => 'nullable|string|max:200000',
            'candidate' => 'nullable|array',
            'name' => 'nullable|string|max:120',
        ]);

        $state = $this->meetings->applySignal($roomId, $role, $data);

        return response()->json([
            'success' => true,
            'role' => $role,
            'state' => $state,
        ]);
    }

    protected function renderJoin(Request $request, Randevu $randevu, string $role): View|Response|RedirectResponse
    {
        if (! $randevu->isOnline()) {
            abort(422, 'Bu randevu online görüşme değil.');
        }

        if ($randevu->durum !== 'onaylandi') {
            return response()->view('frontend.gorusme.beklemede', [
                'randevu' => $randevu,
                'mesaj' => $randevu->durum === 'beklemede'
                    ? 'Randevunuz henüz onaylanmadı. Onay sonrası görüşme odası açılacaktır.'
                    : 'Bu randevu için görüşme açılamaz (durum: '.$randevu->durum.').',
            ], 403);
        }

        $this->meetings->ensureRoom($randevu);
        $randevu->refresh();

        $canJoin = $this->meetings->canJoin($randevu);
        $window = $this->meetings->joinWindow($randevu);

        if ($role === 'hekim') {
            $displayName = trim(($randevu->doktor?->unvan ? $randevu->doktor->unvan.' ' : '').($randevu->doktor?->ad_soyad ?? 'Hekim'));
        } else {
            $displayName = trim(($randevu->ad ?? '').' '.($randevu->soyad ?? ''));
            if ($displayName === '') {
                $displayName = 'Katılımcı';
            }
        }

        $isPlatform = in_array($randevu->meeting_provider ?? 'platform', ['platform', 'webrtc'], true)
            || config('gorusme.provider', 'platform') === 'platform';

        // Eski jitsi modu: harici yönlendirme
        if (! $isPlatform && $canJoin) {
            $directUrl = $this->meetings->directRoomUrl($randevu, $displayName);
            if ($directUrl && (bool) config('gorusme.auto_redirect', false) && ! $request->boolean('stay')) {
                return redirect()->away($directUrl);
            }
        }

        $signalUrl = $role === 'hekim'
            ? url('/hekim/gorusme/'.$randevu->id.'/signal')
            : url('/gorusme/'.$randevu->meeting_join_token.'/signal');

        $response = response()->view('frontend.gorusme.join', [
            'randevu' => $randevu,
            'canJoin' => $canJoin,
            'window' => $window,
            'role' => $role,
            'displayName' => $displayName,
            'jitsiUrl' => $randevu->meeting_url,
            'directUrl' => $isPlatform ? null : $this->meetings->directRoomUrl($randevu, $displayName),
            'roomName' => $randevu->meeting_room_id,
            'isPlatform' => $isPlatform,
            'signalUrl' => $signalUrl,
            'iceServers' => $this->meetings->iceServers(),
            'csrf' => csrf_token(),
        ]);

        // Tarayıcıya kamera/mikrofon izni isteğine izin ver
        $response->headers->set('Permissions-Policy', 'camera=(self), microphone=(self), display-capture=(self)');
        $response->headers->set('Feature-Policy', "camera 'self'; microphone 'self'");

        return $response;
    }

    protected function tooMany(Request $request, string $keyPart, int $max = 0): bool
    {
        $max = $max > 0 ? $max : (int) config('gorusme.join_rate_max', 60);
        $decay = (int) config('gorusme.join_rate_decay', 60);
        $key = 'gorusme-join:'.$request->ip().':'.substr(hash('sha256', $keyPart), 0, 16);

        if (RateLimiter::tooManyAttempts($key, $max)) {
            return true;
        }
        RateLimiter::hit($key, $decay);

        return false;
    }

    protected function rateLimit(Request $request, string $keyPart, int $max = 0): ?Response
    {
        if ($this->tooMany($request, $keyPart, $max)) {
            return response()->view('frontend.gorusme.beklemede', [
                'randevu' => null,
                'mesaj' => 'Çok fazla istek. Lütfen bir süre sonra tekrar deneyin.',
            ], 429);
        }

        return null;
    }
}

