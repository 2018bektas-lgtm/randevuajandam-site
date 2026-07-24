<?php

namespace App\Http\Controllers\Frontend;

use App\Events\RandevuDurumuDegisti;
use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\HastaKayitRequest;
use App\Http\Requests\Frontend\RandevuKaydetRequest;
use App\Http\Requests\Frontend\YorumKaydetRequest;
use App\Models\Yorum;
use App\Models\Doktor;
use App\Models\Hasta;
use App\Models\Randevu;
use App\Rules\TurkishMobilePhone;
use App\Services\AppointmentBookingService;
use App\Services\PhoneOtpService;
use App\Support\MetaPixel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use InvalidArgumentException;

class HastaController extends Controller
{
    /**
     * Show registration form.
     */
    public function kayitFormu()
    {
        return view('frontend.hasta.kayit');
    }

    /**
     * Handle registration request.
     */
    public function kayitOl(HastaKayitRequest $request, PhoneOtpService $otp)
    {
        $telefon = TurkishMobilePhone::normalize($request->input('telefon'));

        try {
            $otp->assertVerifiedIfRequired($telefon, 'kayit');
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->withErrors(['telefon' => $e->getMessage()]);
        }

        $hasta = Hasta::create([
            'ad' => $request->ad,
            'soyad' => $request->soyad,
            'e_posta' => $request->e_posta,
            'telefon' => $telefon,
            'sifre' => $request->sifre,
            'aktif_mi' => true,
        ]);

        $otp->clearVerified($telefon, 'kayit');

        Auth::guard('hasta')->login($hasta);

        MetaPixel::queueOnce(
            'complete_reg_hasta_'.$hasta->id,
            'CompleteRegistration',
            MetaPixel::content(
                'Hasta kaydı',
                'product',
                'hasta-'.$hasta->id,
                null,
                'TRY',
                ['status' => true, 'content_category' => 'hasta']
            )
        );

        return redirect()->route('frontend.hasta.profil')->with('basarili', 'Üyeliğiniz başarıyla oluşturuldu ve giriş yapıldı.');
    }

    /**
     * Show login form.
     */
    public function girisFormu()
    {
        return view('frontend.hasta.giris');
    }

    /**
     * Handle login request with brute-force protection.
     */
    public function girisYap(Request $request)
    {
        $request->validate([
            'e_posta' => ['required', 'email'],
            'sifre' => ['required', 'string'],
        ], [
            'e_posta.required' => 'E-posta adresi zorunludur.',
            'e_posta.email' => 'Geçerli bir e-posta adresi giriniz.',
            'sifre.required' => 'Şifre alanı zorunludur.',
        ]);

        $throttleKey = 'hasta-giris:'.Str::lower($request->input('e_posta')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $saniye = RateLimiter::availableIn($throttleKey);

            return redirect()->back()
                ->withInput($request->only('e_posta'))
                ->withErrors(['e_posta' => "Çok fazla başarısız giriş denemesi. Lütfen {$saniye} saniye sonra tekrar deneyin."]);
        }

        // Check if account is active BEFORE attempting login
        $hasta = Hasta::where('e_posta', $request->e_posta)->first();

        if ($hasta && ! $hasta->aktif_mi) {
            return redirect()->back()
                ->withInput($request->only('e_posta'))
                ->withErrors(['e_posta' => 'Hesabınız pasif durumdadır.']);
        }

        $credentials = [
            'e_posta' => $request->e_posta,
            'password' => $request->sifre,
        ];

        if (Auth::guard('hasta')->attempt($credentials, $request->has('remember'))) {
            RateLimiter::clear($throttleKey);

            // Hesap silme talebi varsa otomatik iptal et
            $loggedIn = Auth::guard('hasta')->user();
            if ($loggedIn->silme_talep_at) {
                $loggedIn->update(['silme_talep_at' => null]);
            }

            return redirect()->intended(route('frontend.hasta.dashboard'));
        }

        RateLimiter::hit($throttleKey, 300);

        return redirect()->back()
            ->withInput($request->only('e_posta'))
            ->withErrors(['e_posta' => 'E-posta adresi veya şifre hatalı.']);
    }

