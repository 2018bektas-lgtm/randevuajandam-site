<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Brans;
use App\Models\Doktor;
use App\Models\DoktorGaleri;
use App\Models\Hasta;
use App\Models\HastaApiToken;
use App\Models\Hizmet;
use App\Models\Il;
use App\Models\Klinik;
use App\Models\Randevu;
use App\Models\Yorum;
use App\Services\AppointmentBookingService;
use App\Services\MeetingRoomService;
use App\Services\RecaptchaService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Mobil hasta uygulaması JSON API (ana site /site).
 */
class MobilePatientController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'e_posta' => ['required', 'email'],
            'sifre' => ['required', 'string'],
            'device' => ['nullable', 'string', 'max:120'],
        ]);

        $key = 'mobile-hasta-login:'.Str::lower($data['e_posta']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 8)) {
            return response()->json(['success' => false, 'message' => 'Çok fazla deneme. Lütfen bekleyin.'], 429);
        }

        $hasta = Hasta::where('e_posta', $data['e_posta'])->first();
        if (! $hasta || ! Hash::check($data['sifre'], $hasta->sifre)) {
            RateLimiter::hit($key, 300);

            return response()->json(['success' => false, 'message' => 'E-posta veya şifre hatalı.'], 422);
        }
        if (! $hasta->aktif_mi) {
            return response()->json(['success' => false, 'message' => 'Hesabınız pasif.'], 403);
        }

        RateLimiter::clear($key);
        $token = HastaApiToken::issue($hasta, $data['device'] ?? null);

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token->token,
                'expires_at' => $token->expires_at?->toIso8601String(),
                'hasta' => $this->hastaPayload($hasta),
            ],
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ad' => ['required', 'string', 'max:100'],
            'soyad' => ['required', 'string', 'max:100'],
            'e_posta' => ['required', 'email', 'max:255', 'unique:hastalar,e_posta'],
            'telefon' => ['required', 'string', 'max:30'],
            'sifre' => ['required', 'string', 'min:6', 'max:100'],
            'device' => ['nullable', 'string', 'max:120'],
        ]);

        $hasta = Hasta::create([
            'ad' => $data['ad'],
            'soyad' => $data['soyad'],
            'e_posta' => $data['e_posta'],
            'telefon' => $data['telefon'],
            'sifre' => $data['sifre'],
            'aktif_mi' => true,
        ]);

        $token = HastaApiToken::issue($hasta, $data['device'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Üyelik oluşturuldu.',
            'data' => [
                'token' => $token->token,
                'expires_at' => $token->expires_at?->toIso8601String(),
                'hasta' => $this->hastaPayload($hasta),
            ],
        ], 201);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var Hasta $hasta */
        $hasta = $request->attributes->get('auth_hasta');

        return response()->json(['success' => true, 'data' => $this->hastaPayload($hasta)]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var HastaApiToken|null $token */
        $token = $request->attributes->get('auth_hasta_token');
        if ($token) {
            $token->delete();
        }

        return response()->json(['success' => true, 'message' => 'Çıkış yapıldı.']);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        /** @var Hasta $hasta */
        $hasta = $request->attributes->get('auth_hasta');

        $data = $request->validate([
            'ad' => ['sometimes', 'required', 'string', 'max:100'],
            'soyad' => ['sometimes', 'required', 'string', 'max:100'],
            'telefon' => ['sometimes', 'required', 'string', 'max:30'],
            'e_posta' => ['sometimes', 'required', 'email', 'max:255', 'unique:hastalar,e_posta,'.$hasta->id],
        ]);

        $hasta->fill($data);
        $hasta->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil güncellendi.',
            'data' => $this->hastaPayload($hasta->fresh()),
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        /** @var Hasta $hasta */
        $hasta = $request->attributes->get('auth_hasta');

        $data = $request->validate([
            'mevcut_sifre' => ['required', 'string'],
            'yeni_sifre' => ['required', 'string', 'min:6', 'max:100'],
        ]);

        if (! Hash::check($data['mevcut_sifre'], $hasta->sifre)) {
            return response()->json(['success' => false, 'message' => 'Mevcut şifre hatalı.'], 422);
        }

        $hasta->sifre = $data['yeni_sifre'];
        $hasta->save();

        return response()->json(['success' => true, 'message' => 'Şifre güncellendi.']);
    }

    /**
     * Google / Apple — henüz production OAuth yok; net hata mesajı.
     */
    public function socialLogin(Request $request): JsonResponse
    {
        $request->validate([
            'provider' => ['required', 'in:google,apple'],
            'id_token' => ['nullable', 'string'],
        ]);

        return response()->json([
            'success' => false,
            'code' => 'social_not_configured',
            'message' => 'Google / Apple ile giriş yakında. Şimdilik e-posta ve şifre kullanın.',
        ], 501);
    }

    public function filtersMeta(): JsonResponse
    {
        $branslar = Brans::query()->orderBy('ad')->get(['id', 'ad']);
        $iller = Il::query()->orderBy('ad')->get(['id', 'ad']);

        return response()->json([
            'success' => true,
            'data' => [
                'branslar' => $branslar->map(fn ($b) => ['id' => $b->id, 'ad' => $b->ad])->values(),
                'iller' => $iller->map(fn ($i) => ['id' => $i->id, 'ad' => $i->ad])->values(),
                'gorusme_tipleri' => [
                    ['id' => 'yuz_yuze', 'ad' => 'Yüz yüze'],
                    ['id' => 'online', 'ad' => 'Online'],
                ],
            ],
        ]);
    }

    public function doctors(Request $request): JsonResponse
    {
        $q = Doktor::platformdaListelenen()
            ->where('tur', 'bireysel')
            ->with(['branslar:id,ad', 'il:id,ad', 'ilce:id,ad', 'paket'])
            ->orderBy('ad_soyad');

        if ($request->filled('arama')) {
            $a = $request->string('arama')->toString();
            $q->where(function ($w) use ($a) {
                $w->where('ad_soyad', 'like', "%{$a}%")
                    ->orWhere('uzmanlik_alani', 'like', "%{$a}%");
            });
        }
        if ($request->filled('brans')) {
            $b = $request->string('brans')->toString();
            $q->where(function ($w) use ($b) {
                $w->where('uzmanlik_alani', 'like', "%{$b}%")
                    ->orWhereHas('branslar', fn ($b2) => $b2->where('ad', 'like', "%{$b}%"));
            });
        }
        if ($request->filled('il_id')) {
            $q->where('il_id', (int) $request->get('il_id'));
        }
        if ($request->boolean('online')) {
            $q->whereHas('paket.sistemOzellikleri', fn ($sq) => $sq->where('kod', 'online_gorusme'));
        }
        if ($request->boolean('randevuya_acik')) {
            $q->where('randevuya_acik_mi', true);
        }
        if ($request->boolean('harita')) {
            $q->whereNotNull('enlem')->whereNotNull('boylam');
        }

        $items = $q->paginate(min((int) $request->get('per_page', 20), 50));

        return response()->json([
            'success' => true,
            'data' => [
                'items' => collect($items->items())->map(fn (Doktor $d) => $this->doktorCard($d))->values(),
                'meta' => [
                    'current_page' => $items->currentPage(),
                    'last_page' => $items->lastPage(),
                    'total' => $items->total(),
                ],
            ],
        ]);
    }

    public function clinics(Request $request): JsonResponse
    {
        $q = Klinik::query()
            ->where('aktif_mi', true)
            ->with(['il:id,ad', 'ilce:id,ad'])
            ->orderBy('ad');

        if ($request->filled('arama')) {
            $a = $request->string('arama')->toString();
            $q->where(function ($w) use ($a) {
                $w->where('ad', 'like', "%{$a}%")
                    ->orWhere('adres', 'like', "%{$a}%")
                    ->orWhere('aciklama', 'like', "%{$a}%");
            });
        }
        if ($request->filled('il_id')) {
            $q->where('il_id', (int) $request->get('il_id'));
        }
        if ($request->boolean('harita')) {
            $q->whereNotNull('enlem')->whereNotNull('boylam');
        }

        $items = $q->paginate(min((int) $request->get('per_page', 20), 50));

        return response()->json([
            'success' => true,
            'data' => [
                'items' => collect($items->items())->map(fn (Klinik $k) => $this->klinikCard($k))->values(),
                'meta' => [
                    'current_page' => $items->currentPage(),
                    'last_page' => $items->lastPage(),
                    'total' => $items->total(),
                ],
            ],
        ]);
    }

    public function clinicShow(int $id): JsonResponse
    {
        $k = Klinik::query()
            ->where('aktif_mi', true)
            ->with(['il', 'ilce', 'doktorlar' => fn ($q) => $q->where('aktif_mi', true)->orderBy('ad_soyad')->limit(30)])
            ->findOrFail($id);

        $doktorlar = $k->relationLoaded('doktorlar')
            ? $k->doktorlar->map(fn (Doktor $d) => [
                'id' => $d->id,
                'ad_soyad' => $d->ad_soyad,
                'unvan' => $d->unvan,
                'uzmanlik' => $d->uzmanlik_alani,
            ])->values()
            : [];

        return response()->json([
            'success' => true,
            'data' => array_merge($this->klinikCard($k), [
                'aciklama' => $k->aciklama,
                'adres' => $k->adres,
                'telefon' => $k->telefon,
                'e_posta' => $k->e_posta,
                'web_sitesi' => $k->web_sitesi,
                'doktorlar' => $doktorlar,
            ]),
        ]);
    }

    public function doctorShow(int $id): JsonResponse
    {
        $d = Doktor::platformdaListelenen()
            ->with([
                'hizmetler' => fn ($q) => $q->where('aktif_mi', true)->orderBy('ad'),
                'branslar',
                'il',
                'ilce',
                'randevuAyari',
                'bloglar' => fn ($q) => $q->where('aktif_mi', true)->orderByDesc('created_at')->limit(20),
                'galeriler' => fn ($q) => $q->orderBy('sira')->limit(30),
                'yorumlar' => fn ($q) => $q->onaylandi()->with('hasta:id,ad,soyad')->orderByDesc('created_at')->limit(30),
            ])
            ->findOrFail($id);

        $online = (bool) ($d->aktifPaket()?->hasFeature('online_gorusme'));
        $avg = $d->yorumlar->avg('puan');

        return response()->json([
            'success' => true,
            'data' => array_merge($this->doktorCard($d), [
                'biyografi' => $d->biyografi,
                'adres' => $d->adres,
                'telefon' => $d->telefon ?? null,
                'randevuya_acik_mi' => (bool) $d->randevuya_acik_mi,
                'online_gorusme' => $online,
                'puan_ortalama' => $avg ? round((float) $avg, 1) : null,
                'yorum_sayisi' => $d->yorumlar->count(),
                'hizmetler' => $d->hizmetler->map(fn (Hizmet $h) => $this->hizmetCard($h, $d))->values(),
                'bloglar' => $d->bloglar->map(fn (Blog $b) => $this->blogCard($b, false))->values(),
                'galeri' => $d->galeriler->map(fn (DoktorGaleri $g) => [
                    'id' => $g->id,
                    'baslik' => $g->baslik,
                    'resim' => $this->mediaUrl($g->resim_yolu),
                ])->values(),
                'yorumlar' => $d->yorumlar->map(fn (Yorum $y) => [
                    'id' => $y->id,
                    'puan' => $y->puan,
                    'yorum' => $y->yorum,
                    'doktor_yaniti' => $y->doktor_yaniti,
                    'hasta' => $y->hasta?->maskeli_ad ?? 'Hasta',
                    'tarih' => optional($y->created_at)?->format('Y-m-d'),
                ])->values(),
            ]),
        ]);
    }

    /** Harita pinleri — hekim + klinik */
    public function mapPins(Request $request): JsonResponse
    {
        $doctors = Doktor::platformdaListelenen()
            ->where('tur', 'bireysel')
            ->whereNotNull('enlem')
            ->whereNotNull('boylam')
            ->with(['il:id,ad', 'ilce:id,ad', 'branslar:id,ad', 'paket'])
            ->get()
            ->map(fn (Doktor $d) => [
                'type' => 'doktor',
                'id' => $d->id,
                'title' => trim(($d->unvan ? $d->unvan.' ' : '').$d->ad_soyad),
                'subtitle' => $d->uzmanlik_alani,
                'il' => $d->il?->ad,
                'lat' => (float) $d->enlem,
                'lng' => (float) $d->boylam,
                'online' => (bool) $d->aktifPaket()?->hasFeature('online_gorusme'),
                'color' => '#C96A2B',
            ]);

        $clinics = Klinik::query()
            ->where('aktif_mi', true)
            ->whereNotNull('enlem')
            ->whereNotNull('boylam')
            ->with(['il:id,ad'])
            ->get()
            ->map(fn (Klinik $k) => [
                'type' => 'klinik',
                'id' => $k->id,
                'title' => $k->ad,
                'subtitle' => $k->il?->ad,
                'il' => $k->il?->ad,
                'lat' => (float) $k->enlem,
                'lng' => (float) $k->boylam,
                'online' => false,
                'color' => '#0284C7',
            ]);

        $pins = $doctors->concat($clinics)->values();

        return response()->json([
            'success' => true,
            'data' => [
                'pins' => $pins,
                'center' => $pins->isNotEmpty()
                    ? ['lat' => $pins->avg('lat'), 'lng' => $pins->avg('lng')]
                    : ['lat' => 39.0, 'lng' => 35.0],
            ],
        ]);
    }

    public function blogs(Request $request): JsonResponse
    {
        $q = Blog::query()
            ->where('aktif_mi', true)
            ->whereHas('doktor', fn ($dq) => $dq->platformdaListelenen())
            ->with(['doktor:id,ad_soyad,unvan,profil_resmi,uzmanlik_alani'])
            ->orderByDesc('created_at');

        if ($request->filled('arama')) {
            $a = $request->string('arama')->toString();
            $q->where(function ($w) use ($a) {
                $w->where('baslik', 'like', "%{$a}%")
                    ->orWhere('icerik', 'like', "%{$a}%");
            });
        }
        if ($request->filled('doktor_id')) {
            $q->where('doktor_id', (int) $request->get('doktor_id'));
        }

        $items = $q->paginate(min((int) $request->get('per_page', 15), 40));

        return response()->json([
            'success' => true,
            'data' => [
                'items' => collect($items->items())->map(fn (Blog $b) => $this->blogCard($b, true))->values(),
                'meta' => [
                    'current_page' => $items->currentPage(),
                    'last_page' => $items->lastPage(),
                    'total' => $items->total(),
                ],
            ],
        ]);
    }

    public function blogShow(int $id): JsonResponse
    {
        $b = Blog::query()
            ->where('aktif_mi', true)
            ->whereHas('doktor', fn ($dq) => $dq->platformdaListelenen())
            ->with(['doktor.il', 'doktor.ilce', 'doktor.branslar'])
            ->findOrFail($id);

        $b->increment('okunma_sayisi');

        return response()->json([
            'success' => true,
            'data' => array_merge($this->blogCard($b, true), [
                'icerik' => $b->icerik,
                'okunma_sayisi' => $b->okunma_sayisi,
            ]),
        ]);
    }

    public function services(Request $request): JsonResponse
    {
        $q = Hizmet::query()
            ->where('aktif_mi', true)
            ->whereHas('doktor', fn ($dq) => $dq->platformdaListelenen()->where('tur', 'bireysel'))
            ->with(['doktor:id,ad_soyad,unvan,uzmanlik_alani,profil_resmi'])
            ->orderBy('ad');

        if ($request->filled('arama')) {
            $a = $request->string('arama')->toString();
            $q->where(function ($w) use ($a) {
                $w->where('ad', 'like', "%{$a}%")
                    ->orWhere('aciklama', 'like', "%{$a}%");
            });
        }
        if ($request->filled('doktor_id')) {
            $q->where('doktor_id', (int) $request->get('doktor_id'));
        }

        $items = $q->paginate(min((int) $request->get('per_page', 20), 50));

        return response()->json([
            'success' => true,
            'data' => [
                'items' => collect($items->items())->map(fn (Hizmet $h) => $this->hizmetCard($h, $h->doktor))->values(),
                'meta' => [
                    'current_page' => $items->currentPage(),
                    'last_page' => $items->lastPage(),
                    'total' => $items->total(),
                ],
            ],
        ]);
    }

    public function serviceShow(int $id): JsonResponse
    {
        $h = Hizmet::query()
            ->where('aktif_mi', true)
            ->whereHas('doktor', fn ($dq) => $dq->platformdaListelenen())
            ->with(['doktor.il', 'doktor.ilce', 'doktor.branslar', 'doktor.paket'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => array_merge($this->hizmetCard($h, $h->doktor), [
                'aciklama' => $h->aciklama,
                'doktor' => $this->doktorCard($h->doktor),
            ]),
        ]);
    }

    public function slots(Request $request, int $id, \App\Services\SlotService $slotService): JsonResponse
    {
        $doktor = Doktor::platformdaListelenen()->findOrFail($id);
        $request->validate(['tarih' => ['required', 'date_format:Y-m-d']]);
        $tarih = Carbon::parse($request->string('tarih')->toString())->startOfDay();
        $periyot = $slotService->getPeriyot($doktor);

        $randevular = $doktor->randevular()
            ->whereDate('tarih', $tarih->toDateString())
            ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi'])
            ->get();
        $izinler = method_exists($doktor, 'izinler')
            ? $doktor->izinler()->get()
            : collect();

        $gunluk = $slotService->generateGunlukSlotlar($doktor, $tarih, $randevular, $izinler, $periyot);

        $out = collect($gunluk)
            ->filter(fn ($s) => is_array($s) && ($s['durum'] ?? '') === 'bos')
            ->map(fn ($s) => ['saat' => substr((string) ($s['saat_string'] ?? $s['saat_baslangic'] ?? ''), 0, 5)])
            ->filter(fn ($s) => $s['saat'] !== '')
            ->values();

        return response()->json([
            'success' => true,
            'data' => ['slots' => $out, 'tarih' => $tarih->toDateString()],
        ]);
    }

    public function book(Request $request, AppointmentBookingService $booking): JsonResponse
    {
        /** @var Hasta $hasta */
        $hasta = $request->attributes->get('auth_hasta');

        // Mobil: bearer token + throttle yeterli; reCAPTCHA web formları için.
        // İsteğe bağlı token gönderilirse doğrulanır, yoksa atlanır.
        if ($request->filled('recaptcha_token')) {
            $captcha = app(RecaptchaService::class)->verify($request->input('recaptcha_token'), 'randevu', $request->ip());
            if (! ($captcha['ok'] ?? false)) {
                return response()->json(['success' => false, 'message' => $captcha['message'] ?? 'Güvenlik doğrulaması başarısız.'], 422);
            }
        }

        $data = $request->validate([
            'doktor_id' => ['required', 'integer', 'exists:doktorlar,id'],
            'hizmet_id' => ['required', 'integer', 'exists:hizmetler,id'],
            'tarih' => ['required', 'date_format:Y-m-d'],
            'saat' => ['required', 'date_format:H:i'],
            'not' => ['nullable', 'string', 'max:1000'],
            'gorusme_tipi' => ['nullable', 'in:yuz_yuze,online'],
        ]);

        $doktor = Doktor::platformdaListelenen()->findOrFail($data['doktor_id']);

        try {
            $randevu = $booking->create([
                'doktor' => $doktor,
                'hasta' => $hasta,
                'hizmet_id' => (int) $data['hizmet_id'],
                'tarih' => $data['tarih'],
                'saat' => $data['saat'],
                'not' => $data['not'] ?? null,
                'gorusme_tipi' => ($data['gorusme_tipi'] ?? 'yuz_yuze') === 'online' ? 'online' : 'yuz_yuze',
                'ad' => $hasta->ad,
                'soyad' => $hasta->soyad,
                'telefon' => $hasta->telefon,
                'e_posta' => $hasta->e_posta,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $randevu->load('hizmet', 'doktor');

        return response()->json([
            'success' => true,
            'message' => $randevu->durum === 'onaylandi' ? 'Randevunuz oluşturuldu.' : 'Randevu talebiniz alındı.',
            'data' => $this->randevuPayload($randevu),
        ], 201);
    }

    public function myAppointments(Request $request): JsonResponse
    {
        /** @var Hasta $hasta */
        $hasta = $request->attributes->get('auth_hasta');
        $items = $hasta->randevular()
            ->with(['doktor:id,ad_soyad,unvan,uzmanlik_alani,profil_resmi', 'hizmet:id,ad,sure'])
            ->orderByDesc('tarih')
            ->orderByDesc('saat')
            ->paginate(min((int) $request->get('per_page', 20), 50));

        return response()->json([
            'success' => true,
            'data' => [
                'items' => collect($items->items())->map(fn (Randevu $r) => $this->randevuPayload($r))->values(),
                'meta' => [
                    'current_page' => $items->currentPage(),
                    'last_page' => $items->lastPage(),
                    'total' => $items->total(),
                ],
            ],
        ]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        /** @var Hasta $hasta */
        $hasta = $request->attributes->get('auth_hasta');
        $randevu = $hasta->randevular()->with(['doktor.randevuAyari', 'hasta', 'hizmet'])->findOrFail($id);

        if (! in_array($randevu->durum, ['beklemede', 'onaylandi'], true)) {
            return response()->json(['success' => false, 'message' => 'Bu randevu iptal edilemez.'], 422);
        }

        $eskiDurum = $randevu->durum;
        $randevu->update(['durum' => 'iptal']);

        // auth_hasta attribute is set by HastaMobileToken — listener resolves "hasta" cancel
        \App\Events\RandevuDurumuDegisti::dispatch($randevu->fresh(['doktor.randevuAyari', 'hasta', 'hizmet']), $eskiDurum, 'iptal');

        return response()->json(['success' => true, 'message' => 'Randevu iptal edildi.']);
    }

    protected function hastaPayload(Hasta $h): array
    {
        return [
            'id' => $h->id,
            'ad' => $h->ad,
            'soyad' => $h->soyad,
            'ad_soyad' => $h->ad_soyad,
            'e_posta' => $h->e_posta,
            'telefon' => $h->telefon,
        ];
    }

    protected function mediaUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return str_starts_with($path, 'http') ? $path : url($path);
    }

    protected function blogCard(Blog $b, bool $withDoktor = false): array
    {
        $excerpt = strip_tags((string) $b->icerik);
        $excerpt = mb_strlen($excerpt) > 160 ? mb_substr($excerpt, 0, 160).'…' : $excerpt;

        $data = [
            'id' => $b->id,
            'baslik' => $b->baslik,
            'slug' => $b->slug,
            'ozet' => $excerpt,
            'resim' => $this->mediaUrl($b->resim),
            'okunma_sayisi' => $b->okunma_sayisi,
            'tarih' => optional($b->created_at)?->format('Y-m-d'),
            'doktor_id' => $b->doktor_id,
        ];

        if ($withDoktor && $b->relationLoaded('doktor') && $b->doktor) {
            $data['doktor'] = [
                'id' => $b->doktor->id,
                'ad_soyad' => $b->doktor->ad_soyad,
                'unvan' => $b->doktor->unvan,
                'profil_resmi' => $this->mediaUrl($b->doktor->profil_resmi),
            ];
        }

        return $data;
    }

    protected function hizmetCard(Hizmet $h, ?Doktor $d = null): array
    {
        $data = [
            'id' => $h->id,
            'ad' => $h->ad,
            'sure' => $h->sure,
            'fiyat' => $h->fiyat,
            'resim' => $this->mediaUrl($h->resim ?? null),
            'doktor_id' => $h->doktor_id,
        ];
        if ($d) {
            $data['doktor_ad'] = $d->ad_soyad;
            $data['doktor_unvan'] = $d->unvan;
        }

        return $data;
    }

    protected function doktorCard(Doktor $d): array
    {
        $foto = $this->mediaUrl($d->profil_resmi);

        return [
            'id' => $d->id,
            'ad_soyad' => $d->ad_soyad,
            'unvan' => $d->unvan,
            'uzmanlik' => $d->uzmanlik_alani,
            'il' => $d->il?->ad,
            'ilce' => $d->ilce?->ad,
            'il_id' => $d->il_id,
            'profil_resmi' => $foto,
            'randevuya_acik_mi' => (bool) $d->randevuya_acik_mi,
            'online_gorusme' => (bool) ($d->relationLoaded('paket') || method_exists($d, 'aktifPaket')
                ? $d->aktifPaket()?->hasFeature('online_gorusme')
                : false),
            'branslar' => $d->relationLoaded('branslar')
                ? $d->branslar->pluck('ad')->values()
                : [],
            'enlem' => $d->enlem,
            'boylam' => $d->boylam,
            'adres' => $d->adres,
        ];
    }

    protected function klinikCard(Klinik $k): array
    {
        $logo = $k->logo
            ? (str_starts_with((string) $k->logo, 'http') ? $k->logo : url($k->logo))
            : null;

        return [
            'id' => $k->id,
            'ad' => $k->ad,
            'slug' => $k->slug,
            'logo' => $logo,
            'il' => $k->il?->ad,
            'ilce' => $k->ilce?->ad,
            'il_id' => $k->il_id,
            'enlem' => $k->enlem,
            'boylam' => $k->boylam,
            'telefon' => $k->telefon,
            'adres' => $k->adres,
        ];
    }

    protected function randevuPayload(Randevu $r): array
    {
        $meet = app(MeetingRoomService::class);
        $join = null;
        $canJoin = false;
        if (($r->gorusme_tipi ?? '') === 'online' && $r->durum === 'onaylandi') {
            try {
                $meet->ensureRoom($r);
                $r->refresh();
                $join = $meet->platformJoinUrl($r);
                $canJoin = $meet->canJoin($r);
            } catch (\Throwable) {
                //
            }
        }

        return [
            'id' => $r->id,
            'tarih' => $r->tarih instanceof \DateTimeInterface ? $r->tarih->format('Y-m-d') : (string) $r->tarih,
            'saat' => substr((string) $r->saat, 0, 5),
            'durum' => $r->durum,
            'gorusme_tipi' => $r->gorusme_tipi ?? 'yuz_yuze',
            'not' => $r->not,
            'can_join' => $canJoin,
            'platform_join_url' => $join,
            'doktor' => $r->doktor ? [
                'id' => $r->doktor->id,
                'ad_soyad' => $r->doktor->ad_soyad,
                'unvan' => $r->doktor->unvan,
            ] : null,
            'hizmet' => $r->hizmet ? [
                'id' => $r->hizmet->id,
                'ad' => $r->hizmet->ad,
            ] : null,
        ];
    }
}
