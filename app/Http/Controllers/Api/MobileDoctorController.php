<?php

namespace App\Http\Controllers\Api;

use App\Events\RandevuDurumuDegisti;
use App\Http\Controllers\Controller;
use App\Models\Doktor;
use App\Models\DoktorApiToken;
use App\Models\DoktorDeviceToken;
use App\Models\Hasta;
use App\Services\AppointmentBookingService;
use App\Services\TwoFactorService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class MobileDoctorController extends Controller
{
    public function __construct(
        protected TwoFactorService $twoFactor,
    ) {}

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'e_posta' => ['required', 'email'],
            'sifre' => ['required', 'string'],
            'device' => ['nullable', 'string', 'max:120'],
        ]);

        $key = 'mobile-doktor-login:'.Str::lower($data['e_posta']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 8)) {
            return response()->json(['success' => false, 'message' => 'Çok fazla deneme. Lütfen bekleyin.'], 429);
        }

        $doktor = Doktor::where('e_posta', $data['e_posta'])->first();
        if (! $doktor || ! Hash::check($data['sifre'], $doktor->sifre)) {
            RateLimiter::hit($key, 300);

            return response()->json(['success' => false, 'message' => 'E-posta veya şifre hatalı.'], 422);
        }
        if (! $doktor->aktif_mi) {
            return response()->json(['success' => false, 'message' => 'Hesabınız pasif durumdadır.'], 403);
        }

        RateLimiter::clear($key);
        if ($doktor->hasTwoFactorEnabled()) {
            $challenge = Str::random(64);
            Cache::put(
                $this->challengeCacheKey($challenge),
                ['doktor_id' => $doktor->id, 'device' => $data['device'] ?? null],
                now()->addMinutes(5),
            );

            return response()->json([
                'success' => true,
                'data' => ['requires_two_factor' => true, 'challenge_token' => $challenge],
            ]);
        }

        return $this->authenticatedResponse($doktor, $data['device'] ?? null, $request->ip());
    }

    public function verifyTwoFactor(Request $request): JsonResponse
    {
        $data = $request->validate([
            'challenge_token' => ['required', 'string', 'size:64'],
            'code' => ['required', 'string', 'min:6', 'max:20'],
        ]);

        $challenge = Cache::get($this->challengeCacheKey($data['challenge_token']));
        if (! is_array($challenge)) {
            return response()->json(['success' => false, 'message' => 'Doğrulama oturumu sona erdi. Lütfen tekrar giriş yapın.'], 422);
        }

        $key = 'mobile-doktor-2fa:'.hash('sha256', $data['challenge_token']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 8)) {
            return response()->json(['success' => false, 'message' => 'Çok fazla deneme. Lütfen bekleyin.'], 429);
        }

        $doktor = Doktor::find($challenge['doktor_id'] ?? null);
        if (! $doktor || ! $doktor->aktif_mi || ! $doktor->hasTwoFactorEnabled()) {
            Cache::forget($this->challengeCacheKey($data['challenge_token']));

            return response()->json(['success' => false, 'message' => 'Doğrulama oturumu geçersiz.'], 422);
        }

        if (! $this->twoFactor->verifyUser($doktor, $data['code'])) {
            RateLimiter::hit($key, 300);

            return response()->json(['success' => false, 'message' => 'Doğrulama kodu hatalı.'], 422);
        }

        RateLimiter::clear($key);
        Cache::forget($this->challengeCacheKey($data['challenge_token']));

        return $this->authenticatedResponse($doktor, $challenge['device'] ?? null, $request->ip());
    }

    /**
     * Public: registration form options (unvan, branş, iller).
     */
    public function registerMeta(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'unvanlar' => class_exists(\App\Models\Unvan::class)
                    ? \App\Models\Unvan::query()->orderBy('ad')->get(['id', 'ad'])
                    : [],
                'branslar' => \App\Models\Brans::query()->orderBy('ad')->get(['id', 'ad']),
                'iller' => \App\Models\Il::query()->orderBy('ad')->get(['id', 'ad']),
            ],
        ]);
    }

    public function registerMetaIlceler(Request $request): JsonResponse
    {
        $ilId = (int) $request->input('il_id');
        $ilceler = $ilId > 0
            ? \App\Models\Ilce::query()->where('il_id', $ilId)->orderBy('ad')->get(['id', 'ad', 'il_id'])
            : [];

        return response()->json(['success' => true, 'data' => ['ilceler' => $ilceler]]);
    }

    /**
     * Public: doctor self-registration (native app form).
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ad_soyad' => ['required', 'string', 'max:255'],
            'e_posta' => ['required', 'email', 'max:255', 'unique:doktorlar,e_posta'],
            'sifre' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'telefon' => ['required', 'string', 'max:50'],
            'unvan' => ['required', 'string', 'max:100'],
            'il_id' => ['required', 'integer', 'exists:iller,id'],
            'ilce_id' => ['required', 'integer', 'exists:ilceler,id'],
            'branslar' => ['required', 'array', 'min:1'],
            'branslar.*' => ['integer', 'exists:branslar,id'],
            'mezuniyet' => ['nullable', 'array'],
            'mezuniyet.*' => ['nullable', 'string', 'max:255'],
            'biyografi' => ['nullable', 'string', 'max:5000'],
            'device' => ['nullable', 'string', 'max:120'],
        ], [
            'e_posta.unique' => 'Bu e-posta adresi zaten kayıtlı.',
            'sifre.confirmed' => 'Şifre tekrarı uyuşmuyor.',
            'branslar.required' => 'En az bir branş seçmelisiniz.',
        ]);

        $bransIsimleri = \App\Models\Brans::whereIn('id', $data['branslar'])->pluck('ad')->toArray();
        $mezuniyet = array_values(array_filter($data['mezuniyet'] ?? [], fn ($v) => $v !== null && trim((string) $v) !== ''));

        $doktor = \Illuminate\Support\Facades\DB::transaction(function () use ($data, $bransIsimleri, $mezuniyet) {
            $doktor = Doktor::create([
                'ad_soyad' => $data['ad_soyad'],
                'e_posta' => mb_strtolower(trim($data['e_posta'])),
                'sifre' => Hash::make($data['sifre']),
                'telefon' => $data['telefon'],
                'il_id' => $data['il_id'],
                'ilce_id' => $data['ilce_id'],
                'unvan' => $data['unvan'],
                'uzmanlik_alani' => implode(', ', $bransIsimleri),
                'mezuniyet' => $mezuniyet,
                'biyografi' => $data['biyografi'] ?? null,
                'tur' => 'bireysel',
                'paket_id' => null,
                'aktif_mi' => true,
            ]);
            $doktor->branslar()->attach($data['branslar']);

            return $doktor;
        });

        return $this->authenticatedResponse($doktor, $data['device'] ?? 'mobile-register', $request->ip());
    }

    /**
     * Public: request password reset email (hekim).
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'e_posta' => ['required', 'email'],
        ]);
        $email = mb_strtolower(trim($data['e_posta']));
        $key = 'mobile-doktor-forgot:'.$email.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['success' => false, 'message' => 'Çok fazla deneme. Lütfen bekleyin.'], 429);
        }
        RateLimiter::hit($key, 600);

        $user = Doktor::where('e_posta', $email)->first();
        // Always same message (anti-enumeration)
        $msg = 'Hesabınız varsa şifre sıfırlama bağlantısı e-posta adresinize gönderildi.';

        if ($user) {
            $token = Str::random(60);
            \Illuminate\Support\Facades\DB::table('password_reset_tokens')
                ->where('email', $email)
                ->where('account_type', 'hekim')
                ->delete();
            \Illuminate\Support\Facades\DB::table('password_reset_tokens')->insert([
                'email' => $email,
                'account_type' => 'hekim',
                'token' => Hash::make($token),
                'created_at' => now(),
            ]);
            try {
                $user->notify(new \App\Notifications\SifreSifirlamaLinkBildirimi($token, 'hekim'));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Mobile forgot password mail: '.$e->getMessage());
            }
        }

        return response()->json(['success' => true, 'message' => $msg]);
    }

    /**
     * Public: reset password with token from email.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'e_posta' => ['required', 'email'],
            'token' => ['required', 'string'],
            'sifre' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'sifre.confirmed' => 'Şifre tekrarı uyuşmuyor.',
            'sifre.min' => 'Şifre en az 8 karakter olmalıdır.',
        ]);

        $email = mb_strtolower(trim($data['e_posta']));
        $record = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('account_type', 'hekim')
            ->first();

        if (! $record || ! Hash::check($data['token'], $record->token)) {
            return response()->json(['success' => false, 'message' => 'Geçersiz veya süresi dolmuş bağlantı.'], 422);
        }
        if (now()->subMinutes(60)->gt($record->created_at)) {
            \Illuminate\Support\Facades\DB::table('password_reset_tokens')
                ->where('email', $email)
                ->where('account_type', 'hekim')
                ->delete();

            return response()->json(['success' => false, 'message' => 'Bağlantının süresi dolmuş. Yeni talep oluşturun.'], 422);
        }

        $user = Doktor::where('e_posta', $email)->first();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Kullanıcı bulunamadı.'], 422);
        }

        $user->update(['sifre' => Hash::make($data['sifre'])]);
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('account_type', 'hekim')
            ->delete();

        return response()->json(['success' => true, 'message' => 'Şifreniz güncellendi. Giriş yapabilirsiniz.']);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');

        return response()->json(['success' => true, 'data' => $this->doktorPayload($doktor)]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var DoktorApiToken|null $token */
        $token = $request->attributes->get('auth_doktor_token');
        $token?->delete();

        return response()->json(['success' => true, 'message' => 'Çıkış yapıldı.']);
    }

    /**
     * List the authenticated doctor's appointments.
     *
     * Query params:
     *  - tarih: Y-m-d, defaults to today. Ignored when range=upcoming.
     *  - range: "gun" (default, single day) or "yaklasan" (next 14 days, excluding iptal).
     */
    public function appointments(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');

        $range = $request->string('range')->value() ?: 'gun';

        $query = $doktor->randevular()->with(['hasta:id,ad,soyad,telefon', 'hizmet:id,ad,sure']);

        if ($range === 'yaklasan') {
            $bugun = Carbon::today();
            $query->whereBetween('tarih', [$bugun->toDateString(), $bugun->copy()->addDays(14)->toDateString()])
                ->whereIn('durum', ['beklemede', 'onaylandi']);
        } else {
            $tarih = $request->filled('tarih') ? Carbon::parse($request->string('tarih')->value()) : Carbon::today();
            $query->whereDate('tarih', $tarih->toDateString())
                ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi', 'iptal']);
        }

        $randevular = $query->orderBy('tarih')->orderBy('saat')->get();

        return response()->json([
            'success' => true,
            'data' => $randevular->map(fn ($r) => $this->appointmentPayload($r))->values(),
        ]);
    }

    /**
     * Update the status of one of the authenticated doctor's appointments.
     */
    public function updateAppointmentStatus(Request $request, int $id): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $randevu = $doktor->randevular()->findOrFail($id);

        $data = $request->validate([
            'durum' => ['required', 'in:onaylandi,iptal,tamamlandi,beklemede'],
            'hekim_notu' => ['nullable', 'string', 'max:1000'],
        ], [
            'durum.required' => 'Durum alanı zorunludur.',
            'durum.in' => 'Geçersiz randevu durumu.',
        ]);

        $eskiDurum = $randevu->durum;
        $randevu->update([
            'durum' => $data['durum'],
            'hekim_notu' => $data['hekim_notu'] ?? $randevu->hekim_notu,
        ]);

        if ($eskiDurum !== $data['durum']) {
            RandevuDurumuDegisti::dispatch($randevu, $eskiDurum, $data['durum']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Randevu durumu güncellendi.',
            'data' => $this->appointmentPayload($randevu->fresh(['hasta', 'hizmet'])),
        ]);
    }

    /**
     * Single appointment detail for doctor mobile.
     */
    public function showAppointment(Request $request, int $id): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $randevu = $doktor->randevular()->with(['hasta', 'hizmet'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->appointmentPayload($randevu, true),
        ]);
    }

    /**
     * Online görüşme oturumu — mobil uygulama içi (WebView / native) için.
     */
    public function meetingSession(Request $request, int $id): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $randevu = $doktor->randevular()->with(['hasta', 'hizmet', 'doktor'])->findOrFail($id);

        if (! method_exists($randevu, 'isOnline') || ! $randevu->isOnline()) {
            return response()->json(['success' => false, 'message' => 'Bu randevu online görüşme değil.'], 422);
        }
        if ($randevu->durum !== 'onaylandi') {
            return response()->json([
                'success' => false,
                'message' => 'Görüşme için randevunun onaylı olması gerekir.',
                'data' => ['can_join' => false, 'durum' => $randevu->durum],
            ], 422);
        }

        $meet = app(\App\Services\MeetingRoomService::class);
        $randevu = $meet->ensureRoom($randevu);
        $canJoin = $meet->canJoin($randevu);
        $window = $meet->joinWindow($randevu);

        $hostPeerId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $randevu->meeting_room_id) ?: ('ra'.$randevu->id);
        $hostPeerId = substr($hostPeerId, 0, 60);
        $signalUrl = url('/api/mobile/v1/doctor/appointments/'.$randevu->id.'/meeting/signal');

        return response()->json([
            'success' => true,
            'data' => [
                'randevu_id' => $randevu->id,
                'can_join' => $canJoin,
                'online_mi' => true,
                'role' => 'hekim',
                'display_name' => (string) ($doktor->ad_soyad ?? 'Hekim'),
                'room' => $randevu->meeting_room_id,
                'host_peer_id' => $hostPeerId,
                'ice_servers' => $meet->iceServers(),
                'peerjs' => config('gorusme.peerjs', [
                    'host' => '0.peerjs.com',
                    'port' => 443,
                    'path' => '/',
                    'secure' => true,
                    'key' => 'peerjs',
                ]),
                'signal_url' => $signalUrl,
                'window' => $window ? [
                    'baslangic' => $window[0]->toIso8601String(),
                    'bitis' => $window[1]->toIso8601String(),
                ] : null,
                'hasta_adi' => trim(($randevu->hasta->ad ?? $randevu->ad).' '.($randevu->hasta->soyad ?? $randevu->soyad)),
                'tarih' => $randevu->tarih instanceof \DateTimeInterface
                    ? $randevu->tarih->format('Y-m-d')
                    : (string) $randevu->tarih,
                'saat' => substr((string) $randevu->saat, 0, 5),
            ],
        ]);
    }

    /**
     * WebRTC DIY signal (hekim) — native / in-app room uses this instead of website pages.
     */
    public function meetingSignal(Request $request, int $id): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $randevu = $doktor->randevular()->findOrFail($id);

        if (! method_exists($randevu, 'isOnline') || ! $randevu->isOnline()) {
            return response()->json(['success' => false, 'message' => 'Bu randevu online görüşme değil.'], 422);
        }

        $meet = app(\App\Services\MeetingRoomService::class);
        $randevu = $meet->ensureRoom($randevu);

        if (! $meet->canJoin($randevu)) {
            return response()->json([
                'success' => false,
                'message' => 'Görüşme penceresi kapalı.',
                'can_join' => false,
            ], 403);
        }

        $roomId = (string) $randevu->meeting_room_id;
        $role = 'hekim';

        if ($request->isMethod('get')) {
            return response()->json([
                'success' => true,
                'state' => $meet->getSignalState($roomId),
            ]);
        }

        $payload = $request->validate([
            'type' => ['required', 'string', 'in:ping,offer,answer,ice,hangup,reset'],
            'sdp' => ['nullable', 'string'],
            'candidate' => ['nullable', 'array'],
            'name' => ['nullable', 'string', 'max:120'],
        ]);

        $state = $meet->applySignal($roomId, $role, $payload);

        return response()->json(['success' => true, 'state' => $state]);
    }

    /**
     * Update appointment service / notes (hekim panel update).
     */
    public function updateAppointment(Request $request, int $id): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $randevu = $doktor->randevular()->findOrFail($id);

        $data = $request->validate([
            'hizmet_id' => ['sometimes', 'nullable', 'integer', 'exists:hizmetler,id'],
            'aciklama' => ['nullable', 'string', 'max:1000'],
            'hekim_notu' => ['nullable', 'string', 'max:1000'],
            'gorusme_tipi' => ['nullable', 'in:yuz_yuze,online'],
        ]);

        if (array_key_exists('hizmet_id', $data) && $data['hizmet_id']) {
            $hizmet = $doktor->hizmetler()->where('id', $data['hizmet_id'])->first();
            if (! $hizmet) {
                return response()->json(['success' => false, 'message' => 'Seçilen hizmet size ait değil.'], 422);
            }
            $randevu->hizmet_id = $hizmet->id;
        }

        if (array_key_exists('aciklama', $data)) {
            $randevu->not = $data['aciklama'];
        }
        if (array_key_exists('hekim_notu', $data)) {
            $randevu->hekim_notu = $data['hekim_notu'];
        }
        if (! empty($data['gorusme_tipi'])) {
            $randevu->gorusme_tipi = $data['gorusme_tipi'];
        }
        $randevu->save();

        return response()->json([
            'success' => true,
            'message' => 'Randevu güncellendi.',
            'data' => $this->appointmentPayload($randevu->fresh(['hasta', 'hizmet']), true),
        ]);
    }

    /**
     * Soft-delete appointment (hekim panel destroy).
     */
    public function destroyAppointment(Request $request, int $id): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $randevu = $doktor->randevular()->findOrFail($id);
        $randevu->delete();

        return response()->json(['success' => true, 'message' => 'Randevu silindi.']);
    }

    /**
     * Weekly / range calendar payload for the doctor mobile app.
     * Mirrors hekim panel takvimEvents + week summary.
     *
     * Query params:
     *  - start: Y-m-d (defaults to Monday of current week)
     *  - end: Y-m-d (defaults to Sunday of the start week)
     */
    public function calendar(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');

        $start = $request->filled('start')
            ? Carbon::parse($request->string('start')->value())->startOfDay()
            : Carbon::today()->startOfWeek();
        $end = $request->filled('end')
            ? Carbon::parse($request->string('end')->value())->startOfDay()
            : $start->copy()->endOfWeek()->startOfDay();

        if ($end->lt($start)) {
            return response()->json(['success' => false, 'message' => 'Bitiş tarihi başlangıçtan önce olamaz.'], 422);
        }

        // Cap range to 42 days (≈ 6 weeks) to keep payloads mobile-friendly.
        if ($start->diffInDays($end) > 42) {
            $end = $start->copy()->addDays(42);
        }

        $doktor->loadMissing('randevuAyari');
        $periyot = (int) ($doktor->randevuAyari?->randevu_periyodu ?? 30);
        if ($periyot <= 0) {
            $periyot = 30;
        }

        $randevular = $doktor->randevular()
            ->whereBetween('tarih', [$start->toDateString(), $end->toDateString()])
            ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi', 'iptal'])
            ->with(['hasta:id,ad,soyad,telefon', 'hizmet:id,ad,sure'])
            ->orderBy('tarih')
            ->orderBy('saat')
            ->get();

        $dayCounts = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $dayCounts[$cursor->toDateString()] = 0;
            $cursor->addDay();
        }

        foreach ($randevular as $randevu) {
            $tarihKey = $randevu->tarih instanceof Carbon
                ? $randevu->tarih->toDateString()
                : substr((string) $randevu->tarih, 0, 10);
            if (isset($dayCounts[$tarihKey]) && in_array($randevu->durum, ['beklemede', 'onaylandi', 'tamamlandi'], true)) {
                $dayCounts[$tarihKey]++;
            }
        }

        $izinler = $doktor->izinler()
            ->where(function ($q) use ($start, $end) {
                $q->where('baslangic_zaman', '<=', $end->toDateTimeString().' 23:59:59')
                    ->where('bitis_zaman', '>=', $start->toDateTimeString().' 00:00:00');
            })
            ->get()
            ->map(fn ($izin) => [
                'id' => $izin->id,
                'baslangic' => $izin->baslangic_zaman?->toIso8601String(),
                'bitis' => $izin->bitis_zaman?->toIso8601String(),
                'aciklama' => $izin->aciklama,
            ])
            ->values();

        $calismaSaatleri = $this->ensureWorkingHours($doktor)->map(fn ($cs) => [
            'id' => $cs->id,
            'gun' => $cs->gun,
            'aktif_mi' => (bool) $cs->aktif_mi,
            'mesai_baslangic' => substr((string) $cs->mesai_baslangic, 0, 5),
            'mesai_bitis' => substr((string) $cs->mesai_bitis, 0, 5),
            'ogle_arasi_aktif_mi' => (bool) $cs->ogle_arasi_aktif_mi,
            'ogle_baslangic' => $cs->ogle_baslangic ? substr((string) $cs->ogle_baslangic, 0, 5) : null,
            'ogle_bitis' => $cs->ogle_bitis ? substr((string) $cs->ogle_bitis, 0, 5) : null,
        ])->values();

        return response()->json([
            'success' => true,
            'data' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
                'periyot' => $periyot,
                'day_counts' => $dayCounts,
                'appointments' => $randevular->map(fn ($r) => $this->appointmentPayload($r))->values(),
                'leaves' => $izinler,
                'working_hours' => $calismaSaatleri,
            ],
        ]);
    }

    /**
     * Create a new appointment from the doctor mobile calendar (same rules as hekim panel).
     */
    public function storeAppointment(Request $request, AppointmentBookingService $bookingService): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');

        if ($doktor->paket && ! is_null($doktor->paket->max_randevu_sayisi)) {
            $currentAppointmentsCount = $doktor->randevular()->count();
            if ($currentAppointmentsCount >= $doktor->paket->max_randevu_sayisi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mevcut paketinizde maksimum '.$doktor->paket->max_randevu_sayisi.' randevu oluşturabilirsiniz. Lütfen paketinizi yükseltin.',
                ], 422);
            }
        }

        $data = $request->validate([
            'hizmet_id' => ['required', 'integer', 'exists:hizmetler,id'],
            'danisan_id' => ['required', 'integer', 'exists:hastalar,id'],
            'tarih' => ['required', 'date'],
            'saat' => ['required', 'date_format:H:i'],
            'aciklama' => ['nullable', 'string', 'max:1000'],
            'gorusme_tipi' => ['nullable', 'in:yuz_yuze,online'],
        ]);

        $newDateTime = Carbon::parse($data['tarih'].' '.$data['saat']);
        if ($newDateTime->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçmiş bir tarihe veya saate randevu eklenemez.',
            ], 422);
        }

        $hasta = Hasta::findOrFail($data['danisan_id']);

        try {
            $randevu = $bookingService->create([
                'doktor' => $doktor,
                'hasta' => $hasta,
                'hizmet_id' => (int) $data['hizmet_id'],
                'tarih' => Carbon::parse($data['tarih'])->toDateString(),
                'saat' => $data['saat'],
                'not' => $data['aciklama'] ?? null,
                'ad' => $hasta->ad,
                'soyad' => $hasta->soyad,
                'telefon' => $hasta->telefon,
                'e_posta' => $hasta->e_posta,
                'durum' => 'onaylandi',
                'gorusme_tipi' => ($data['gorusme_tipi'] ?? null) === 'online' ? 'online' : 'yuz_yuze',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Randevu başarıyla oluşturuldu.',
            'data' => $this->appointmentPayload($randevu->load(['hasta', 'hizmet'])),
        ], 201);
    }

    /**
     * Reschedule appointment date/time (same rules as hekim panel drag-and-drop).
     */
    public function rescheduleAppointment(Request $request, int $id, AppointmentBookingService $bookingService): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $randevu = $doktor->randevular()->findOrFail($id);

        $data = $request->validate([
            'tarih' => ['required', 'date'],
            'saat' => ['required', 'date_format:H:i'],
        ]);

        $newDateTime = Carbon::parse($data['tarih'].' '.$data['saat']);
        if ($newDateTime->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçmiş bir tarihe/saate randevu taşınamaz.',
            ], 422);
        }

        try {
            $bookingService->reschedule($randevu, Carbon::parse($data['tarih'])->toDateString(), $data['saat']);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Randevu tarihi ve saati başarıyla güncellendi.',
            'data' => $this->appointmentPayload($randevu->fresh(['hasta', 'hizmet'])),
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $doktor->loadMissing(['il:id,ad', 'ilce:id,ad', 'klinik:id,ad']);

        return response()->json([
            'success' => true,
            'data' => [
                ...$this->doktorPayload($doktor),
                'telefon' => $doktor->telefon,
                'adres' => $doktor->adres,
                'unvan' => $doktor->unvan,
                'il_id' => $doktor->il_id,
                'ilce_id' => $doktor->ilce_id,
                'il' => $doktor->il?->ad,
                'ilce' => $doktor->ilce?->ad,
                'enlem' => $doktor->enlem,
                'boylam' => $doktor->boylam,
                'klinik' => $doktor->klinik?->ad,
                'klinik_rolu' => $doktor->klinik_rolu,
                'sosyal' => [
                    'instagram' => $doktor->instagram,
                    'facebook' => $doktor->facebook,
                    'twitter' => $doktor->twitter,
                    'linkedin' => $doktor->linkedin,
                    'youtube' => $doktor->youtube,
                    'web_sitesi' => $doktor->web_sitesi,
                ],
            ],
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $request->validate([
            'ad_soyad' => ['sometimes', 'required', 'string', 'max:255'],
            'unvan' => ['sometimes', 'nullable', 'string', 'max:100'],
            'telefon' => ['sometimes', 'nullable', 'string', 'max:50'],
            'adres' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'uzmanlik_alani' => ['sometimes', 'nullable', 'string', 'max:255'],
            'biyografi' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'il_id' => ['sometimes', 'nullable', 'integer', 'exists:iller,id'],
            'ilce_id' => ['sometimes', 'nullable', 'integer', 'exists:ilceler,id'],
            'enlem' => ['sometimes', 'nullable', 'numeric'],
            'boylam' => ['sometimes', 'nullable', 'numeric'],
            'instagram' => ['sometimes', 'nullable', 'string', 'max:255'],
            'facebook' => ['sometimes', 'nullable', 'string', 'max:255'],
            'twitter' => ['sometimes', 'nullable', 'string', 'max:255'],
            'linkedin' => ['sometimes', 'nullable', 'string', 'max:255'],
            'youtube' => ['sometimes', 'nullable', 'string', 'max:255'],
            'web_sitesi' => ['sometimes', 'nullable', 'string', 'max:255'],
            'profil_resmi' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
        ]);

        $update = $request->only([
            'ad_soyad', 'unvan', 'telefon', 'adres', 'uzmanlik_alani', 'biyografi',
            'il_id', 'ilce_id', 'enlem', 'boylam',
            'instagram', 'facebook', 'twitter', 'linkedin', 'youtube', 'web_sitesi',
        ]);

        if ($request->hasFile('profil_resmi')) {
            if ($doktor->profil_resmi) {
                Storage::disk('public')->delete($doktor->profil_resmi);
            }
            $update['profil_resmi'] = $request->file('profil_resmi')->store('uploads/profil', 'public');
        }

        if ($update !== []) {
            $doktor->update($update);
        }

        return $this->profile($request);
    }

    public function meta(Request $request): JsonResponse
    {
        $iller = \App\Models\Il::query()->orderBy('ad')->get(['id', 'ad']);
        $ilceler = [];
        if ($request->filled('il_id')) {
            $ilceler = \App\Models\Ilce::query()
                ->where('il_id', (int) $request->input('il_id'))
                ->orderBy('ad')
                ->get(['id', 'ad', 'il_id']);
        }

        $unvanlar = [];
        if (class_exists(\App\Models\Unvan::class)) {
            $unvanlar = \App\Models\Unvan::query()->orderBy('ad')->get(['id', 'ad']);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'iller' => $iller,
                'ilceler' => $ilceler,
                'unvanlar' => $unvanlar,
            ],
        ]);
    }

    public function ical(Request $request)
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');

        $from = now()->subMonths(1)->startOfDay();
        $to = now()->addMonths(6)->endOfDay();

        $randevular = $doktor->randevular()
            ->with(['hasta', 'hizmet'])
            ->whereBetween('tarih', [$from->toDateString(), $to->toDateString()])
            ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi'])
            ->orderBy('tarih')
            ->orderBy('saat')
            ->get();

        $periyot = (int) ($doktor->randevuAyari?->randevu_periyodu ?? 30);
        if ($periyot < 5) {
            $periyot = 30;
        }

        $escape = static function (string $text): string {
            $text = str_replace(["\r\n", "\n", "\r"], '\\n', $text);

            return addcslashes($text, ',;\\');
        };

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Randevu Ajandam//Hekim Mobile//TR',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:'.$escape(($doktor->ad_soyad ?? 'Hekim').' Randevuları'),
        ];

        foreach ($randevular as $r) {
            $tarih = $r->tarih instanceof \DateTimeInterface
                ? $r->tarih->format('Y-m-d')
                : Carbon::parse($r->tarih)->toDateString();
            $saat = substr((string) $r->saat, 0, 8);
            if (strlen($saat) === 5) {
                $saat .= ':00';
            }
            $start = Carbon::parse($tarih.' '.$saat);
            $end = $start->copy()->addMinutes($periyot);
            $hastaAd = trim(($r->hasta->ad ?? $r->ad ?? '').' '.($r->hasta->soyad ?? $r->soyad ?? '')) ?: 'Hasta';
            $hizmet = $r->hizmet?->ad ?? 'Randevu';
            $summary = $hizmet.' — '.$hastaAd;
            $desc = 'Durum: '.$r->durum;
            if ($r->not) {
                $desc .= '\\nNot: '.$r->not;
            }

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:randevu-'.$r->id.'@randevuajandam';
            $lines[] = 'DTSTAMP:'.gmdate('Ymd\THis\Z');
            $lines[] = 'DTSTART:'.$start->format('Ymd\THis');
            $lines[] = 'DTEND:'.$end->format('Ymd\THis');
            $lines[] = 'SUMMARY:'.$escape($summary);
            $lines[] = 'DESCRIPTION:'.$escape($desc);
            $lines[] = 'STATUS:'.($r->durum === 'iptal' ? 'CANCELLED' : 'CONFIRMED');
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';
        $ics = implode("\r\n", $lines)."\r\n";
        $filename = 'randevular-'.Str::slug($doktor->ad_soyad ?? 'hekim').'.ics';

        if ($request->boolean('json')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'filename' => $filename,
                    'content' => $ics,
                    'count' => $randevular->count(),
                ],
            ]);
        }

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Membership / demo window for the doctor (or their clinic when applicable).
     *
     * @return array<string, mixed>
     */
    private function membershipPayload(Doktor $doktor): array
    {
        $baslangic = $doktor->uyelik_baslangic;
        $bitis = $doktor->uyelik_bitis;
        $kaynak = 'hekim';

        if (method_exists($doktor, 'klinikteMi') && $doktor->klinikteMi() && $doktor->klinik) {
            $baslangic = $doktor->klinik->uyelik_baslangic ?? $baslangic;
            $bitis = $doktor->klinik->uyelik_bitis ?? $bitis;
            $kaynak = 'klinik';
        }

        $baslangicStr = $baslangic
            ? ($baslangic instanceof \DateTimeInterface
                ? $baslangic->format('Y-m-d')
                : Carbon::parse((string) $baslangic)->toDateString())
            : null;
        $bitisStr = $bitis
            ? ($bitis instanceof \DateTimeInterface
                ? $bitis->format('Y-m-d')
                : Carbon::parse((string) $bitis)->toDateString())
            : null;

        $kalanGun = null;
        $aktifMi = true;
        if ($bitisStr) {
            $bitisCarbon = Carbon::parse($bitisStr)->endOfDay();
            $kalanGun = (int) now()->startOfDay()->diffInDays($bitisCarbon, false);
            $aktifMi = $bitisCarbon->isFuture() || $bitisCarbon->isToday();
        }

        $paket = method_exists($doktor, 'aktifPaket') ? $doktor->aktifPaket() : $doktor->paket;
        $features = [];
        if ($paket && method_exists($paket, 'sistemOzellikleri')) {
            $features = $paket->sistemOzellikleri()->pluck('kod')->filter()->values()->all();
        }
        $demoMu = $paket === null || $features === [];

        return [
            'uyelik_baslangic' => $baslangicStr,
            'uyelik_bitis' => $bitisStr,
            'kalan_gun' => $kalanGun,
            'uyelik_aktif_mi' => $aktifMi,
            'demo_mu' => $demoMu,
            'kaynak' => $kaynak,
            'paket' => $paket ? [
                'id' => $paket->id,
                'ad' => $paket->ad ?? $paket->name ?? null,
                'tur' => $paket->tur ?? null,
            ] : null,
            'features' => $features,
            'ozellik_sayisi' => count($features),
        ];
    }

    public function packageFeatures(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $membership = $this->membershipPayload($doktor);
        $features = $membership['features'] ?? [];

        return response()->json([
            'success' => true,
            'data' => [
                'paket' => $membership['paket'],
                'features' => $features,
                // Empty features => treat as unrestricted (demo / no package row).
                'restrict' => $features !== [],
                'uyelik' => $membership,
            ],
        ]);
    }

    public function packages(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $current = method_exists($doktor, 'aktifPaket') ? $doktor->aktifPaket() : $doktor->paket;
        $membership = $this->membershipPayload($doktor);

        $items = \App\Models\Paket::query()
            ->where('aktif_mi', true)
            ->where(function ($q) use ($doktor) {
                if (method_exists($doktor, 'klinik_id') && $doktor->klinik_id) {
                    $q->where('tur', 'klinik');
                } else {
                    $q->where('tur', 'bireysel');
                }
            })
            ->orderBy('sira')
            ->orderBy('id')
            ->get()
            ->map(function ($p) use ($current) {
                $features = method_exists($p, 'sistemOzellikleri')
                    ? $p->sistemOzellikleri()->pluck('kod')->filter()->values()->all()
                    : [];
                $ozellikler = is_array($p->ozellikler) ? array_values(array_filter($p->ozellikler)) : [];
                $isFree = (float) ($p->aylik_indirimli_fiyat ?? $p->aylik_fiyat ?? 0) <= 0
                    && (float) ($p->yillik_indirimli_fiyat ?? $p->yillik_fiyat ?? 0) <= 0;
                $isWeb = in_array('web_sitesi', $features, true)
                    || in_array('klinik_web_sitesi', $features, true)
                    || (bool) ($p->domain_dahil_mi ?? false)
                    || str_contains(mb_strtolower((string) $p->ad), 'web sitesi')
                    || str_contains(mb_strtolower((string) $p->ad), 'kurumsal');

                return [
                    'id' => $p->id,
                    'ad' => $p->ad,
                    'tur' => $p->tur,
                    'aciklama' => $p->aciklama,
                    'aylik_fiyat' => $p->aylik_fiyat,
                    'aylik_indirimli_fiyat' => $p->aylik_indirimli_fiyat,
                    'yillik_fiyat' => $p->yillik_fiyat,
                    'yillik_indirimli_fiyat' => $p->yillik_indirimli_fiyat,
                    'features' => $features,
                    /** Pazarlama madde listesi (web paket_sec ile aynı) */
                    'ozellikler' => $ozellikler,
                    'domain_dahil_mi' => (bool) ($p->domain_dahil_mi ?? false),
                    'deneme_gun' => (int) ($p->deneme_gun ?? 0),
                    'web_sitesi_mi' => $isWeb,
                    'aktif_paket_mi' => $current && (int) $current->id === (int) $p->id,
                    'ucretsiz_mi' => $isFree,
                ];
            })
            ->values();

        // Site paket_sec: ücretli + web olmayan 2. bireysel paket = Popüler; klinik 2. = Önerilen
        $isKlinikList = $items->isNotEmpty() && ($items->first()['tur'] ?? '') === 'klinik';
        $paidNonWebIndex = 0;
        $clinicIndex = 0;
        $items = $items->map(function (array $row) use (&$paidNonWebIndex, &$clinicIndex, $isKlinikList) {
            $isFeatured = false;
            if ($isKlinikList) {
                $clinicIndex++;
                $isFeatured = $clinicIndex === 2;
            } elseif (! ($row['ucretsiz_mi'] ?? false) && ! ($row['web_sitesi_mi'] ?? false)) {
                $paidNonWebIndex++;
                $isFeatured = $paidNonWebIndex === 2;
            }

            $row['populer_mi'] = $isFeatured;
            if ($row['aktif_paket_mi'] ?? false) {
                $row['etiket'] = 'Aktif';
            } elseif ($isFeatured) {
                $row['etiket'] = $isKlinikList ? 'Önerilen' : 'Popüler';
            } elseif ($row['web_sitesi_mi'] ?? false) {
                $row['etiket'] = $isKlinikList ? 'Web sitesi dahil' : 'Web sitesi';
            } elseif ($row['ucretsiz_mi'] ?? false) {
                $row['etiket'] = 'Ücretsiz';
            } elseif (($row['deneme_gun'] ?? 0) > 0) {
                $row['etiket'] = ((int) $row['deneme_gun']).' gün deneme';
            } else {
                $row['etiket'] = null;
            }

            return $row;
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'mevcut' => $current ? [
                    'id' => $current->id,
                    'ad' => $current->ad ?? $current->name ?? null,
                    'tur' => $current->tur ?? null,
                ] : null,
                'uyelik' => $membership,
                'items' => $items,
            ],
        ]);
    }

    /**
     * Save package preference from onboarding (does not activate paid/clinic packages).
     */
    public function preferPackage(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $data = $request->validate([
            'paket_id' => ['required', 'integer', 'exists:paketler,id'],
            'odeme_periyodu' => ['nullable', 'in:aylik,yillik'],
            'package_key' => ['nullable', 'string', 'max:80'],
            'tur' => ['nullable', 'string', 'in:bireysel,klinik'],
        ]);

        $paket = \App\Models\Paket::where('aktif_mi', true)->findOrFail($data['paket_id']);
        $payload = [
            'paket_id' => (int) $paket->id,
            'paket_ad' => $paket->ad,
            'tur' => $paket->tur ?? ($data['tur'] ?? 'bireysel'),
            'odeme_periyodu' => $data['odeme_periyodu'] ?? 'aylik',
            'package_key' => $data['package_key'] ?? null,
            'saved_at' => now()->toIso8601String(),
        ];

        \Illuminate\Support\Facades\Cache::put(
            'mobil_paket_tercihi_'.$doktor->id,
            $payload,
            now()->addDays(90)
        );

        return response()->json([
            'success' => true,
            'message' => 'Paket tercihi kaydedildi.',
            'data' => $payload,
        ]);
    }

    /**
     * Confirm App Store / Play / RevenueCat purchase and activate membership.
     */
    public function confirmIapPurchase(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $data = $request->validate([
            'paket_id' => ['required', 'integer', 'exists:paketler,id'],
            'odeme_periyodu' => ['required', 'in:aylik,yillik'],
            'product_id' => ['required', 'string', 'max:191'],
            'transaction_id' => ['nullable', 'string', 'max:191'],
            'app_user_id' => ['nullable', 'string', 'max:191'],
            'receipt' => ['nullable', 'string'],
            'platform' => ['nullable', 'string', 'in:ios,android'],
        ]);

        $paket = \App\Models\Paket::where('aktif_mi', true)->findOrFail($data['paket_id']);
        if (method_exists($paket, 'klinikPaketiMi') && $paket->klinikPaketiMi()) {
            return response()->json([
                'success' => false,
                'message' => 'Klinik paketleri IAP ile mobilden aktifleştirilemez.',
            ], 422);
        }

        $iap = app(\App\Services\MobileIapService::class);
        $periodPrice = $data['odeme_periyodu'] === 'yillik'
            ? (float) $paket->yillik_fiyat
            : (float) $paket->aylik_fiyat;
        $discounted = $data['odeme_periyodu'] === 'yillik'
            ? $paket->yillik_indirimli_fiyat
            : $paket->aylik_indirimli_fiyat;
        $tutar = $discounted !== null && (float) $discounted > 0 ? (float) $discounted : $periodPrice;

        if ($tutar <= 0) {
            $iap->activate($doktor, $paket, $data['odeme_periyodu'], [
                'source' => 'free',
                'transaction_id' => $data['transaction_id'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ücretsiz paket aktifleştirildi.',
                'data' => $this->membershipPayload($doktor->fresh()),
            ]);
        }

        $verify = $iap->verifyPurchase([
            'paket_id' => (int) $paket->id,
            'period' => $data['odeme_periyodu'],
            'product_id' => $data['product_id'],
            'transaction_id' => $data['transaction_id'] ?? '',
            'app_user_id' => $data['app_user_id'] ?? ('doktor_'.$doktor->id),
        ]);

        if (! ($verify['ok'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $verify['message'] ?? 'Satın alma doğrulanamadı.',
                'data' => [
                    'iap_ready' => (bool) config('services.revenuecat.secret_key'),
                    'fallback' => 'havale',
                ],
            ], 422);
        }

        $iap->activate($doktor, $paket, $data['odeme_periyodu'], [
            'source' => 'store',
            'transaction_id' => $data['transaction_id'] ?? null,
            'platform' => $data['platform'] ?? null,
            'product_id' => $data['product_id'],
        ]);

        if (class_exists(\App\Models\UyelikOdeme::class)) {
            try {
                \App\Models\UyelikOdeme::create([
                    'doktor_id' => $doktor->id,
                    'paket_id' => $paket->id,
                    'odeme_yontemi' => 'iap',
                    'odeme_periyodu' => $data['odeme_periyodu'],
                    'tutar' => $tutar,
                    'durum' => 'onaylandi',
                    'havale_referans' => $data['transaction_id'] ?? $data['product_id'],
                ]);
            } catch (\Throwable) {
                /* optional audit row */
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Mağaza aboneliği doğrulandı, paket aktif.',
            'data' => $this->membershipPayload($doktor->fresh()),
        ]);
    }

    /**
     * Activate package natively: free trial or bank transfer request.
     */
    public function subscribePackage(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $data = $request->validate([
            'paket_id' => ['required', 'integer', 'exists:paketler,id'],
            'odeme_periyodu' => ['required', 'in:aylik,yillik'],
            'odeme_yontemi' => ['nullable', 'in:havale,ucretsiz'],
            'havale_referans' => ['nullable', 'string', 'max:100'],
        ]);

        $paket = \App\Models\Paket::where('aktif_mi', true)->findOrFail($data['paket_id']);
        if (method_exists($paket, 'klinikPaketiMi') && $paket->klinikPaketiMi()) {
            return response()->json([
                'success' => false,
                'message' => 'Klinik paketleri mobil uygulamadan başlatılamaz. Bireysel paket seçin veya klinik kaydını web panelinden tamamlayın.',
            ], 422);
        }

        $periodPrice = $data['odeme_periyodu'] === 'yillik'
            ? (float) $paket->yillik_fiyat
            : (float) $paket->aylik_fiyat;
        $discounted = $data['odeme_periyodu'] === 'yillik'
            ? $paket->yillik_indirimli_fiyat
            : $paket->aylik_indirimli_fiyat;
        $tutar = $discounted !== null && (float) $discounted > 0 ? (float) $discounted : $periodPrice;
        $isFree = $tutar <= 0;

        if ($isFree) {
            $baslangic = now();
            $bitis = $data['odeme_periyodu'] === 'yillik'
                ? $baslangic->copy()->addYear()
                : $baslangic->copy()->addMonth();
            $doktor->update([
                'paket_id' => $paket->id,
                'odeme_periyodu' => $data['odeme_periyodu'],
                'uyelik_baslangic' => $baslangic,
                'uyelik_bitis' => $bitis,
                'iyzico_subscription_status' => 'ACTIVE',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ücretsiz paket aktifleştirildi.',
                'data' => $this->membershipPayload($doktor->fresh()),
            ]);
        }

        // Paid: only bank transfer (havale) from mobile — no embedded web checkout
        if (($data['odeme_yontemi'] ?? 'havale') !== 'havale') {
            return response()->json([
                'success' => false,
                'message' => 'Mobil uygulamada ücretli paket için havale/EFT kullanın. Kart ödemesi web panelinden yapılır.',
            ], 422);
        }
        if (empty($data['havale_referans'])) {
            return response()->json([
                'success' => false,
                'message' => 'Havale referansını girin.',
            ], 422);
        }

        if (class_exists(\App\Models\UyelikOdeme::class)) {
            \App\Models\UyelikOdeme::create([
                'doktor_id' => $doktor->id,
                'paket_id' => $paket->id,
                'odeme_yontemi' => 'havale',
                'odeme_periyodu' => $data['odeme_periyodu'],
                'tutar' => $tutar,
                'durum' => 'beklemede',
                'havale_referans' => $data['havale_referans'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Havale talebiniz alındı. Onay sonrası paketiniz aktifleşir.',
            'data' => [
                'tutar' => $tutar,
                'odeme_periyodu' => $data['odeme_periyodu'],
                'durum' => 'beklemede',
            ],
        ]);
    }

    public function registerDevice(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $data = $request->validate([
            'push_token' => ['required', 'string', 'max:512'],
            'platform' => ['nullable', 'string', 'in:android,ios,web'],
            'provider' => ['nullable', 'string', 'in:expo,fcm'],
            'device_name' => ['nullable', 'string', 'max:120'],
            'app_version' => ['nullable', 'string', 'max:40'],
        ]);

        DoktorDeviceToken::upsertToken(
            $doktor->id,
            $data['push_token'],
            $data['platform'] ?? null,
            $data['device_name'] ?? null,
            $data['app_version'] ?? null,
            $data['provider'] ?? 'expo',
        );

        return response()->json(['success' => true, 'message' => 'Cihaz kaydedildi.']);
    }

    public function notifications(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $items = $doktor->notifications()
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'type' => $n->data['type'] ?? class_basename($n->type),
                'title' => $n->data['title'] ?? $n->data['baslik'] ?? 'Bildirim',
                'body' => $n->data['body'] ?? $n->data['mesaj'] ?? '',
                'data' => $n->data,
                'read_at' => $n->read_at?->toIso8601String(),
                'created_at' => $n->created_at?->toIso8601String(),
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'unread' => $doktor->unreadNotifications()->count(),
            ],
        ]);
    }

    public function markNotificationsRead(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $ids = $request->input('ids');
        if (is_array($ids) && $ids !== []) {
            $doktor->notifications()->whereIn('id', $ids)->get()->each->markAsRead();
        } else {
            $doktor->unreadNotifications->markAsRead();
        }

        return response()->json(['success' => true, 'message' => 'Bildirimler okundu işaretlendi.']);
    }

    public function destroyNotification(Request $request, string $id): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $notification = $doktor->notifications()->where('id', $id)->first();
        if (! $notification) {
            return response()->json(['success' => false, 'message' => 'Bildirim bulunamadı.'], 404);
        }
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bildirim silindi.',
            'data' => [
                'unread' => $doktor->unreadNotifications()->count(),
            ],
        ]);
    }

    public function destroyAllNotifications(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $deleted = $doktor->notifications()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tüm bildirimler silindi.',
            'data' => [
                'deleted' => (int) $deleted,
                'unread' => 0,
            ],
        ]);
    }

    public function patients(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $search = trim($request->string('q')->value() ?? '');
        $patientIds = $doktor->randevular()->whereNotNull('hasta_id')->distinct()->pluck('hasta_id');

        if ($doktor->klinik_id) {
            $klinikHastaIds = $doktor->klinik?->hastalar()->pluck('hastalar.id') ?? collect();
            $patientIds = $patientIds->merge($klinikHastaIds)->unique()->values();
        }

        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(50, max(10, (int) $request->input('per_page', 20)));

        $query = Hasta::query()
            ->when($patientIds->isNotEmpty(), fn ($q) => $q->whereIn('id', $patientIds), fn ($q) => $q->whereRaw('1 = 0'))
            ->when($search !== '', fn ($q) => $q->where(fn ($inner) => $inner
                ->where('ad', 'like', "%{$search}%")
                ->orWhere('soyad', 'like', "%{$search}%")
                ->orWhere('telefon', 'like', "%{$search}%")
                ->orWhere('e_posta', 'like', "%{$search}%")))
            ->withCount(['randevular as randevu_sayisi' => fn ($q) => $q->where('doktor_id', $doktor->id)])
            ->orderBy('ad')
            ->orderBy('soyad');

        $total = (clone $query)->count();
        $patients = $query
            ->forPage($page, $perPage)
            ->get(['id', 'ad', 'soyad', 'telefon', 'e_posta']);

        return response()->json([
            'success' => true,
            'data' => $patients,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => max(1, (int) ceil($total / $perPage)),
            ],
        ]);
    }

    /**
     * Patient detail + recent appointments with this doctor.
     */
    public function showPatient(Request $request, int $id): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');

        $allowedIds = $doktor->randevular()->whereNotNull('hasta_id')->distinct()->pluck('hasta_id');
        if ($doktor->klinik_id) {
            $allowedIds = $allowedIds->merge($doktor->klinik?->hastalar()->pluck('hastalar.id') ?? collect())->unique();
        }

        abort_unless($allowedIds->contains($id), 404);

        $hasta = Hasta::query()->findOrFail($id);
        $randevular = $doktor->randevular()
            ->where('hasta_id', $id)
            ->with('hizmet:id,ad,sure')
            ->orderByDesc('tarih')
            ->orderByDesc('saat')
            ->limit(50)
            ->get()
            ->map(fn ($r) => $this->appointmentPayload($r));

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $hasta->id,
                'ad' => $hasta->ad,
                'soyad' => $hasta->soyad,
                'telefon' => $hasta->telefon,
                'e_posta' => $hasta->e_posta,
                'randevular' => $randevular,
            ],
        ]);
    }

    public function updatePatient(Request $request, int $id): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');

        $allowedIds = $doktor->randevular()->whereNotNull('hasta_id')->distinct()->pluck('hasta_id');
        if ($doktor->klinik_id) {
            $allowedIds = $allowedIds->merge($doktor->klinik?->hastalar()->pluck('hastalar.id') ?? collect())->unique();
        }
        abort_unless($allowedIds->contains($id), 404);

        $data = $request->validate([
            'ad' => ['sometimes', 'required', 'string', 'max:120'],
            'soyad' => ['sometimes', 'required', 'string', 'max:120'],
            'ad_soyad' => ['sometimes', 'nullable', 'string', 'max:255'],
            'telefon' => ['sometimes', 'nullable', 'string', 'max:50'],
            'e_posta' => ['sometimes', 'nullable', 'email', 'max:255'],
        ]);

        $hasta = Hasta::query()->findOrFail($id);

        if (! empty($data['ad_soyad'])) {
            $parts = preg_split('/\s+/', trim((string) $data['ad_soyad']), 2) ?: [];
            $data['ad'] = $parts[0] ?? $hasta->ad;
            $data['soyad'] = $parts[1] ?? ($hasta->soyad ?? '');
            unset($data['ad_soyad']);
        }

        $update = [];
        foreach (['ad', 'soyad', 'telefon', 'e_posta'] as $key) {
            if (array_key_exists($key, $data)) {
                $update[$key] = $data[$key];
            }
        }
        if ($update !== []) {
            $hasta->update($update);
        }

        return response()->json([
            'success' => true,
            'message' => 'Danışan güncellendi.',
            'data' => [
                'id' => $hasta->id,
                'ad' => $hasta->ad,
                'soyad' => $hasta->soyad,
                'telefon' => $hasta->telefon,
                'e_posta' => $hasta->e_posta,
            ],
        ]);
    }

    public function destroyPatient(Request $request, int $id): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');

        $allowedIds = $doktor->randevular()->whereNotNull('hasta_id')->distinct()->pluck('hasta_id');
        if ($doktor->klinik_id) {
            $allowedIds = $allowedIds->merge($doktor->klinik?->hastalar()->pluck('hastalar.id') ?? collect())->unique();
        }
        abort_unless($allowedIds->contains($id), 404);

        $active = $doktor->randevular()
            ->where('hasta_id', $id)
            ->whereIn('durum', ['beklemede', 'onaylandi'])
            ->exists();
        if ($active) {
            return response()->json([
                'success' => false,
                'message' => 'Aktif/bekleyen randevusu olan danışan silinemez.',
            ], 422);
        }

        // Soft-unlink: only detach from clinic pool; keep Hasta row for other doctors.
        if ($doktor->klinik_id && $doktor->klinik) {
            $doktor->klinik->hastalar()->detach($id);
        }

        // Do not hard-delete global patient accounts; mark as inactive if only linked to this doctor.
        $otherLinks = \App\Models\Randevu::query()
            ->where('hasta_id', $id)
            ->where('doktor_id', '!=', $doktor->id)
            ->exists();
        if (! $otherLinks) {
            $hasta = Hasta::query()->find($id);
            if ($hasta && ! $doktor->randevular()->where('hasta_id', $id)->whereIn('durum', ['beklemede', 'onaylandi'])->exists()) {
                // Keep history; soft flag if column exists
                if (\Illuminate\Support\Facades\Schema::hasColumn('hastalar', 'aktif_mi')) {
                    $hasta->update(['aktif_mi' => false]);
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Danışan listeden kaldırıldı / pasifleştirildi.']);
    }

    /**
     * Available day slots for creating appointments (empty working slots).
     */
    public function daySlots(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $data = $request->validate(['tarih' => ['required', 'date']]);
        $tarih = Carbon::parse($data['tarih']);
        $gunIndeksi = (int) $tarih->format('N');

        $calismaSaati = $doktor->calismaSaatleri()->where('gun', $gunIndeksi)->first();
        $periyot = (int) ($doktor->randevuAyari?->randevu_periyodu ?? 30);
        if ($periyot <= 0) {
            $periyot = 30;
        }

        if (! $calismaSaati || ! $calismaSaati->aktif_mi) {
            return response()->json([
                'success' => true,
                'data' => [
                    'tarih' => $tarih->toDateString(),
                    'aktif_mi' => false,
                    'periyot' => $periyot,
                    'slots' => [],
                ],
            ]);
        }

        $randevular = $doktor->randevular()
            ->whereDate('tarih', $tarih->toDateString())
            ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi'])
            ->get();
        $izinler = $doktor->izinler()
            ->where('baslangic_zaman', '<=', $tarih->toDateString().' 23:59:59')
            ->where('bitis_zaman', '>=', $tarih->toDateString().' 00:00:00')
            ->get();

        $slotService = app(\App\Services\SlotService::class);
        $slots = $slotService->generateGunlukSlotlar($doktor, $tarih, $randevular, $izinler, $periyot);
        $available = collect($slots)
            ->filter(fn ($s) => ($s['durum'] ?? '') === 'bos')
            ->map(fn ($s) => [
                'saat' => $s['saat_string'] ?? $s['saat_baslangic'],
                'bitis' => $s['saat_bitis'] ?? null,
            ])
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'tarih' => $tarih->toDateString(),
                'aktif_mi' => true,
                'periyot' => $periyot,
                'slots' => $available,
            ],
        ]);
    }

    /**
     * Create a new patient from the doctor mobile app (same rules as hekim panel).
     */
    public function storePatient(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');

        if ($doktor->paket && ! is_null($doktor->paket->max_hasta_sayisi)) {
            $currentPatientsCount = $doktor->randevular()->distinct('hasta_id')->count('hasta_id');
            if ($currentPatientsCount >= $doktor->paket->max_hasta_sayisi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mevcut paketinizde maksimum '.$doktor->paket->max_hasta_sayisi.' danışan ekleyebilirsiniz. Lütfen paketinizi yükseltin.',
                ], 422);
            }
        }

        $data = $request->validate([
            'ad_soyad' => ['required', 'string', 'max:255'],
            'telefon' => ['required', 'string', 'max:50'],
            'e_posta' => ['nullable', 'email', 'max:255', 'unique:hastalar,e_posta'],
        ], [
            'ad_soyad.required' => 'Danışan adı zorunludur.',
            'telefon.required' => 'Telefon numarası zorunludur.',
            'e_posta.unique' => 'Bu e-posta ile kayıtlı bir danışan zaten var.',
        ]);

        $parts = preg_split('/\s+/', trim($data['ad_soyad']), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $soyad = count($parts) > 1 ? array_pop($parts) : '';
        $ad = implode(' ', $parts);
        if ($ad === '') {
            $ad = $data['ad_soyad'];
        }

        $email = trim((string) ($data['e_posta'] ?? ''));
        if ($email === '') {
            $digits = preg_replace('/\D+/', '', $data['telefon']) ?: Str::lower(Str::random(8));
            $email = 'misafir+'.$digits.'@randevu.local';
            if (Hasta::where('e_posta', $email)->exists()) {
                $email = 'misafir+'.$digits.'.'.Str::lower(Str::random(4)).'@randevu.local';
            }
        }

        $tempPassword = Str::password(10);
        $hasta = Hasta::create([
            'ad' => $ad,
            'soyad' => $soyad,
            'e_posta' => $email,
            'telefon' => $data['telefon'],
            'sifre' => $tempPassword,
            'aktif_mi' => true,
        ]);

        if ($doktor->klinik_id && $doktor->klinik) {
            $doktor->klinik->hastalar()->syncWithoutDetaching([
                $hasta->id => ['kayit_tarihi' => now()],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Yeni danışan başarıyla oluşturuldu.',
            'data' => [
                'id' => $hasta->id,
                'ad' => $hasta->ad,
                'soyad' => $hasta->soyad,
                'telefon' => $hasta->telefon,
                'e_posta' => $hasta->e_posta,
            ],
        ], 201);
    }

    public function services(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');

        return response()->json([
            'success' => true,
            'data' => $doktor->hizmetler()->latest('id')->get([
                'id', 'ad', 'aciklama', 'sure', 'fiyat', 'resim', 'aktif_mi',
                'meta_baslik', 'meta_aciklama', 'meta_anahtar_kelimeler',
            ]),
        ]);
    }

    public function storeService(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $data = $this->validateService($request);
        unset($data['resim']);
        $hizmet = $doktor->hizmetler()->create($data);
        if ($request->hasFile('resim')) {
            $hizmet->update([
                'resim' => $request->file('resim')->store('uploads/hizmet', 'public'),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Hizmet eklendi.', 'data' => $hizmet->fresh()], 201);
    }

    public function updateService(Request $request, int $id): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $hizmet = $doktor->hizmetler()->findOrFail($id);
        $data = $this->validateService($request);
        unset($data['resim']);
        $hizmet->update($data);
        if ($request->hasFile('resim')) {
            if ($hizmet->resim) {
                Storage::disk('public')->delete($hizmet->resim);
            }
            $hizmet->update([
                'resim' => $request->file('resim')->store('uploads/hizmet', 'public'),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Hizmet güncellendi.', 'data' => $hizmet->fresh()]);
    }

    public function destroyService(Request $request, int $id): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $doktor->hizmetler()->findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Hizmet silindi.']);
    }

    public function appointmentSettings(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');

        return response()->json(['success' => true, 'data' => $this->ensureAppointmentSettings($doktor)]);
    }

    public function updateAppointmentSettings(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $data = $request->validate([
            'aktif_mi' => ['required', 'boolean'],
            'randevu_onay_tipi' => ['required', 'in:manuel,otomatik'],
            'en_erken_randevu_saati' => ['required', 'integer', 'min:0'],
            'en_gec_randevu_gunu' => ['required', 'integer', 'min:1'],
            'randevu_periyodu' => ['required', 'integer', 'in:15,20,30,45,60'],
            'randevu_iptal_aktif_mi' => ['required', 'boolean'],
            'iptal_saat_limiti' => ['required', 'integer', 'min:0'],
            'gunluk_maksimum_randevu' => ['required', 'integer', 'min:0'],
            'email_bildirimleri' => ['required', 'boolean'],
            'sms_bildirimleri' => ['required', 'boolean'],
        ]);
        $ayarlar = $this->ensureAppointmentSettings($doktor);
        $ayarlar->update($data);

        return response()->json(['success' => true, 'message' => 'Randevu ayarları güncellendi.', 'data' => $ayarlar->fresh()]);
    }

    public function workingHours(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');

        return response()->json(['success' => true, 'data' => $this->ensureWorkingHours($doktor)]);
    }

    public function updateWorkingHours(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $data = $request->validate([
            'hours' => ['required', 'array', 'size:7'],
            'hours.*.id' => ['required', 'integer'],
            'hours.*.aktif_mi' => ['required', 'boolean'],
            'hours.*.mesai_baslangic' => ['required', 'date_format:H:i'],
            'hours.*.mesai_bitis' => ['required', 'date_format:H:i'],
            'hours.*.ogle_arasi_aktif_mi' => ['required', 'boolean'],
            'hours.*.ogle_baslangic' => ['nullable', 'date_format:H:i'],
            'hours.*.ogle_bitis' => ['nullable', 'date_format:H:i'],
        ]);

        $ownedHours = $doktor->calismaSaatleri()->get()->keyBy('id');
        foreach ($data['hours'] as $hour) {
            $ownedHour = $ownedHours->get($hour['id']);
            abort_unless($ownedHour, 404);
            $ownedHour->update($hour);
        }

        return response()->json(['success' => true, 'message' => 'Çalışma saatleri güncellendi.', 'data' => $doktor->calismaSaatleri()->orderBy('gun')->get()]);
    }

    private function appointmentPayload($randevu, bool $detailed = false): array
    {
        $doktor = $randevu->relationLoaded('doktor') ? $randevu->doktor : null;
        $sure = 30;
        if ($randevu->hizmet && $randevu->hizmet->sure) {
            $sure = (int) $randevu->hizmet->sure;
        } elseif ($doktor?->randevuAyari?->randevu_periyodu) {
            $sure = (int) $doktor->randevuAyari->randevu_periyodu;
        }

        $saatStr = substr((string) $randevu->saat, 0, 5);
        $bitisSaat = null;
        try {
            $bitisSaat = Carbon::createFromFormat('H:i', $saatStr)->addMinutes($sure)->format('H:i');
        } catch (\Throwable) {
            $bitisSaat = $saatStr;
        }

        $payload = [
            'id' => $randevu->id,
            'tarih' => $randevu->tarih instanceof \DateTimeInterface
                ? $randevu->tarih->format('Y-m-d')
                : (string) $randevu->tarih,
            'saat' => $saatStr,
            'bitis_saat' => $bitisSaat,
            'sure' => $sure,
            'durum' => $randevu->durum,
            'gorusme_tipi' => $randevu->gorusme_tipi,
            'hasta_id' => $randevu->hasta_id,
            'hasta_adi' => trim(($randevu->hasta->ad ?? $randevu->ad).' '.($randevu->hasta->soyad ?? $randevu->soyad)),
            'telefon' => $randevu->hasta->telefon ?? $randevu->telefon,
            'e_posta' => $randevu->hasta->e_posta ?? $randevu->e_posta,
            'hizmet_id' => $randevu->hizmet_id,
            'hizmet' => $randevu->hizmet?->ad,
            'not' => $randevu->not,
            'hekim_notu' => $randevu->hekim_notu,
        ];

        $isOnline = method_exists($randevu, 'isOnline') ? $randevu->isOnline() : ($randevu->gorusme_tipi === 'online');
        $payload['online_mi'] = $isOnline;
        $payload['join_url'] = null;
        $payload['can_join'] = false;

        if ($detailed && $isOnline && $randevu->durum === 'onaylandi') {
            try {
                $meet = app(\App\Services\MeetingRoomService::class);
                if (! $randevu->meeting_join_token) {
                    $randevu = $meet->ensureRoom($randevu);
                }
                $payload['join_url'] = url('/hekim/gorusme/'.$randevu->id);
                $payload['join_app_url'] = url('/hekim/gorusme/'.$randevu->id.'/app');
                $payload['can_join'] = $meet->canJoin($randevu);
                $payload['platform_join_url'] = $meet->platformJoinUrl($randevu);
            } catch (\Throwable) {
                $payload['join_url'] = url('/hekim/gorusme/'.$randevu->id);
                $payload['join_app_url'] = url('/hekim/gorusme/'.$randevu->id.'/app');
            }
        }

        return $payload;
    }

    private function validateService(Request $request): array
    {
        $data = $request->validate([
            'ad' => ['required', 'string', 'max:255'],
            'aciklama' => ['nullable', 'string'],
            'sure' => ['required', 'integer', 'min:1', 'max:1440'],
            'fiyat' => ['nullable', 'numeric', 'min:0'],
            'aktif_mi' => ['required', 'boolean'],
            'meta_baslik' => ['nullable', 'string', 'max:255'],
            'meta_aciklama' => ['nullable', 'string', 'max:255'],
            'meta_anahtar_kelimeler' => ['nullable', 'string', 'max:255'],
            'resim' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
        ]);
        // FormData may send aktif_mi as "1"/"0"/"true"
        if ($request->has('aktif_mi')) {
            $data['aktif_mi'] = $request->boolean('aktif_mi');
        }

        return $data;
    }

    private function ensureAppointmentSettings(Doktor $doktor): \App\Models\RandevuAyari
    {
        return $doktor->randevuAyari()->firstOrCreate([], [
            'randevu_onay_tipi' => 'manuel',
            'en_erken_randevu_saati' => 2,
            'en_gec_randevu_gunu' => 30,
            'randevu_periyodu' => 30,
            'randevu_iptal_aktif_mi' => true,
            'iptal_saat_limiti' => 24,
            'gunluk_maksimum_randevu' => 0,
            'email_bildirimleri' => true,
            'sms_bildirimleri' => true,
            'aktif_mi' => true,
        ]);
    }

    private function ensureWorkingHours(Doktor $doktor): \Illuminate\Database\Eloquent\Collection
    {
        if (! $doktor->calismaSaatleri()->exists()) {
            foreach (range(1, 7) as $gun) {
                $doktor->calismaSaatleri()->create([
                    'gun' => $gun,
                    'aktif_mi' => $gun <= 5,
                    'mesai_baslangic' => '09:00',
                    'mesai_bitis' => '17:00',
                    'ogle_arasi_aktif_mi' => true,
                    'ogle_baslangic' => '12:00',
                    'ogle_bitis' => '13:00',
                ]);
            }
        }

        return $doktor->calismaSaatleri()->orderBy('gun')->get();
    }

    private function authenticatedResponse(Doktor $doktor, ?string $device, ?string $ip): JsonResponse
    {
        $token = DoktorApiToken::issue($doktor, $device ?: 'doctor-mobile', $ip);

        return response()->json([
            'success' => true,
            'data' => [
                'requires_two_factor' => false,
                'token' => $token['plain'],
                'expires_at' => $token['model']->expires_at?->toIso8601String(),
                'doktor' => $this->doktorPayload($doktor),
            ],
        ]);
    }

    private function challengeCacheKey(string $challenge): string
    {
        return 'mobile-doktor-2fa:'.hash('sha256', $challenge);
    }

    private function doktorPayload(Doktor $doktor): array
    {
        $doktor->loadMissing('branslar');

        return [
            'id' => $doktor->id,
            'ad_soyad' => $doktor->ad_soyad,
            'unvan' => $doktor->unvan,
            'e_posta' => $doktor->e_posta,
            'profil_resmi' => $doktor->profil_resmi,
            'uzmanlik_alani' => $doktor->uzmanlik_alani,
            'branslar' => $doktor->branslar->pluck('ad')->values(),
        ];
    }
}