    /**
     * Hasta paneli dashboard.
     */
    public function dashboard()
    {
        /** @var Hasta $hasta */
        $hasta = Auth::guard('hasta')->user();

        $yaklasanRandevular = $hasta->randevular()
            ->with('doktor', 'hizmet')
            ->whereIn('durum', ['beklemede', 'onaylandi'])
            ->where('tarih', '>=', now()->toDateString())
            ->orderBy('tarih')->orderBy('saat')
            ->take(3)->get();

        $toplamRandevuSayisi = $hasta->randevular()->count();
        $yaklasanSayi = $hasta->randevular()
            ->whereIn('durum', ['beklemede', 'onaylandi'])
            ->where('tarih', '>=', now()->toDateString())
            ->count();
        $tamamlananSayisi = $hasta->randevular()->where('durum', 'tamamlandi')->count();
        $bekleyenYorumSayisi = $hasta->randevular()
            ->where('durum', 'tamamlandi')
            ->whereDoesntHave('yorum')
            ->count();

        return view('frontend.hasta.dashboard', compact(
            'hasta', 'yaklasanRandevular',
            'toplamRandevuSayisi', 'yaklasanSayi', 'tamamlananSayisi', 'bekleyenYorumSayisi'
        ));
    }

    /**
     * Show profile dashboard.
     */
    public function profil()
    {
        /** @var Hasta $hasta */
        $hasta = Auth::guard('hasta')->user();

        $bekleyenYorumSayisi = $hasta->randevular()
            ->where('durum', 'tamamlandi')
            ->whereDoesntHave('yorum')
            ->count();

        return view('frontend.hasta.profil', compact('hasta', 'bekleyenYorumSayisi'));
    }

    /**
     * Update profile details.
     */
    public function profilGuncelle(Request $request)
    {
        /** @var Hasta $hasta */
        $hasta = Auth::guard('hasta')->user();

        $request->validate([
            'ad' => 'required|string|max:255',
            'soyad' => 'required|string|max:255',
            'telefon' => ['required', 'string', 'regex:/^0\s\(5[0-9]{2}\)\s[0-9]{3}\s[0-9]{2}\s[0-9]{2}$/'],
            'sifre' => 'nullable|string|min:8|confirmed',
        ], [
            'ad.required' => 'Ad alanı zorunludur.',
            'soyad.required' => 'Soyad alanı zorunludur.',
            'telefon.required' => 'Telefon numarası zorunludur.',
            'telefon.regex' => 'Telefon numarası 0 (5xx) xxx xx xx formatında olmalıdır.',
            'sifre.min' => 'Şifre en az 6 karakter olmalıdır.',
            'sifre.confirmed' => 'Şifreler uyuşmuyor.',
        ]);

        $data = [
            'ad' => $request->ad,
            'soyad' => $request->soyad,
            'telefon' => $request->telefon,
        ];

        if ($request->filled('sifre')) {
            $data['sifre'] = $request->sifre;
        }

        $hasta->update($data);

        return redirect()->back()->with('basarili', 'Profil bilgileriniz başarıyla güncellendi.');
    }

    /**
     * Show appointments list.
     */
    public function randevular()
    {
        /** @var Hasta $hasta */
        $hasta = Auth::guard('hasta')->user();
        $randevular = $hasta->randevular()->with('doktor', 'hizmet', 'yorum')->latest()->paginate(10);
        $bekleyenYorumSayisi = $hasta->randevular()
            ->where('durum', 'tamamlandi')
            ->whereDoesntHave('yorum')
            ->count();

        return view('frontend.hasta.randevular', compact('hasta', 'randevular', 'bekleyenYorumSayisi'));
    }

