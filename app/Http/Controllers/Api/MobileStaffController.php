<?php

namespace App\Http\Controllers\Api;

use App\Events\RandevuDurumuDegisti;
use App\Http\Controllers\Controller;
use App\Models\Hasta;
use App\Models\KlinikPersonel;
use App\Models\Odeme;
use App\Models\PersonelApiToken;
use App\Models\Randevu;
use App\Services\AppointmentBookingService;
use App\Services\SlotService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use InvalidArgumentException;

class MobileStaffController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'e_posta' => ['required', 'email'],
            'sifre' => ['required', 'string'],
            'device' => ['nullable', 'string', 'max:120'],
        ]);

        $key = 'mobile-staff-login:'.Str::lower($data['e_posta']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 8)) {
            return response()->json(['success' => false, 'message' => 'Çok fazla deneme. Lütfen bekleyin.'], 429);
        }

        $personel = KlinikPersonel::where('e_posta', $data['e_posta'])->first();
        if (! $personel || ! Hash::check($data['sifre'], $personel->sifre)) {
            RateLimiter::hit($key, 300);

            return response()->json(['success' => false, 'message' => 'E-posta veya şifre hatalı.'], 422);
        }
        if (! $personel->aktif_mi) {
            return response()->json([
                'success' => false,
                'message' => 'Hesabınız pasif. Klinik yöneticinizle iletişime geçin.',
            ], 403);
        }

        RateLimiter::clear($key);
        $personel->loadMissing('klinik');

        return $this->authenticatedResponse($personel, $data['device'] ?? null, $request->ip());
    }

    public function me(Request $request): JsonResponse
    {
        /** @var KlinikPersonel $personel */
        $personel = $request->attributes->get('auth_personel');
        $personel->loadMissing('klinik');

        return response()->json([
            'success' => true,
            'data' => $this->personelPayload($personel),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var PersonelApiToken|null $token */
        $token = $request->attributes->get('auth_personel_token');
        if ($token) {
            $token->delete();
        }

        return response()->json(['success' => true, 'message' => 'Oturum kapatıldı.']);
    }

    /**
     * Register Expo / FCM push token for staff device (cached until dedicated table exists).
     */
    public function registerDevice(Request $request): JsonResponse
    {
        /** @var KlinikPersonel $personel */
        $personel = $request->attributes->get('auth_personel');
        $data = $request->validate([
            'push_token' => ['required', 'string', 'max:512'],
            'platform' => ['nullable', 'string', 'in:android,ios,web'],
            'provider' => ['nullable', 'string', 'in:expo,fcm'],
            'device_name' => ['nullable', 'string', 'max:120'],
            'app_version' => ['nullable', 'string', 'max:40'],
        ]);

        \Illuminate\Support\Facades\Cache::put(
            'personel_push_'.$personel->id,
            [
                'token' => $data['push_token'],
                'platform' => $data['platform'] ?? null,
                'provider' => $data['provider'] ?? 'expo',
                'device_name' => $data['device_name'] ?? null,
                'app_version' => $data['app_version'] ?? null,
                'updated_at' => now()->toIso8601String(),
            ],
            now()->addDays(180)
        );

        return response()->json(['success' => true, 'message' => 'Cihaz kaydedildi.']);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        /** @var KlinikPersonel $personel */
        $personel = $request->attributes->get('auth_personel');

        $data = $request->validate([
            'sifre' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'mevcut_sifre' => ['nullable', 'string'],
        ], [
            'sifre.confirmed' => 'Şifre tekrarı uyuşmuyor.',
            'sifre.min' => 'Şifre en az 8 karakter olmalıdır.',
        ]);

        // First-login force change may omit current password
        if ($personel->sifre_degistirildi_mi) {
            if (empty($data['mevcut_sifre']) || ! Hash::check($data['mevcut_sifre'], $personel->sifre)) {
                return response()->json(['success' => false, 'message' => 'Mevcut şifre hatalı.'], 422);
            }
        }

        $personel->update([
            'sifre' => $data['sifre'],
            'sifre_degistirildi_mi' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Şifre güncellendi.',
            'data' => $this->personelPayload($personel->fresh()->load('klinik')),
        ]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $personel = $this->personel($request);
        $klinik = $personel->klinik;
        $doktorIds = $klinik->doktorlar()->where('aktif_mi', true)->pluck('id');

        $today = now()->toDateString();
        $bugunRandevu = Randevu::whereIn('doktor_id', $doktorIds)
            ->whereDate('tarih', $today)
            ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi'])
            ->count();
        $bekleyenTalep = Randevu::whereIn('doktor_id', $doktorIds)
            ->where('durum', 'beklemede')
            ->count();
        $hastaSayisi = $klinik->hastalar()->count();
        $hekimSayisi = $doktorIds->count();

        $sonraki = Randevu::whereIn('doktor_id', $doktorIds)
            ->whereIn('durum', ['beklemede', 'onaylandi'])
            ->where(function ($q) use ($today) {
                $q->whereDate('tarih', '>', $today)
                    ->orWhere(function ($q2) use ($today) {
                        $q2->whereDate('tarih', $today)->where('saat', '>=', now()->format('H:i'));
                    });
            })
            ->with(['doktor:id,ad_soyad', 'hizmet:id,ad', 'hasta:id,ad,soyad'])
            ->orderBy('tarih')
            ->orderBy('saat')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'bugun_randevu' => $bugunRandevu,
                'bekleyen_talep' => $bekleyenTalep,
                'hasta_sayisi' => $hastaSayisi,
                'hekim_sayisi' => $hekimSayisi,
                'sonraki_randevu' => $sonraki ? $this->appointmentPayload($sonraki) : null,
                'yetkiler' => $this->yetkiler($personel),
            ],
        ]);
    }

    public function doctors(Request $request): JsonResponse
    {
        $personel = $this->personel($request);
        $items = $personel->klinik->doktorlar()
            ->where('aktif_mi', true)
            ->orderBy('ad_soyad')
            ->get(['id', 'ad_soyad', 'unvan', 'e_posta']);

        return response()->json([
            'success' => true,
            'data' => $items->map(fn ($d) => [
                'id' => $d->id,
                'ad_soyad' => $d->ad_soyad,
                'unvan' => $d->unvan,
                'e_posta' => $d->e_posta,
            ])->values(),
        ]);
    }

    public function appointments(Request $request): JsonResponse
    {
        $this->requireYetki($request, 'randevu');
        $personel = $this->personel($request);
        $klinik = $personel->klinik;

        $data = $request->validate([
            'doktor_id' => ['nullable', 'integer'],
            'tarih' => ['nullable', 'date'],
            'start' => ['nullable', 'date'],
            'end' => ['nullable', 'date'],
            'durum' => ['nullable', 'string'],
        ]);

        $query = Randevu::query()
            ->whereHas('doktor', fn ($q) => $q->where('klinik_id', $klinik->id)->where('aktif_mi', true))
            ->with(['doktor:id,ad_soyad', 'hizmet:id,ad,sure', 'hasta:id,ad,soyad,telefon']);

        if (! empty($data['doktor_id'])) {
            $klinik->doktorlar()->where('aktif_mi', true)->findOrFail($data['doktor_id']);
            $query->where('doktor_id', $data['doktor_id']);
        }
        if (! empty($data['tarih'])) {
            $query->whereDate('tarih', $data['tarih']);
        } elseif (! empty($data['start']) && ! empty($data['end'])) {
            $query->whereBetween('tarih', [$data['start'], $data['end']]);
        } else {
            $query->whereDate('tarih', now()->toDateString());
        }
        if (! empty($data['durum'])) {
            $query->where('durum', $data['durum']);
        } else {
            $query->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi', 'iptal']);
        }

        $items = $query->orderBy('tarih')->orderBy('saat')->limit(200)->get()
            ->map(fn ($r) => $this->appointmentPayload($r));

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function doctorMeta(Request $request, SlotService $slotService): JsonResponse
    {
        $this->requireYetki($request, 'randevu');
        $personel = $this->personel($request);
        $data = $request->validate([
            'doktor_id' => ['required', 'integer'],
            'tarih' => ['nullable', 'date'],
        ]);

        $doktor = $personel->klinik->doktorlar()->where('aktif_mi', true)->findOrFail($data['doktor_id']);
        $hizmetler = $doktor->hizmetler()->where('aktif_mi', true)->get(['id', 'ad', 'fiyat', 'sure']);

        $slots = [];
        if (! empty($data['tarih'])) {
            $tarih = Carbon::parse($data['tarih']);
            $periyot = $slotService->getPeriyot($doktor);
            $randevular = $doktor->randevular()
                ->whereDate('tarih', $tarih->toDateString())
                ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi'])
                ->get();
            $izinler = $doktor->izinler()
                ->where('baslangic_zaman', '<=', $tarih->toDateString().' 23:59:59')
                ->where('bitis_zaman', '>=', $tarih->toDateString().' 00:00:00')
                ->get();
            $gunluk = $slotService->generateGunlukSlotlar($doktor, $tarih, $randevular, $izinler, $periyot);
            $slots = collect($gunluk)->where('durum', 'bos')->pluck('saat_string')->values()->all();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'hizmetler' => $hizmetler,
                'slots' => $slots,
            ],
        ]);
    }

    public function storeAppointment(Request $request, AppointmentBookingService $bookingService): JsonResponse
    {
        $this->requireYetki($request, 'randevu');
        $personel = $this->personel($request);
        $klinik = $personel->klinik;

        $data = $request->validate([
            'doktor_id' => ['required', 'integer'],
            'hasta_id' => ['required', 'integer'],
            'hizmet_id' => ['required', 'integer'],
            'tarih' => ['required', 'date'],
            'saat' => ['required', 'date_format:H:i'],
            'not' => ['nullable', 'string', 'max:500'],
        ]);

        $doktor = $klinik->doktorlar()->where('aktif_mi', true)->findOrFail($data['doktor_id']);
        $hasta = $klinik->hastalar()->findOrFail($data['hasta_id']);

        try {
            $randevu = $bookingService->create([
                'doktor' => $doktor,
                'hasta' => $hasta,
                'hizmet_id' => (int) $data['hizmet_id'],
                'tarih' => $data['tarih'],
                'saat' => $data['saat'],
                'not' => $data['not'] ?? null,
                'durum' => 'onaylandi',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Randevu oluşturuldu.',
            'data' => $this->appointmentPayload($randevu->load(['doktor:id,ad_soyad', 'hizmet:id,ad', 'hasta:id,ad,soyad'])),
        ]);
    }

    public function rescheduleAppointment(Request $request, int $id, AppointmentBookingService $bookingService): JsonResponse
    {
        $this->requireYetki($request, 'randevu');
        $personel = $this->personel($request);
        $data = $request->validate([
            'tarih' => ['required', 'date'],
            'saat' => ['required', 'date_format:H:i'],
        ]);

        $randevu = $this->clinicAppointment($personel, $id);

        try {
            $bookingService->reschedule($randevu, $data['tarih'], $data['saat']);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'message' => 'Randevu ertelendi.']);
    }

    public function updateAppointment(Request $request, int $id): JsonResponse
    {
        $this->requireYetki($request, 'randevu');
        $personel = $this->personel($request);
        $data = $request->validate([
            'not' => ['nullable', 'string', 'max:500'],
            'durum' => ['required', 'in:beklemede,onaylandi,tamamlandi,iptal'],
        ]);

        $randevu = $this->clinicAppointment($personel, $id);
        $eski = $randevu->durum;
        $randevu->update([
            'not' => $data['not'] ?? $randevu->not,
            'durum' => $data['durum'],
        ]);
        if ($eski !== $data['durum']) {
            event(new RandevuDurumuDegisti($randevu, $eski, $data['durum']));
        }

        return response()->json(['success' => true, 'message' => 'Randevu güncellendi.']);
    }

    public function cancelAppointment(Request $request, int $id): JsonResponse
    {
        $this->requireYetki($request, 'randevu');
        $personel = $this->personel($request);
        $randevu = $this->clinicAppointment($personel, $id);
        $eski = $randevu->durum;
        $randevu->update(['durum' => 'iptal']);
        if ($eski !== 'iptal') {
            event(new RandevuDurumuDegisti($randevu, $eski, 'iptal'));
        }

        return response()->json(['success' => true, 'message' => 'Randevu iptal edildi.']);
    }

    public function requests(Request $request): JsonResponse
    {
        $this->requireYetki($request, 'randevu');
        $personel = $this->personel($request);
        $klinik = $personel->klinik;
        $doktorId = $request->input('doktor_id');

        $query = Randevu::where('durum', 'beklemede')
            ->whereHas('doktor', function ($q) use ($klinik, $doktorId) {
                $q->where('klinik_id', $klinik->id);
                if ($doktorId) {
                    $q->where('id', $doktorId);
                }
            })
            ->with(['doktor:id,ad_soyad', 'hizmet:id,ad', 'hasta:id,ad,soyad,telefon'])
            ->orderBy('tarih')
            ->orderBy('saat');

        $items = $query->limit(100)->get()->map(fn ($r) => $this->appointmentPayload($r));

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function approveRequest(Request $request, int $id): JsonResponse
    {
        $this->requireYetki($request, 'randevu');
        $personel = $this->personel($request);
        $randevu = $this->clinicAppointment($personel, $id);
        $eski = $randevu->durum;
        $randevu->update(['durum' => 'onaylandi']);
        if ($eski !== 'onaylandi') {
            event(new RandevuDurumuDegisti($randevu, $eski, 'onaylandi'));
        }

        return response()->json(['success' => true, 'message' => 'Talep onaylandı.']);
    }

    public function rejectRequest(Request $request, int $id): JsonResponse
    {
        $this->requireYetki($request, 'randevu');
        $personel = $this->personel($request);
        $randevu = $this->clinicAppointment($personel, $id);
        $eski = $randevu->durum;
        $randevu->update(['durum' => 'iptal']);
        if ($eski !== 'iptal') {
            event(new RandevuDurumuDegisti($randevu, $eski, 'iptal'));
        }

        return response()->json(['success' => true, 'message' => 'Talep reddedildi.']);
    }

    public function patients(Request $request): JsonResponse
    {
        // Randevu oluştururken hasta arama da bu endpoint'i kullanır
        $personel = $this->personel($request);
        $y = $this->yetkiler($personel);
        if (! ($y['hasta'] ?? false) && ! ($y['randevu'] ?? false) && ! ($y['odeme'] ?? false)) {
            $this->requireYetki($request, 'hasta');
        }
        $q = trim((string) $request->input('q', ''));

        $query = $personel->klinik->hastalar();
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('ad', 'like', "%{$q}%")
                    ->orWhere('soyad', 'like', "%{$q}%")
                    ->orWhere('e_posta', 'like', "%{$q}%")
                    ->orWhere('telefon', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy('ad')->orderBy('soyad')->limit(50)->get()
            ->map(fn ($h) => $this->patientPayload($h));

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function storePatient(Request $request): JsonResponse
    {
        $this->requireYetki($request, 'hasta');
        $personel = $this->personel($request);

        $data = $request->validate([
            'ad_soyad' => ['required', 'string', 'max:100'],
            'e_posta' => ['required', 'email', 'unique:hastalar,e_posta'],
            'telefon' => ['required', 'string', 'max:50'],
            'sifre' => ['nullable', 'string', 'min:6'],
        ], [
            'e_posta.unique' => 'Bu e-posta zaten kayıtlı.',
        ]);

        $parts = preg_split('/\s+/', trim($data['ad_soyad']), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $soyad = count($parts) > 1 ? array_pop($parts) : '';
        $ad = implode(' ', $parts);
        if ($ad === '') {
            $ad = $data['ad_soyad'];
        }

        $gecici = $data['sifre'] ?? Str::password(10);
        $hasta = Hasta::create([
            'ad' => $ad,
            'soyad' => $soyad,
            'e_posta' => $data['e_posta'],
            'telefon' => $data['telefon'],
            'sifre' => $gecici,
            'aktif_mi' => true,
        ]);
        $personel->klinik->hastalar()->syncWithoutDetaching([$hasta->id => ['kayit_tarihi' => now()]]);

        return response()->json([
            'success' => true,
            'message' => empty($data['sifre']) ? 'Hasta eklendi. Geçici şifre: '.$gecici : 'Hasta eklendi.',
            'data' => $this->patientPayload($hasta),
        ]);
    }

    public function showPatient(Request $request, int $id): JsonResponse
    {
        $this->requireYetki($request, 'hasta');
        $personel = $this->personel($request);
        $hasta = $personel->klinik->hastalar()->findOrFail($id);

        $randevular = Randevu::where('hasta_id', $id)
            ->whereHas('doktor', fn ($q) => $q->where('klinik_id', $personel->klinik_id))
            ->with(['doktor:id,ad_soyad', 'hizmet:id,ad'])
            ->orderByDesc('tarih')
            ->orderByDesc('saat')
            ->limit(50)
            ->get()
            ->map(fn ($r) => $this->appointmentPayload($r));

        return response()->json([
            'success' => true,
            'data' => [
                'hasta' => $this->patientPayload($hasta),
                'randevular' => $randevular,
            ],
        ]);
    }

    public function payments(Request $request): JsonResponse
    {
        $this->requireYetki($request, 'odeme');
        $personel = $this->personel($request);
        $klinik = $personel->klinik;

        $data = $request->validate([
            'doktor_id' => ['nullable', 'integer'],
            'durum' => ['nullable', 'string'],
            'tarih' => ['nullable', 'date'],
        ]);

        $query = Odeme::whereHas('doktor', fn ($q) => $q->where('klinik_id', $klinik->id))
            ->with(['doktor:id,ad_soyad', 'hasta:id,ad,soyad', 'hizmet:id,ad']);

        if (! empty($data['doktor_id'])) {
            $query->where('doktor_id', $data['doktor_id']);
        }
        if (! empty($data['durum'])) {
            $query->where('durum', $data['durum']);
        }
        if (! empty($data['tarih'])) {
            $query->whereDate('odeme_tarihi', $data['tarih']);
        } else {
            $query->whereDate('odeme_tarihi', now()->toDateString());
        }

        $clone = clone $query;
        $toplam = (float) $clone->sum('odenen_tutar');
        $items = $query->orderByDesc('created_at')->limit(100)->get()->map(fn ($o) => [
            'id' => $o->id,
            'tutar' => (float) $o->tutar,
            'odenen_tutar' => (float) $o->odenen_tutar,
            'odeme_yontemi' => $o->odeme_yontemi,
            'durum' => $o->durum,
            'aciklama' => $o->aciklama,
            'odeme_tarihi' => $o->odeme_tarihi
                ? (is_string($o->odeme_tarihi) ? substr($o->odeme_tarihi, 0, 10) : $o->odeme_tarihi->format('Y-m-d'))
                : null,
            'doktor_adi' => $o->doktor?->ad_soyad,
            'hasta_adi' => trim(($o->hasta->ad ?? '').' '.($o->hasta->soyad ?? '')),
            'hizmet' => $o->hizmet?->ad,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'toplam_gelir' => $toplam,
            ],
        ]);
    }

    public function storePayment(Request $request): JsonResponse
    {
        $this->requireYetki($request, 'odeme');
        $personel = $this->personel($request);
        $klinik = $personel->klinik;

        $data = $request->validate([
            'hasta_id' => ['required', 'integer'],
            'doktor_id' => ['required', 'integer'],
            'tutar' => ['required', 'numeric', 'min:0.01'],
            'odeme_yontemi' => ['required', 'in:nakit,kredi_karti,havale,online'],
            'odeme_tarihi' => ['required', 'date'],
            'aciklama' => ['nullable', 'string', 'max:500'],
        ]);

        $doktor = $klinik->doktorlar()->findOrFail($data['doktor_id']);
        $klinik->hastalar()->findOrFail($data['hasta_id']);

        $odeme = Odeme::create([
            'doktor_id' => $doktor->id,
            'hasta_id' => $data['hasta_id'],
            'tutar' => $data['tutar'],
            'odenen_tutar' => $data['tutar'],
            'odeme_yontemi' => $data['odeme_yontemi'],
            'durum' => 'odendi',
            'aciklama' => $data['aciklama'] ?? null,
            'odeme_tarihi' => $data['odeme_tarihi'],
        ]);

        if (method_exists($odeme, 'kalemler')) {
            $odeme->kalemler()->create([
                'tutar' => $data['tutar'],
                'tarih' => $data['odeme_tarihi'],
                'odeme_yontemi' => $data['odeme_yontemi'],
                'not' => 'Personel mobil uygulamasından alınan ödeme',
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Ödeme kaydedildi.', 'data' => ['id' => $odeme->id]]);
    }

    public function destroyPayment(Request $request, int $id): JsonResponse
    {
        $this->requireYetki($request, 'odeme');
        $personel = $this->personel($request);
        $klinik = $personel->klinik;

        $odeme = Odeme::whereHas('doktor', fn ($q) => $q->where('klinik_id', $klinik->id))->findOrFail($id);
        $odeme->update(['durum' => 'iptal']);
        if (method_exists($odeme, 'kalemler')) {
            $odeme->kalemler()->delete();
        }

        return response()->json(['success' => true, 'message' => 'Ödeme iptal edildi.']);
    }

    // ── helpers ──────────────────────────────────────────────

    private function personel(Request $request): KlinikPersonel
    {
        /** @var KlinikPersonel $p */
        $p = $request->attributes->get('auth_personel');
        $p->loadMissing('klinik');

        return $p;
    }

    private function requireYetki(Request $request, string $modul): void
    {
        $personel = $this->personel($request);
        $yetkiler = $this->yetkiler($personel);
        if (! ($yetkiler[$modul] ?? false)) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Bu modül için yetkiniz yok.',
                ], 403)
            );
        }
    }

    private function yetkiler(KlinikPersonel $personel): array
    {
        $y = $personel->yetkiler;
        if (! is_array($y) || $y === []) {
            return [
                'randevu' => true,
                'hasta' => true,
                'odeme' => ($personel->rol ?? '') !== 'sekreter',
                'finans' => false,
            ];
        }

        return [
            'randevu' => (bool) ($y['randevu'] ?? false),
            'hasta' => (bool) ($y['hasta'] ?? false),
            'odeme' => (bool) ($y['odeme'] ?? false),
            'finans' => (bool) ($y['finans'] ?? false),
        ];
    }

    private function clinicAppointment(KlinikPersonel $personel, int $id): Randevu
    {
        return Randevu::whereHas('doktor', fn ($q) => $q->where('klinik_id', $personel->klinik_id))
            ->findOrFail($id);
    }

    private function authenticatedResponse(KlinikPersonel $personel, ?string $device, ?string $ip): JsonResponse
    {
        $token = PersonelApiToken::issue($personel, $device ?: 'staff-mobile', $ip);

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token['plain'],
                'expires_at' => $token['model']->expires_at?->toIso8601String(),
                'personel' => $this->personelPayload($personel),
            ],
        ]);
    }

    private function personelPayload(KlinikPersonel $personel): array
    {
        $personel->loadMissing('klinik');

        return [
            'id' => $personel->id,
            'ad_soyad' => $personel->ad_soyad,
            'e_posta' => $personel->e_posta,
            'telefon' => $personel->telefon,
            'rol' => $personel->rol,
            'sifre_degistirildi_mi' => (bool) $personel->sifre_degistirildi_mi,
            'yetkiler' => $this->yetkiler($personel),
            'klinik' => $personel->klinik ? [
                'id' => $personel->klinik->id,
                'ad' => $personel->klinik->ad,
            ] : null,
        ];
    }

    private function appointmentPayload(Randevu $r): array
    {
        $tarih = $r->tarih instanceof \DateTimeInterface
            ? $r->tarih->format('Y-m-d')
            : substr((string) $r->tarih, 0, 10);

        return [
            'id' => $r->id,
            'tarih' => $tarih,
            'saat' => substr((string) $r->saat, 0, 5),
            'durum' => $r->durum,
            'not' => $r->not ?? $r->aciklama ?? null,
            'hasta_id' => $r->hasta_id,
            'hasta_adi' => trim(($r->hasta->ad ?? $r->ad ?? '').' '.($r->hasta->soyad ?? $r->soyad ?? '')),
            'hasta_telefon' => $r->hasta->telefon ?? $r->telefon ?? null,
            'doktor_id' => $r->doktor_id,
            'doktor_adi' => $r->doktor?->ad_soyad,
            'hizmet_id' => $r->hizmet_id,
            'hizmet' => $r->hizmet?->ad,
        ];
    }

    private function patientPayload(Hasta $h): array
    {
        return [
            'id' => $h->id,
            'ad' => $h->ad,
            'soyad' => $h->soyad,
            'ad_soyad' => trim(($h->ad ?? '').' '.($h->soyad ?? '')),
            'e_posta' => $h->e_posta,
            'telefon' => $h->telefon,
        ];
    }
}
