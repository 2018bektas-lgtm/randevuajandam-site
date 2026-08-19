<?php

namespace App\Http\Controllers\Api;

use App\Events\RandevuDurumuDegisti;
use App\Http\Controllers\Controller;
use App\Models\Doktor;
use App\Models\DoktorApiToken;
use App\Models\DoktorDeviceToken;
use App\Models\Hasta;
use App\Notifications\MeslekBelgesiAdminBildirimi;
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
     * Public: doctor self-registration (site ile aynı niyet: paket → kayıt → meslek → ödeme).
     * Doğrudan üyelik açılmaz; kayit_paket_id saklanır, meslek onayı sonrası ödeme.
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
                'regex:~^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$~',
                'confirmed',
            ],
            'telefon' => ['required', 'string', 'max:50'],
            'tc_kimlik_no' => ['required', 'string', 'size:11', 'unique:doktorlar,tc_kimlik_no'],
            'diploma_no' => ['nullable', 'string', 'min:3', 'max:64'],
            'edevlet_barkod' => ['nullable', 'string', 'max:64'],
            'unvan' => ['required', 'string', 'max:100'],
            'il_id' => ['required', 'integer', 'exists:iller,id'],
            'ilce_id' => ['required', 'integer', 'exists:ilceler,id'],
            'branslar' => ['required', 'array', 'min:1'],
            'branslar.*' => ['integer', 'exists:branslar,id'],
            'paket_id' => ['required', 'integer', 'exists:paketler,id'],
            'kayit_periyot' => ['required', 'in:aylik,yillik'],
            'kvkk_onay' => ['accepted'],
            'sozlesme_onay' => ['accepted'],
            'mezuniyet' => ['nullable', 'array'],
            'mezuniyet.*' => ['nullable', 'string', 'max:255'],
            'biyografi' => ['nullable', 'string', 'max:5000'],
            'device' => ['nullable', 'string', 'max:120'],
            'referans_kodu' => ['nullable', 'string', 'max:16'],
            'meslek_belgesi' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'mezuniyet_belgeleri' => ['nullable', 'array', 'max:8'],
            'mezuniyet_belgeleri.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ], [
            'e_posta.unique' => 'Bu e-posta adresi zaten kayıtlı.',
            'sifre.confirmed' => 'Şifre tekrarı uyuşmuyor.',
            'sifre.regex' => 'Şifre en az bir büyük harf, bir küçük harf ve bir rakam içermelidir.',
            'branslar.required' => 'En az bir branş seçmelisiniz.',
            'paket_id.required' => 'Kayıt için paket seçimi zorunludur.',
            'tc_kimlik_no.required' => 'T.C. kimlik numarası zorunludur.',
            'tc_kimlik_no.unique' => 'Bu T.C. kimlik numarası ile kayıtlı bir hekim zaten var.',
            'kvkk_onay.accepted' => 'KVKK metnini kabul etmelisiniz.',
            'sozlesme_onay.accepted' => 'Kullanım koşullarını kabul etmelisiniz.',
            'meslek_belgesi.mimes' => 'Belge PDF, JPG veya PNG olmalıdır.',
            'meslek_belgesi.max' => 'Belge en fazla 5 MB olabilir.',
            'mezuniyet_belgeleri.*.mimes' => 'Her belge PDF, JPG veya PNG olmalıdır.',
            'mezuniyet_belgeleri.*.max' => 'Her belge en fazla 5 MB olabilir.',
        ]);

        $tc = preg_replace('/\D/', '', (string) $data['tc_kimlik_no']) ?? '';
        if (strlen($tc) !== 11) {
            return response()->json(['success' => false, 'message' => 'T.C. kimlik numarası 11 haneli olmalıdır.'], 422);
        }

        $barkod = ! empty($data['edevlet_barkod'])
            ? strtoupper(trim((string) $data['edevlet_barkod']))
            : null;
        $diplomaNo = ! empty($data['diploma_no']) ? trim((string) $data['diploma_no']) : null;

        $belgeFiles = [];
        if ($request->hasFile('mezuniyet_belgeleri')) {
            foreach ($request->file('mezuniyet_belgeleri') as $f) {
                if ($f) {
                    $belgeFiles[] = $f;
                }
            }
        }
        if ($request->hasFile('meslek_belgesi')) {
            $belgeFiles[] = $request->file('meslek_belgesi');
        }

        // Otomatik e-Devlet yok: en az bir belge (veya diploma/barkod + belge tercih)
        if ($belgeFiles === [] && ! $diplomaNo && ! $barkod) {
            return response()->json([
                'success' => false,
                'message' => 'En az bir mezuniyet / meslek belgesi yükleyin veya diploma / tescil no girin. Belgeleriniz yönetici onayına gidecektir.',
            ], 422);
        }
        if ($belgeFiles === []) {
            return response()->json([
                'success' => false,
                'message' => 'Meslek belgesi (PDF veya fotoğraf) yüklemeniz zorunludur. Kaydınız yönetici onayına düşecektir.',
            ], 422);
        }

        $kayitPaket = \App\Models\Paket::where('aktif_mi', true)->findOrFail($data['paket_id']);
        $kayitPeriyot = $data['kayit_periyot'];

        $bransIsimleri = \App\Models\Brans::whereIn('id', $data['branslar'])->pluck('ad')->toArray();
        $mezuniyet = array_values(array_filter($data['mezuniyet'] ?? [], fn ($v) => $v !== null && trim((string) $v) !== ''));

        // Her zaman yönetici onayı — sistem otomatik doğrulamaz
        $meslekDurum = 'beklemede';
        $meslekNot = 'manuel_yukleme:mobil;'.count($belgeFiles).' belge; yonetici onayi bekleniyor';
        $belgeRel = null;
        $storedPaths = [];
        foreach ($belgeFiles as $file) {
            $storedPaths[] = $file->store('private/mezuniyet-yukleme', 'local');
        }
        $belgeRel = $storedPaths[0] ?? null;

        $doktor = \Illuminate\Support\Facades\DB::transaction(function () use (
            $data,
            $bransIsimleri,
            $mezuniyet,
            $kayitPaket,
            $kayitPeriyot,
            $tc,
            $diplomaNo,
            $barkod,
            $meslekDurum,
            $meslekNot,
            $belgeRel,
            $storedPaths
        ) {
            $doktor = Doktor::create([
                'ad_soyad' => $data['ad_soyad'],
                'e_posta' => mb_strtolower(trim($data['e_posta'])),
                'sifre' => Hash::make($data['sifre']),
                'telefon' => $data['telefon'],
                'tc_kimlik_no' => $tc,
                'diploma_no' => $diplomaNo,
                'edevlet_barkod' => $barkod,
                'meslek_belge_yolu' => $belgeRel,
                'meslek_dogrulama_durumu' => $meslekDurum,
                'meslek_dogrulama_notu' => $meslekNot ? \Illuminate\Support\Str::limit((string) $meslekNot, 500) : null,
                'meslek_dogrulandi_at' => null,
                'il_id' => $data['il_id'],
                'ilce_id' => $data['ilce_id'],
                'unvan' => $data['unvan'],
                'uzmanlik_alani' => implode(', ', $bransIsimleri),
                'mezuniyet' => $mezuniyet,
                'biyografi' => $data['biyografi'] ?? null,
                'tur' => method_exists($kayitPaket, 'klinikPaketiMi') && $kayitPaket->klinikPaketiMi()
                    ? 'klinik'
                    : 'bireysel',
                'paket_id' => null,
                'kayit_paket_id' => $kayitPaket->id,
                'kayit_periyot' => $kayitPeriyot,
                'aktif_mi' => true,
                'platformda_gorunur' => false,
            ]);
            $doktor->branslar()->attach($data['branslar']);

            if (class_exists(\App\Models\DoktorMezuniyetBelgesi::class)) {
                foreach ($storedPaths as $path) {
                    \App\Models\DoktorMezuniyetBelgesi::create([
                        'doktor_id' => $doktor->id,
                        'barkod' => $barkod,
                        'tc_kimlik_no' => $tc,
                        'ad_soyad_belge' => $data['ad_soyad'],
                        'diploma_no' => $diplomaNo,
                        'dogrulama_durumu' => 'beklemede',
                        'eslesme_detay' => ['nedenler' => ['manuel_yukleme', 'mobil']],
                        'dosya_yolu' => $path,
                        'auto_onay_uygun' => false,
                    ]);
                }
            }

            if (! empty($data['referans_kodu']) && class_exists(\App\Services\ReferansService::class)) {
                try {
                    app(\App\Services\ReferansService::class)->attachOnRegister(
                        $doktor,
                        (string) $data['referans_kodu']
                    );
                    app(\App\Services\ReferansService::class)->ensureKod($doktor);
                } catch (\Throwable $e) {
                    // referans opsiyonel
                }
            }

            return $doktor;
        });

        if ($belgeRel && ($meslekDurum ?? '') === 'beklemede') {
            MeslekBelgesiAdminBildirimi::notifyAdmin($doktor, 'mobil_kayit');
        }

        $response = $this->authenticatedResponse($doktor, $data['device'] ?? 'mobile-register', $request->ip());
        $payload = $response->getData(true);
        $payload['data']['next_step'] = 'meslek_bekleme';
        $payload['data']['meslek_dogrulama_durumu'] = $meslekDurum;
        $payload['data']['kayit_paket_id'] = $kayitPaket->id;
        $payload['data']['kayit_periyot'] = $kayitPeriyot;
        $payload['message'] = 'Kaydınız alındı. Belgeleriniz yönetici onayına gönderildi. Onay sonrası paket ödemesine geçebilirsiniz.';

        return response()->json($payload, $response->status());
    }

    /**
     * Authenticated: meslek doğrulama durumu (kayıt sonrası bekleme ekranı).
     */
    public function meslekStatus(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $doktor->refresh();
        $doktor->loadMissing('kayitPaketi');

        return response()->json([
            'success' => true,
            'data' => [
                'durum' => $doktor->meslek_dogrulama_durumu ?? 'beklemede',
                'can_proceed' => method_exists($doktor, 'canProceedToPayment')
                    ? $doktor->canProceedToPayment()
                    : (($doktor->meslek_dogrulama_durumu ?? '') === 'onaylandi'),
                'kayit_paket_id' => $doktor->kayit_paket_id,
                'kayit_periyot' => $doktor->kayit_periyot,
                'kayit_paket_ad' => $doktor->kayitPaketi?->ad,
                'not' => $doktor->meslek_dogrulama_notu,
            ],
        ]);
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
            'durum' => ['required', 'in:onaylandi,iptal,tamamlandi,beklemede,gelmedi'],
        ], [
            'durum.required' => 'Durum alanı zorunludur.',
            'durum.in' => 'Geçersiz randevu durumu.',
        ]);

        // Vitrin: onay/red için randevu_talepleri gerekir
        $yonetimGereken = in_array($data['durum'], ['onaylandi', 'iptal'], true)
            && $randevu->durum === 'beklemede';
        if ($yonetimGereken && ! $doktor->hasPaketFeature('randevu_talepleri')) {
            return response()->json([
                'success' => false,
                'message' => 'Randevu taleplerini onaylamak veya reddetmek için ücretli bir pakete geçmelisiniz.',
                'feature' => 'randevu_talepleri',
            ], 403);
        }

        $eskiDurum = $randevu->durum;
        $randevu->update([
            'durum' => $data['durum'],
        ]);

        if ($eskiDurum !== $data['durum']) {
            RandevuDurumuDegisti::dispatch($randevu, $eskiDurum, $data['durum']);
        }

        // No-show SMS
        if ($data['durum'] === 'gelmedi' && $eskiDurum !== 'gelmedi' && $doktor->hasPaketFeature('no_show_mesaj')) {
            try {
                $hasta = $randevu->hasta;
                if ($hasta && ! empty($hasta->telefon)) {
                    $kontor = app(\App\Services\SmsKontorService::class);
                    if ($kontor->doktorGonderebilir($doktor)) {
                        $ad = $hasta->ad_soyad ?? trim(($hasta->ad ?? '').' '.($hasta->soyad ?? '')) ?: 'Hasta';
                        $msg = "Sayin {$ad}, randevunuza ulasilamadi. Yeni slot icin hekim profilinden randevu alabilirsiniz. ".config('app.url');
                        if (app(\App\Services\SmsService::class)->send($hasta->telefon, $msg, $doktor->resolveSmsHeader())) {
                            $kontor->tuket($doktor, 1);
                        }
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Mobile no-show SMS: '.$e->getMessage());
            }
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
            'gorusme_tipi' => ['nullable', 'in:yuz_yuze,online'],
            'meeting_url' => ['nullable', 'url', 'max:500'],
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
        if (! empty($data['gorusme_tipi'])) {
            if ($data['gorusme_tipi'] === 'online' && ! $doktor->hasPaketFeature('online_gorusme')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Online görüşme mevcut paketinizde yok.',
                    'feature' => 'online_gorusme',
                ], 403);
            }
            $randevu->gorusme_tipi = $data['gorusme_tipi'];
        }
        if (array_key_exists('meeting_url', $data)) {
            $randevu->meeting_url = $data['meeting_url'] ?: null;
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
            'seri' => ['nullable', 'boolean'],
            'seri_adet' => ['nullable', 'integer', 'min:2', 'max:52'],
            'seri_aralik_gun' => ['nullable', 'integer', 'min:1', 'max:90'],
        ]);

        $not = $data['aciklama'] ?? null;

        $seri = (bool) ($data['seri'] ?? false) || (int) ($data['seri_adet'] ?? 0) > 1;
        if ($seri && ! $doktor->hasPaketFeature('seri_randevu')) {
            return response()->json([
                'success' => false,
                'message' => 'Seri randevu mevcut paketinizde yok. Paketinizi yükseltin.',
                'feature' => 'seri_randevu',
            ], 403);
        }

        $newDateTime = Carbon::parse($data['tarih'].' '.$data['saat']);
        if ($newDateTime->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçmiş bir tarihe veya saate randevu eklenemez.',
            ], 422);
        }

        $hasta = Hasta::findOrFail($data['danisan_id']);
        $adet = $seri ? max(2, min(52, (int) ($data['seri_adet'] ?? 2))) : 1;
        $aralik = max(1, min(90, (int) ($data['seri_aralik_gun'] ?? 7)));
        $baslangic = Carbon::parse($data['tarih']);
        $last = null;
        $created = 0;

        try {
            for ($i = 0; $i < $adet; $i++) {
                $tarih = $baslangic->copy()->addDays($i * $aralik)->toDateString();
                $last = $bookingService->create([
                    'doktor' => $doktor,
                    'hasta' => $hasta,
                    'hizmet_id' => (int) $data['hizmet_id'],
                    'tarih' => $tarih,
                    'saat' => $data['saat'],
                    'not' => $not,
                    'ad' => $hasta->ad,
                    'soyad' => $hasta->soyad,
                    'telefon' => $hasta->telefon,
                    'e_posta' => $hasta->e_posta,
                    'durum' => 'onaylandi',
                    'gorusme_tipi' => ($data['gorusme_tipi'] ?? null) === 'online' ? 'online' : 'yuz_yuze',
                ]);
                $created++;
            }
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage().($created > 0 ? " ({$created} randevu oluşturuldu.)" : ''),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $created > 1
                ? "{$created} seri randevu başarıyla oluşturuldu."
                : 'Randevu başarıyla oluşturuldu.',
            'adet' => $created,
            'data' => $last ? $this->appointmentPayload($last->load(['hasta', 'hizmet'])) : null,
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
                'tc_kimlik_no' => $doktor->tc_kimlik_no,
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
            'tc_kimlik_no' => ['sometimes', 'nullable', 'string', 'size:11', 'regex:/^[1-9][0-9]{10}$/'],
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
            'profil_resmi_sil' => ['sometimes', 'nullable'],
        ]);

        $update = $request->only([
            'ad_soyad', 'unvan', 'telefon', 'adres', 'uzmanlik_alani', 'biyografi',
            'il_id', 'ilce_id', 'enlem', 'boylam',
            'instagram', 'facebook', 'twitter', 'linkedin', 'youtube', 'web_sitesi',
        ]);

        // Dış bağlantılar paketsiz kaydedilmez
        if (! $doktor->hasPaketFeature('dis_baglanti')) {
            foreach (['instagram', 'facebook', 'twitter', 'linkedin', 'youtube', 'web_sitesi'] as $sosyal) {
                if (array_key_exists($sosyal, $update)) {
                    unset($update[$sosyal]);
                }
            }
        }

        // Web panelindeki gibi: boş gönderim mevcut T.C. kimliği silmez.
        if ($request->filled('tc_kimlik_no')) {
            $update['tc_kimlik_no'] = $request->input('tc_kimlik_no');
        }

        if ($request->hasFile('profil_resmi')) {
            \App\Support\PublicMedia::delete($doktor->profil_resmi);
            $update['profil_resmi'] = \App\Support\PublicMedia::store($request->file('profil_resmi'), 'uploads/profil');
        } elseif ($request->boolean('profil_resmi_sil')) {
            \App\Support\PublicMedia::delete($doktor->profil_resmi);
            $update['profil_resmi'] = null;
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
        // Danışan yorumları çekirdek panel özelliği (tüm paketlerde list+yanıt)
        if ($paket && ! in_array('yorum', $features, true)) {
            $features[] = 'yorum';
        }
        $demoMu = $paket === null
            || (method_exists($doktor, 'isOnTrial') && $doktor->isOnTrial())
            || ($doktor->odeme_periyodu ?? '') === 'deneme';

        $willAuto = method_exists($doktor, 'willAutoRenew') ? (bool) $doktor->willAutoRenew() : false;
        $hasCard = method_exists($doktor, 'hasPaytrSavedCard') ? (bool) $doktor->hasPaytrSavedCard() : false;
        if ($kaynak === 'klinik' && $doktor->klinik) {
            $willAuto = method_exists($doktor->klinik, 'willAutoRenew')
                ? (bool) $doktor->klinik->willAutoRenew()
                : $willAuto;
            $hasCard = method_exists($doktor->klinik, 'hasPaytrSavedCard')
                ? (bool) $doktor->klinik->hasPaytrSavedCard()
                : $hasCard;
        }
        $tahmini = method_exists($doktor, 'estimatedRenewalAmount')
            ? $doktor->estimatedRenewalAmount()
            : null;
        $periyot = $kaynak === 'klinik' && $doktor->klinik
            ? ($doktor->klinik->odeme_periyodu ?? $doktor->odeme_periyodu)
            : $doktor->odeme_periyodu;
        $periyotLabel = $periyot === 'yillik' ? 'yıllık' : ($periyot === 'aylik' ? 'aylık' : ($periyot === 'deneme' ? 'deneme' : ($periyot ?? null)));

        return [
            'uyelik_baslangic' => $baslangicStr,
            'uyelik_bitis' => $bitisStr,
            'kalan_gun' => $kalanGun,
            'uyelik_aktif_mi' => $aktifMi,
            'demo_mu' => $demoMu,
            'kaynak' => $kaynak,
            'odeme_periyodu' => $periyot,
            'odeme_periyodu_label' => $periyotLabel,
            'will_auto_renew' => $willAuto,
            'has_saved_card' => $hasCard,
            'estimated_renewal_amount' => $tahmini,
            'yenileme_kapali' => (bool) ($doktor->abonelik_yenileme_kapali ?? false),
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

        // features = allowlist. Boş liste = premium modül yok (demo/starter çekirdek panel).
        // yorum her aktif pakette (çekirdek) — menü "paket yükselt" göstermesin.
        if (($membership['paket'] ?? null) && ! in_array('yorum', $features, true)) {
            $features[] = 'yorum';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'paket' => $membership['paket'],
                'features' => array_values($features),
                'restrict' => true,
                'feature_mode' => 'allowlist',
                'uyelik' => $membership,
            ],
        ]);
    }

    /**
     * Abonelik durumu + iptal edilebilirlik.
     * Web paneli karşılığı: hekim.uyelik
     */
    public function subscription(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $doktor->loadMissing(['paket', 'klinik.paket']);

        $klinikSahibi = method_exists($doktor, 'klinikSahibiMi') && $doktor->klinikSahibiMi();
        $klinik = $klinikSahibi ? $doktor->klinik : null;

        $klinikIptalEdilebilir = false;
        if ($klinik) {
            $klinikIptalEdilebilir = (bool) $klinik->uyelik_bitis
                && ! $klinik->uyelik_bitis->isPast()
                && ! ($klinik->abonelik_yenileme_kapali ?? false);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'uyelik' => $this->membershipPayload($doktor),
                'bireysel' => [
                    'iptal_edilebilir' => method_exists($doktor, 'canCancelSubscription')
                        ? (bool) $doktor->canCancelSubscription()
                        : false,
                    'yenileme_kapali' => (bool) $doktor->abonelik_yenileme_kapali,
                    'iptal_at' => $doktor->abonelik_iptal_at?->toIso8601String(),
                    'iptal_nedeni' => $doktor->abonelik_iptal_nedeni,
                ],
                'klinik' => $klinik ? [
                    'id' => $klinik->id,
                    'ad' => $klinik->ad,
                    'sahip_mi' => true,
                    'uyelik_bitis' => $klinik->uyelik_bitis?->toDateString(),
                    'iptal_edilebilir' => $klinikIptalEdilebilir,
                    'yenileme_kapali' => (bool) ($klinik->abonelik_yenileme_kapali ?? false),
                ] : null,
                // Klinik üyesi (sahip değil) kendi aboneliğini yönetemez.
                'salt_okunur' => method_exists($doktor, 'klinikteMi')
                    && $doktor->klinikteMi()
                    && ! $klinikSahibi,
            ],
        ]);
    }

    /**
     * Aboneliği iptal et (dönem sonuna kadar erişim sürer).
     * Web paneli karşılığı: hekim.uyelik.iptal
     *
     * NOT: Mağaza (App Store / Google Play) üzerinden alınmış abonelikler
     * sunucudan iptal edilemez; uygulama kullanıcıyı mağaza yönetim ekranına yönlendirir.
     */
    public function cancelSubscription(
        Request $request,
        \App\Services\IyzicoSubscriptionService $iyzico,
        \App\Services\PaytrService $paytr
    ): JsonResponse {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');

        $data = $request->validate([
            'onay' => ['required', 'accepted'],
            'neden' => ['nullable', 'string', 'max:255'],
            'hedef' => ['nullable', 'in:bireysel,klinik'],
        ], [
            'onay.accepted' => 'İptal için onay vermelisiniz.',
        ]);

        $hedef = $data['hedef'] ?? 'bireysel';
        $neden = $data['neden'] ?? null;

        if ($hedef === 'klinik') {
            return $this->cancelClinicSubscription($doktor, $neden, $iyzico, $paytr);
        }

        if (method_exists($doktor, 'klinikteMi') && $doktor->klinikteMi()
            && ! (method_exists($doktor, 'klinikSahibiMi') && $doktor->klinikSahibiMi())) {
            return response()->json([
                'success' => false,
                'message' => 'Klinik aboneliğini yalnızca klinik sahibi yönetebilir.',
            ], 403);
        }

        if (method_exists($doktor, 'canCancelSubscription') && ! $doktor->canCancelSubscription()) {
            return response()->json([
                'success' => false,
                'message' => $doktor->abonelik_yenileme_kapali
                    ? 'Aboneliğiniz zaten iptal sürecinde. Erişim '
                        .($doktor->uyelik_bitis?->format('d.m.Y') ?? 'dönem sonu')
                        .' tarihine kadar sürer.'
                    : 'İptal edilecek aktif abonelik bulunamadı.',
            ], 422);
        }

        $ref = (string) ($doktor->iyzico_subscription_reference_code ?? '');
        $isPaytr = $paytr->isPaytrReference($ref);
        $isRealIyzico = $iyzico->isRealSubscriptionReference($ref);
        if ($isRealIyzico && ! $isPaytr && $iyzico->isConfigured()) {
            $cancelResult = $iyzico->cancelSubscription($ref);
            if (($cancelResult['status'] ?? '') !== 'success') {
                \Illuminate\Support\Facades\Log::warning(
                    'Legacy iyzico cancel skipped/failed (mobile) — local cancel continues',
                    ['doktor_id' => $doktor->id, 'ref' => $ref]
                );
            }
        }

        $doktor->forceFill([
            'abonelik_yenileme_kapali' => true,
            'abonelik_iptal_at' => now(),
            'abonelik_iptal_nedeni' => $neden,
            'iyzico_subscription_status' => 'CANCELED',
        ])->save();

        $bitis = $doktor->uyelik_bitis?->format('d.m.Y H:i') ?? 'dönem sonu';

        return response()->json([
            'success' => true,
            'message' => 'Aboneliğiniz iptal edildi. Otomatik yenileme kapatıldı; '
                ."mevcut paketinizi {$bitis} tarihine kadar kullanmaya devam edebilirsiniz.",
            'data' => $this->membershipPayload($doktor->fresh()),
        ]);
    }

    private function cancelClinicSubscription(
        Doktor $doktor,
        ?string $neden,
        \App\Services\IyzicoSubscriptionService $iyzico,
        \App\Services\PaytrService $paytr
    ): JsonResponse {
        $klinik = $doktor->klinik;
        if (! (method_exists($doktor, 'klinikSahibiMi') && $doktor->klinikSahibiMi()) || ! $klinik) {
            return response()->json([
                'success' => false,
                'message' => 'Klinik aboneliğini yalnızca sahip iptal edebilir.',
            ], 403);
        }

        if (! $klinik->uyelik_bitis || $klinik->uyelik_bitis->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Aktif klinik aboneliği bulunamadı.',
            ], 422);
        }

        if ($klinik->abonelik_yenileme_kapali ?? false) {
            return response()->json([
                'success' => false,
                'message' => 'Klinik aboneliği zaten iptal sürecinde. Erişim '
                    .$klinik->uyelik_bitis->format('d.m.Y').' tarihine kadar sürer.',
            ], 422);
        }

        $ref = (string) (
            $klinik->iyzico_subscription_reference_code
            ?: $doktor->iyzico_subscription_reference_code
            ?: ''
        );
        $isPaytr = $paytr->isPaytrReference($ref);
        $isRealIyzico = $iyzico->isRealSubscriptionReference($ref);

        // Web panelindeki gibi: eski iyzico iptali başarısızsa yerel iptal YAPILMAZ.
        if ($isRealIyzico && ! $isPaytr && $iyzico->isConfigured()) {
            $cancelResult = $iyzico->cancelSubscription($ref);
            if (($cancelResult['status'] ?? '') !== 'success') {
                \Illuminate\Support\Facades\Log::error('BLOCKED clinic local cancel (mobile) — iyzico failed', [
                    'klinik_id' => $klinik->id,
                    'ref' => $ref,
                    'result' => $cancelResult,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => ($cancelResult['errorMessage'] ?? 'Eski iyzico klinik iptali başarısız.')
                        .' Yerel iptal yapılmadı.',
                ], 422);
            }
        }

        $attrs = [
            'abonelik_yenileme_kapali' => true,
            'abonelik_iptal_at' => now(),
            'abonelik_iptal_nedeni' => $neden,
            'iyzico_subscription_status' => 'CANCELED',
        ];
        $klinik->forceFill(array_filter(
            $attrs,
            fn ($_, $k) => \Illuminate\Support\Facades\Schema::hasColumn($klinik->getTable(), $k),
            ARRAY_FILTER_USE_BOTH
        ))->save();

        $doktor->forceFill($attrs)->save();

        $bitis = $klinik->uyelik_bitis->format('d.m.Y H:i');

        return response()->json([
            'success' => true,
            'message' => "Klinik aboneliği iptal edildi. Otomatik yenileme kapatıldı; erişim {$bitis} tarihine kadar devam eder.",
            'data' => $this->membershipPayload($doktor->fresh()),
        ]);
    }

    public function packages(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $current = method_exists($doktor, 'aktifPaket') ? $doktor->aktifPaket() : $doktor->paket;
        $membership = $this->membershipPayload($doktor);

        // Klinik sahibi: klinik paketleri. Klinik üyesi (sahip değil): salt okunur üyelik.
        // Solo hekim: bireysel paketler.
        $katalogTur = 'bireysel';
        if (method_exists($doktor, 'klinikSahibiMi') && $doktor->klinikSahibiMi()) {
            $katalogTur = 'klinik';
        } elseif (method_exists($doktor, 'klinikteMi') && $doktor->klinikteMi()) {
            // Üye: kendi bireysel yükseltme listesini görmesin; boş/klinik-only değil,
            // aktif klinik paket özeti membership'te; katalog bireysel kapalı.
            $katalogTur = 'klinik';
        }

        $items = \App\Models\Paket::query()
            ->where('aktif_mi', true)
            ->where('tur', $katalogTur)
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

                $vitrin = method_exists($p, 'vitrinEtiketi') ? $p->vitrinEtiketi() : null;
                $isFeatured = (bool) ($p->one_cikan_mi ?? false)
                    || in_array($vitrin['stil'] ?? '', ['popular'], true);
                $isActive = $current && (int) $current->id === (int) $p->id;

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
                    'aktif_paket_mi' => $isActive,
                    'ucretsiz_mi' => $isFree,
                    'max_doktor_sayisi' => $p->max_doktor_sayisi,
                    'max_personel_sayisi' => $p->max_personel_sayisi,
                    'merkezi_finans_mi' => (bool) ($p->merkezi_finans_mi ?? false),
                    'muhasebeci_giris_mi' => (bool) ($p->merkezi_finans_mi ?? false)
                        || in_array('finans', $features, true),
                    'one_cikan_mi' => (bool) ($p->one_cikan_mi ?? false),
                    'populer_mi' => $isFeatured,
                    'etiket' => $isActive ? 'Aktif' : ($vitrin['label'] ?? null),
                    'etiket_stil' => $isActive ? 'active' : ($vitrin['stil'] ?? null),
                ];
            })
            ->values();

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
        $isKlinikPaket = method_exists($paket, 'klinikPaketiMi')
            ? $paket->klinikPaketiMi()
            : (($paket->tur ?? '') === 'klinik');

        // Klinik paket: yalnızca mevcut klinik sahibi (yeni klinik kaydı web'den)
        if ($isKlinikPaket) {
            $doktor->loadMissing('klinik');
            $isOwner = $doktor->klinik_id
                && method_exists($doktor, 'klinikSahibiMi')
                && $doktor->klinikSahibiMi();
            if (! $isOwner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Klinik paketi için önce klinik sahibi olmalısınız. Yeni klinik kaydı web panelinden yapılır; mevcut klinik paket yükseltmesi mobilden havale ile talep edilebilir.',
                    'code' => 'klinik_owner_required',
                ], 422);
            }
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

            if ($isKlinikPaket && $doktor->klinik) {
                $doktor->klinik->update([
                    'paket_id' => $paket->id,
                    'odeme_periyodu' => $data['odeme_periyodu'],
                    'uyelik_baslangic' => $baslangic,
                    'uyelik_bitis' => $bitis,
                    'iyzico_subscription_status' => 'ACTIVE',
                    'max_doktor_sayisi' => $paket->max_doktor_sayisi ?: $doktor->klinik->max_doktor_sayisi,
                ]);
                $doktor->update([
                    'paket_id' => $paket->id,
                    'odeme_periyodu' => $data['odeme_periyodu'],
                    'uyelik_baslangic' => $baslangic,
                    'uyelik_bitis' => $bitis,
                    'iyzico_subscription_status' => 'ACTIVE',
                    'tur' => 'klinik',
                ]);
            } else {
                $doktor->update([
                    'paket_id' => $paket->id,
                    'odeme_periyodu' => $data['odeme_periyodu'],
                    'uyelik_baslangic' => $baslangic,
                    'uyelik_bitis' => $bitis,
                    'iyzico_subscription_status' => 'ACTIVE',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => $isKlinikPaket
                    ? 'Ücretsiz klinik paketi aktifleştirildi.'
                    : 'Ücretsiz paket aktifleştirildi.',
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
                'kurulum_verisi' => $isKlinikPaket
                    ? [
                        'tur' => 'klinik',
                        'klinik_id' => $doktor->klinik_id,
                        'kaynak' => 'mobile',
                    ]
                    : ['tur' => 'bireysel', 'kaynak' => 'mobile'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $isKlinikPaket
                ? 'Klinik paket havale talebiniz alındı. Onay sonrası klinik paketiniz güncellenir.'
                : 'Havale talebiniz alındı. Onay sonrası paketiniz aktifleşir.',
            'data' => [
                'tutar' => $tutar,
                'odeme_periyodu' => $data['odeme_periyodu'],
                'durum' => 'beklemede',
                'tur' => $isKlinikPaket ? 'klinik' : 'bireysel',
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

        $canTedavi = $doktor->hasPaketFeature('tedavi_gecmisi');
        $canFinans = $doktor->hasPaketFeature('hasta_bakiyeleri') || $doktor->hasPaketFeature('finans');

        $randevular = $canTedavi
            ? $doktor->randevular()
                ->where('hasta_id', $id)
                ->with('hizmet:id,ad,sure')
                ->orderByDesc('tarih')
                ->orderByDesc('saat')
                ->limit(50)
                ->get()
                ->map(fn ($r) => $this->appointmentPayload($r))
            : [];

        $odemeler = collect();
        $toplamBorc = 0.0;
        $toplamOdenen = 0.0;
        $kalanBakiye = 0.0;
        if ($canFinans) {
            $odemeler = $doktor->odemeler()
                ->where('hasta_id', $id)
                ->whereNotIn('durum', ['iptal'])
                ->orderByDesc('odeme_tarihi')
                ->orderByDesc('id')
                ->limit(40)
                ->get(['id', 'tutar', 'odenen_tutar', 'durum', 'odeme_yontemi', 'odeme_tarihi', 'aciklama']);
            $aciklar = $odemeler->whereIn('durum', ['beklemede', 'kismi_odeme']);
            $toplamBorc = (float) $aciklar->sum('tutar');
            $toplamOdenen = (float) $odemeler->sum('odenen_tutar');
            $kalanBakiye = max(0, $toplamBorc - $toplamOdenen);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $hasta->id,
                'ad' => $hasta->ad,
                'soyad' => $hasta->soyad,
                'telefon' => $hasta->telefon,
                'e_posta' => $hasta->e_posta,
                'randevular' => $randevular,
                'tedavi_gecmisi_acik' => $canTedavi,
                'finans' => $canFinans ? [
                    'toplam_borc' => $toplamBorc,
                    'toplam_odenen' => $toplamOdenen,
                    'kalan_bakiye' => $kalanBakiye,
                    'odemeler' => $odemeler->map(fn ($o) => [
                        'id' => $o->id,
                        'tutar' => (float) $o->tutar,
                        'odenen_tutar' => (float) $o->odenen_tutar,
                        'durum' => $o->durum,
                        'odeme_yontemi' => $o->odeme_yontemi,
                        'odeme_tarihi' => optional($o->odeme_tarihi)?->format('Y-m-d'),
                        'aciklama' => $o->aciklama,
                    ])->values(),
                ] : null,
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
        return response()->json([
            'success' => false,
            'message' => 'Hasta silme desteklenmez.',
        ], 405);
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
                'resim' => \App\Support\PublicMedia::store($request->file('resim'), 'uploads/hizmet'),
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
        unset($data['resim'], $data['resim_sil']);
        $hizmet->update($data);
        if ($request->hasFile('resim')) {
            \App\Support\PublicMedia::delete($hizmet->resim);
            $hizmet->update([
                'resim' => \App\Support\PublicMedia::store($request->file('resim'), 'uploads/hizmet'),
            ]);
        } elseif ($request->boolean('resim_sil')) {
            \App\Support\PublicMedia::delete($hizmet->resim);
            $hizmet->update(['resim' => null]);
        }

        return response()->json(['success' => true, 'message' => 'Hizmet güncellendi.', 'data' => $hizmet->fresh()]);
    }

    public function destroyService(Request $request, int $id): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $hizmet = $doktor->hizmetler()->findOrFail($id);
        \App\Support\PublicMedia::delete($hizmet->resim);
        $hizmet->delete();

        return response()->json(['success' => true, 'message' => 'Hizmet silindi.']);
    }

    public function appointmentSettings(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $ayarlar = $this->ensureAppointmentSettings($doktor);
        $payload = $ayarlar->toArray();
        $payload['sms_gonderici_baslik'] = $doktor->sms_gonderici_baslik ?? null;
        $payload['can_email_bildirim'] = $doktor->hasPaketFeature('email_bildirim');
        $payload['can_sms_hatirlatma'] = $doktor->hasPaketFeature('sms_hatirlatma');
        $payload['can_sms_baslik'] = $doktor->hasPaketFeature('sms_baslik');
        $payload['can_yorum_davet'] = $doktor->hasPaketFeature('yorum_davet');
        $payload['can_no_show_mesaj'] = $doktor->hasPaketFeature('no_show_mesaj');

        return response()->json(['success' => true, 'data' => $payload]);
    }

    /**
     * Hasta listesi CSV dışa aktarma — web hekim.randevu.hastalar.export karşılığı.
     * Hasta silme yoktur; yalnızca dışa aktarma.
     */
    public function exportPatients(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $hastaIds = $doktor->randevular()->distinct()->pluck('hasta_id');
        $hastalar = Hasta::whereIn('id', $hastaIds)
            ->withCount(['randevular as randevu_sayisi' => fn ($q) => $q->where('doktor_id', $doktor->id)])
            ->orderBy('ad')
            ->orderBy('soyad')
            ->get();

        $out = fopen('php://temp', 'r+');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['ID', 'Ad', 'Soyad', 'Telefon', 'E-posta', 'Randevu Sayısı', 'Durum'], ';');
        foreach ($hastalar as $h) {
            fputcsv($out, [
                $h->id,
                $h->ad,
                $h->soyad,
                $h->telefon,
                $h->e_posta,
                $h->randevu_sayisi ?? 0,
                $h->aktif_mi ? 'Aktif' : 'Pasif',
            ], ';');
        }
        rewind($out);
        $csv = stream_get_contents($out) ?: '';
        fclose($out);

        $filename = 'hastalar-'.now()->format('Y-m-d').'.csv';

        return response()->json([
            'success' => true,
            'data' => [
                'filename' => $filename,
                'csv' => $csv,
                'csv_base64' => base64_encode($csv),
                'count' => $hastalar->count(),
            ],
        ]);
    }

    /**
     * SMS kontör bakiyesi ve ek paketler — web hekim.ek-urun.sms karşılığı (satın alma PayTR web).
     */
    public function smsCredits(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');
        $svc = app(\App\Services\SmsKontorService::class);
        $paket = $svc->paketForDoktor($doktor);
        $packs = [];
        foreach (config('ek_urunler.sms_paketleri', []) as $kod => $pack) {
            $packs[] = [
                'kod' => (string) $kod,
                'adet' => (int) ($pack['adet'] ?? 0),
                'fiyat' => (float) ($pack['fiyat'] ?? 0),
                'etiket' => (string) ($pack['etiket'] ?? ''),
                'not' => $pack['not'] ?? null,
            ];
        }

        $kalan = $svc->kalan($doktor);

        return response()->json([
            'success' => true,
            'data' => [
                'kalan' => $kalan,
                'kullanilan' => $svc->kullanilan($doktor),
                'ek_kontor' => $svc->ekKontor($doktor),
                'paket_kota' => $paket?->sms_aylik_kontor,
                'paket_ad' => $paket?->ad,
                'sinirsiz' => $kalan === null,
                'sms_paketleri' => $packs,
                'satin_alma_url' => url('/hekim/sms-kontor'),
            ],
        ]);
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
            'sms_gonderici_baslik' => ['nullable', 'string', 'max:11'],
        ]);
        // Paket yetkisi olmayan bildirim bayraklarını zorla kapat
        if (! $doktor->hasPaketFeature('email_bildirim')) {
            $data['email_bildirimleri'] = false;
        }
        if (! $doktor->hasPaketFeature('sms_hatirlatma')) {
            $data['sms_bildirimleri'] = false;
        }
        $smsBaslik = $data['sms_gonderici_baslik'] ?? null;
        unset($data['sms_gonderici_baslik']);

        $ayarlar = $this->ensureAppointmentSettings($doktor);
        $ayarlar->update($data);

        if ($doktor->hasPaketFeature('sms_baslik') && filled($smsBaslik)
            && \Illuminate\Support\Facades\Schema::hasColumn('doktorlar', 'sms_gonderici_baslik')
        ) {
            $baslik = mb_strtoupper(preg_replace('/[^A-Za-z0-9 ]/', '', (string) $smsBaslik) ?? '');
            $doktor->update(['sms_gonderici_baslik' => mb_substr(trim($baslik), 0, 11) ?: null]);
        } elseif (! $doktor->hasPaketFeature('sms_baslik')
            && \Illuminate\Support\Facades\Schema::hasColumn('doktorlar', 'sms_gonderici_baslik')
        ) {
            $doktor->update(['sms_gonderici_baslik' => null]);
        }

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
        ];

        $isOnline = ($randevu->gorusme_tipi ?? 'yuz_yuze') === 'online';
        $payload['online_mi'] = $isOnline;
        $payload['meeting_url'] = $isOnline ? ($randevu->meeting_url ?: null) : null;

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
            'resim_sil' => ['nullable'],
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
        $doktor->forceFill(['son_giris_at' => now()])->save();
        if ($doktor->platformda_gorunur === false && method_exists($doktor, 'aktifPaket') && $doktor->aktifPaket()?->ucretsizMi()) {
            $doktor->forceFill(['platformda_gorunur' => true])->save();
        }

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
            'meslek_dogrulama_durumu' => $doktor->meslek_dogrulama_durumu ?? 'beklemede',
            'kayit_paket_id' => $doktor->kayit_paket_id,
            'kayit_periyot' => $doktor->kayit_periyot,
            'paket_id' => $doktor->paket_id,
            'platformda_gorunur' => (bool) ($doktor->platformda_gorunur ?? false),
        ];
    }
}