    /**
     * Cancel an appointment.
     */
    public function randevuIptal(int $id)
    {
        /** @var Hasta $hasta */
        $hasta = Auth::guard('hasta')->user();
        $randevu = $hasta->randevular()->findOrFail($id);

        if (! in_array($randevu->durum, ['beklemede', 'onaylandi'])) {
            return redirect()->back()->with('hata', 'Bu randevu iptal edilemez durumdadır.');
        }

        $doktor = $randevu->doktor;
        $ayarlar = $doktor ? $doktor->randevuAyari : null;

        if ($ayarlar) {
            if (! $ayarlar->randevu_iptal_aktif_mi) {
                return redirect()->back()->with('hata', 'Bu hekim için online randevu iptali kapatılmıştır.');
            }

            if ($ayarlar->iptal_saat_limiti > 0) {
                $tarihStr = $randevu->tarih instanceof \DateTimeInterface ? $randevu->tarih->format('Y-m-d') : Carbon::parse($randevu->tarih)->toDateString();
                $randevuZamani = Carbon::parse($tarihStr.' '.$randevu->saat);
                $limitZamani = now()->addHours($ayarlar->iptal_saat_limiti);
                if ($randevuZamani->lt($limitZamani)) {
                    return redirect()->back()->with('hata', 'Randevu başlangıcına '.$ayarlar->iptal_saat_limiti.' saatten az süre kaldığı için iptal edemezsiniz.');
                }
            }
        }

        $eskiDurum = $randevu->durum;
        $randevu->update(['durum' => 'iptal']);

        RandevuDurumuDegisti::dispatch($randevu, $eskiDurum, 'iptal');

        return redirect()->back()->with('basarili', 'Randevunuz başarıyla iptal edildi.');
    }

    /**
     * Handle appointment booking request (logged-in patient).
     */
    public function randevuKaydet(RandevuKaydetRequest $request, AppointmentBookingService $bookingService)
    {
        /** @var Hasta $hasta */
        $hasta = Auth::guard('hasta')->user();
        $doktor = Doktor::findOrFail($request->doktor_id);

        $gorusmeTipi = $request->input('gorusme_tipi', 'yuz_yuze');
        $ayarlar = $doktor->randevuAyari;
        if ($gorusmeTipi === 'online' && $ayarlar && ! $ayarlar->online_randevu_aktif) {
            return redirect()->back()->withInput()->with('hata', 'Bu hekim için online randevu şu an kapalıdır.');
        }
        if ($gorusmeTipi === 'yuz_yuze' && $ayarlar && ! $ayarlar->yuzyuze_randevu_aktif) {
            return redirect()->back()->withInput()->with('hata', 'Bu hekim için yüz yüze randevu şu an kapalıdır.');
        }

        try {
            $bookingService->assertPackageAppointmentLimit($doktor);
            $onayTipi = $bookingService->resolveDefaultStatus($doktor);

            $randevu = $bookingService->create([
                'doktor' => $doktor,
                'hasta' => $hasta,
                'hizmet_id' => (int) $request->hizmet_id,
                'tarih' => $request->tarih,
                'saat' => $request->saat,
                'not' => $request->not,
                'durum' => $onayTipi,
                'gorusme_tipi' => $gorusmeTipi,
            ]);
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('hata', $e->getMessage());
        }

        MetaPixel::queueOnce(
            'schedule_randevu_'.$randevu->id,
            'Schedule',
            MetaPixel::content(
                'Randevu',
                'product',
                'randevu-'.$randevu->id,
                null,
                'TRY',
                [
                    'content_category' => 'appointment',
                    'status' => $randevu->durum,
                ]
            )
        );

        return redirect()->route('frontend.hasta.randevular')->with(
            'basarili',
            $randevu->durum === 'onaylandi'
                ? 'Randevunuz başarıyla oluşturuldu ve onaylandı!'
                : 'Randevu talebiniz başarıyla oluşturuldu! Hekim onayından sonra bilgilendirileceksiniz.'
        );
    }

    /**
     * Guest booking from main platform (no login).
     */
    public function randevuMisafirKaydet(Request $request, AppointmentBookingService $bookingService, PhoneOtpService $otp)
    {
        $hp = config('randevu.honeypot_field', 'website_url');
        if ($request->filled($hp)) {
            return redirect()->back()->with('hata', 'Geçersiz istek.');
        }

        $throttleKey = 'misafir-randevu:'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            return redirect()->back()->withInput()->with('hata', 'Çok fazla istek. Lütfen biraz sonra tekrar deneyin.');
        }

