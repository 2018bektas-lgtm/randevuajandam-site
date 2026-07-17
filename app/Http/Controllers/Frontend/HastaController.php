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
use App\Services\AppointmentBookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
    public function kayitOl(HastaKayitRequest $request)
    {

        $hasta = Hasta::create([
            'ad' => $request->ad,
            'soyad' => $request->soyad,
            'e_posta' => $request->e_posta,
            'telefon' => $request->telefon,
            'sifre' => $request->sifre,
            'aktif_mi' => true,
        ]);

        Auth::guard('hasta')->login($hasta);

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

            return redirect()->intended(route('frontend.hasta.profil'));
        }

        RateLimiter::hit($throttleKey, 300);

        return redirect()->back()
            ->withInput($request->only('e_posta'))
            ->withErrors(['e_posta' => 'E-posta adresi veya şifre hatalı.']);
    }

    /**
     * Show profile dashboard.
     */
    public function profil()
    {
        /** @var Hasta $hasta */
        $hasta = Auth::guard('hasta')->user();

        return view('frontend.hasta.profil', compact('hasta'));
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
        $randevular = $hasta->randevular()->with('doktor', 'hizmet')->latest()->paginate(10);

        return view('frontend.hasta.randevular', compact('hasta', 'randevular'));
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
            ]);
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('hata', $e->getMessage());
        }

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
    public function randevuMisafirKaydet(Request $request, AppointmentBookingService $bookingService)
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
            'telefon' => ['required', 'string', 'max:30'],
            'e_posta' => ['nullable', 'email', 'max:255'],
            'not' => ['nullable', 'string', 'max:1000'],
            'gorusme_tipi' => ['nullable', 'in:yuz_yuze,online'],
            'kvkk_onay' => ['accepted'],
            'recaptcha_token' => ['nullable', 'string'],
        ], [
            'kvkk_onay.accepted' => 'KVKK onayını işaretlemelisiniz.',
            'ad.required' => 'Ad zorunludur.',
            'soyad.required' => 'Soyad zorunludur.',
            'telefon.required' => 'Telefon zorunludur.',
        ]);

        $doktor = Doktor::findOrFail($validated['doktor_id']);

        try {
            $randevu = $bookingService->createFromGuest($doktor, [
                'hizmet_id' => (int) $validated['hizmet_id'],
                'tarih' => $validated['tarih'],
                'saat' => $validated['saat'],
                'ad' => $validated['ad'],
                'soyad' => $validated['soyad'],
                'telefon' => $validated['telefon'],
                'e_posta' => $validated['e_posta'] ?? null,
                'not' => $validated['not'] ?? null,
                'gorusme_tipi' => $validated['gorusme_tipi'] ?? 'yuz_yuze',
            ]);
        } catch (InvalidArgumentException $e) {
            RateLimiter::hit($throttleKey, 300);

            return redirect()->back()->withInput()->with('hata', $e->getMessage());
        }

        RateLimiter::hit($throttleKey, 300);

        $mesaj = $randevu->durum === 'onaylandi'
            ? 'Randevunuz oluşturuldu ve onaylandı.'
            : 'Randevu talebiniz alındı. Hekim onayından sonra bilgilendirileceksiniz.';

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

        $yorum = Yorum::create([
            'hasta_id' => $hasta->id,
            'doktor_id' => $randevu->doktor_id,
            'randevu_id' => $randevu->id,
            'puan' => $validated['puan'],
            'yorum' => $validated['yorum'],
            'onay_durumu' => 'beklemede',
        ]);

        try {
            $doktor = $randevu->doktor ?? Doktor::find($randevu->doktor_id);
            if ($doktor) {
                $doktor->notify(new \App\Notifications\YeniYorumBildirimi($yorum));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Yorum doktor bildirimi hatası: '.$e->getMessage());
        }

        return redirect()->back()->with('basarili', 'Yorumunuz başarıyla gönderildi. Onaylandıktan sonra yayınlanacaktır.');
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