        $captcha = app(\App\Services\RecaptchaService::class)->verify(
            $request->input('recaptcha_token'),
            'randevu',
            $request->ip()
        );
        if (! ($captcha['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('hata', $captcha['message'] ?? 'Güvenlik doğrulaması başarısız.');
        }

        $validated = $request->validate([
            'doktor_id' => ['required', 'exists:doktorlar,id'],
            'hizmet_id' => ['required', 'exists:hizmetler,id'],
            'tarih' => ['required', 'date', 'after_or_equal:today'],
            'saat' => ['required', 'date_format:H:i'],
            'ad' => ['required', 'string', 'max:100'],
            'soyad' => ['required', 'string', 'max:100'],
            'telefon' => ['required', 'string', new TurkishMobilePhone],
            'e_posta' => ['required', 'email', 'max:255'],
            'not' => ['nullable', 'string', 'max:1000', new \App\Rules\NoProfanity],
            'gorusme_tipi' => ['nullable', 'in:yuz_yuze,online'],
            'kvkk_onay' => ['accepted'],
            'recaptcha_token' => ['nullable', 'string'],
        ], [
            'kvkk_onay.accepted' => 'KVKK onayını işaretlemelisiniz.',
            'ad.required' => 'Ad zorunludur.',
            'soyad.required' => 'Soyad zorunludur.',
            'telefon.required' => 'Telefon zorunludur.',
            'e_posta.required' => 'E-posta adresi zorunludur.',
            'e_posta.email' => 'Geçerli bir e-posta adresi giriniz.',
        ]);

        $telefon = TurkishMobilePhone::normalize($validated['telefon']);
        $doktor = Doktor::findOrFail($validated['doktor_id']);

        $gorusmeTipi = $validated['gorusme_tipi'] ?? 'yuz_yuze';
        $ayarlar = $doktor->randevuAyari;
        if ($gorusmeTipi === 'online' && $ayarlar && ! $ayarlar->online_randevu_aktif) {
            return redirect()->back()->withInput()->with('hata', 'Bu hekim için online randevu şu an kapalıdır.');
        }
        if ($gorusmeTipi === 'yuz_yuze' && $ayarlar && ! $ayarlar->yuzyuze_randevu_aktif) {
            return redirect()->back()->withInput()->with('hata', 'Bu hekim için yüz yüze randevu şu an kapalıdır.');
        }

        try {
            $otp->assertVerifiedIfRequired($telefon, 'randevu', (int) $doktor->id);

            $randevu = $bookingService->createFromGuest($doktor, [
                'hizmet_id' => (int) $validated['hizmet_id'],
                'tarih' => $validated['tarih'],
                'saat' => $validated['saat'],
                'ad' => $validated['ad'],
                'soyad' => $validated['soyad'],
                'telefon' => $telefon,
                'e_posta' => $validated['e_posta'],
                'not' => $validated['not'] ?? null,
                'gorusme_tipi' => $gorusmeTipi,
            ]);
        } catch (InvalidArgumentException $e) {
            RateLimiter::hit($throttleKey, 300);

            return redirect()->back()->withInput()->with('hata', $e->getMessage());
        }

        $otp->clearVerified($telefon, 'randevu', (int) $doktor->id);
        RateLimiter::hit($throttleKey, 300);

        $mesaj = $randevu->durum === 'onaylandi'
            ? 'Randevunuz oluşturuldu ve onaylandı.'
            : 'Randevu talebiniz alındı. Hekim onayından sonra bilgilendirileceksiniz.';

        MetaPixel::queueOnce(
            'schedule_randevu_'.$randevu->id,
            'Schedule',
            MetaPixel::content(
                'Misafir randevu',
                'product',
                'randevu-'.$randevu->id,
                null,
                'TRY',
                [
                    'content_category' => 'appointment',
                    'status' => $randevu->durum,
                ]
            )
        );

        return redirect()
            ->route('frontend.randevu.yonet', $randevu->yonetim_token)
            ->with('basarili', $mesaj);
    }

    /**
     * Store a review for a completed appointment.
     */
    public function yorumKaydet(YorumKaydetRequest $request)
    {
        /** @var Hasta $hasta */
        $hasta = Auth::guard('hasta')->user();
        $validated = $request->validated();

        $randevu = $hasta->randevular()->where('id', $validated['randevu_id'])->first();

        if (! $randevu) {
            return redirect()->back()->with('hata', 'Bu randevu size ait değil.');
        }

        if ($randevu->durum !== 'tamamlandi') {
            return redirect()->back()->with('hata', 'Sadece tamamlanmış randevulara yorum yapabilirsiniz.');
        }

        $mevcutYorum = Yorum::where('hasta_id', $hasta->id)
            ->where('randevu_id', $randevu->id)
            ->exists();

        if ($mevcutYorum) {
            return redirect()->back()->with('hata', 'Bu randevu için zaten yorum yapmışsınız.');
        }

        Yorum::create([
            'hasta_id' => $hasta->id,
            'doktor_id' => $randevu->doktor_id,
            'randevu_id' => $randevu->id,
            'puan' => $validated['puan'],
            'yorum' => $validated['yorum'],
            'onay_durumu' => 'beklemede',
        ]);

        // Hekime bildirim yok: yorumlar yalnızca platform yönetimi tarafından denetlenir (adil moderasyon).

        return redirect()->back()->with('basarili', 'Yorumunuz alındı. Platform yönetimi onayladıktan sonra herkese açık profilde yayınlanacaktır.');
    }

    /**
     * Download iCal file for an appointment.
     */
    public function randevuIcal(int $id): Response
    {
        /** @var Hasta $hasta */
        $hasta = Auth::guard('hasta')->user();
        $randevu = $hasta->randevular()->with('doktor', 'hizmet')->findOrFail($id);

        if ($randevu->durum === 'iptal') {
            abort(410);
        }

        $periyot = $randevu->doktor?->randevuAyari?->randevu_periyodu
            ?? $randevu->hizmet?->sure
            ?? 30;
        if ($periyot < 5) {
            $periyot = 30;
        }

        $tarih = $randevu->tarih instanceof \DateTimeInterface
            ? $randevu->tarih->format('Y-m-d')
            : Carbon::parse($randevu->tarih)->toDateString();
        $saat = strlen($randevu->saat) === 5 ? $randevu->saat.':00' : $randevu->saat;

        $start = Carbon::parse($tarih.' '.$saat);
        $end = $start->copy()->addMinutes($periyot);

        $doktorAdi = $randevu->doktor
            ? trim(($randevu->doktor->unvan ? $randevu->doktor->unvan.' ' : '').$randevu->doktor->ad_soyad)
            : 'Hekim';
        $hizmetAdi = $randevu->hizmet?->ad ?? 'Randevu';

        $ical = "BEGIN:VCALENDAR\r\n"
            ."VERSION:2.0\r\n"
            ."CALSCALE:GREGORIAN\r\n"
            ."METHOD:PUBLISH\r\n"
            ."BEGIN:VEVENT\r\n"
            ."UID:randevu-{$randevu->id}@randevuajandam\r\n"
            ."DTSTAMP:".now()->utc()->format('Ymd\THis\Z')."\r\n"
            ."DTSTART:".$start->format('Ymd\THis')."\r\n"
            ."DTEND:".$end->format('Ymd\THis')."\r\n"
            ."SUMMARY:".$this->icalEscape($hizmetAdi.' - '.$doktorAdi)."\r\n"
            ."DESCRIPTION:".$this->icalEscape("Hekim: {$doktorAdi}\nHizmet: {$hizmetAdi}")."\r\n"
            ."END:VEVENT\r\n"
            ."END:VCALENDAR\r\n";

        return response($ical, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="randevu-'.$start->format('Y-m-d-Hi').'.ics"',
        ]);
    }

    /**
     * Show reschedule form.
     */
    public function randevuYenidenPlanlaFormu(int $id)
    {
        /** @var Hasta $hasta */
        $hasta = Auth::guard('hasta')->user();
        $randevu = $hasta->randevular()->with('doktor', 'hizmet')->findOrFail($id);

        if (! in_array($randevu->durum, ['beklemede', 'onaylandi'])) {
            return redirect()->route('frontend.hasta.randevular')->with('hata', 'Bu randevu yeniden planlanamaz.');
        }

        return view('frontend.hasta.yeniden-planla', compact('hasta', 'randevu'));
    }

    /**
     * Process reschedule request.
     */
    public function randevuYenidenPlanla(Request $request, int $id, AppointmentBookingService $bookingService)
    {
        /** @var Hasta $hasta */
        $hasta = Auth::guard('hasta')->user();
        $randevu = $hasta->randevular()->with('doktor', 'hizmet')->findOrFail($id);

        if (! in_array($randevu->durum, ['beklemede', 'onaylandi'])) {
            return redirect()->route('frontend.hasta.randevular')->with('hata', 'Bu randevu yeniden planlanamaz.');
        }

        $request->validate([
            'tarih' => ['required', 'date', 'after_or_equal:today'],
            'saat' => ['required', 'regex:/^\d{2}:\d{2}$/'],
        ], [
            'tarih.required' => 'Tarih zorunludur.',
            'tarih.after_or_equal' => 'Geçmiş bir tarih seçilemez.',
            'saat.required' => 'Saat zorunludur.',
            'saat.regex' => 'Geçerli bir saat seçin.',
        ]);

        $doktor = $randevu->doktor;
        $ayarlar = $doktor?->randevuAyari;

        if ($ayarlar) {
            if (! $ayarlar->randevu_iptal_aktif_mi) {
                return back()->with('hata', 'Bu hekim için online randevu değişikliği kapatılmıştır.');
            }
            if ($ayarlar->iptal_saat_limiti > 0) {
                $tarihStr = $randevu->tarih instanceof \DateTimeInterface
                    ? $randevu->tarih->format('Y-m-d')
                    : Carbon::parse($randevu->tarih)->toDateString();
                $randevuZamani = Carbon::parse($tarihStr.' '.$randevu->saat);
                if ($randevuZamani->lt(now()->addHours($ayarlar->iptal_saat_limiti))) {
                    return back()->with('hata', 'Randevu başlangıcına '.$ayarlar->iptal_saat_limiti.' saatten az süre kaldığı için değişiklik yapamazsınız.');
                }
            }
        }

        try {
            $onayTipi = $bookingService->resolveDefaultStatus($doktor);
            $bookingService->create([
                'doktor' => $doktor,
                'hasta' => $hasta,
                'hizmet_id' => $randevu->hizmet_id,
                'tarih' => $request->tarih,
                'saat' => $request->saat,
                'not' => $randevu->not,
                'durum' => $onayTipi,
                'gorusme_tipi' => $randevu->gorusme_tipi ?? 'yuz_yuze',
            ]);
        } catch (InvalidArgumentException $e) {
            return back()->with('hata', $e->getMessage());
        }

        $eskiDurum = $randevu->durum;
        $randevu->update(['durum' => 'iptal']);
        RandevuDurumuDegisti::dispatch($randevu, $eskiDurum, 'iptal');

        return redirect()->route('frontend.hasta.randevular')
            ->with('basarili', 'Randevunuz yeniden planlandı. Eski randevunuz iptal edildi.');
    }

    /**
     * Update notification preferences.
     */
    public function bildirimTercihleriniGuncelle(Request $request)
    {
        /** @var Hasta $hasta */
        $hasta = Auth::guard('hasta')->user();

        $hasta->update([
            'bildirim_email' => $request->boolean('bildirim_email'),
            'bildirim_sms' => $request->boolean('bildirim_sms'),
        ]);

        return redirect()->back()->with('basarili', 'Bildirim tercihleriniz güncellendi.');
    }

    /**
     * Submit account deletion request (KVKK).
     */
    public function hesapSilTalep(Request $request)
    {
        /** @var Hasta $hasta */
        $hasta = Auth::guard('hasta')->user();

        $request->validate([
            'sifre' => ['required', 'string'],
        ], [
            'sifre.required' => 'Hesap silme için şifrenizi girmelisiniz.',
        ]);

        if (! Hash::check($request->sifre, $hasta->sifre)) {
            return back()->withErrors(['sifre_sil' => 'Şifre hatalı.']);
        }

        $hasta->update(['silme_talep_at' => now()]);
        Auth::guard('hasta')->logout();

        return redirect()->route('frontend.hasta.giris')
            ->with('basarili', 'Hesap silme talebiniz alındı. Verileriniz 30 gün içinde kalıcı olarak silinecektir. Bu süre içinde giriş yaparsanız talebiniz iptal olur.');
    }

    private function icalEscape(string $value): string
    {
        $value = str_replace(["\r\n", "\n", "\r"], '\n', $value);

        return addcslashes($value, ',;\\');
    }

    /**
     * Handle logout.
     */
    public function cikisYap()
    {
        Auth::guard('hasta')->logout();

        return redirect()->route('frontend.hasta.giris')->with('basarili', 'Başarıyla çıkış yaptınız.');
    }
}
