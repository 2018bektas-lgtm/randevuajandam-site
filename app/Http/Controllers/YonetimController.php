<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Doktor;
use App\Models\Hasta;
use App\Models\Klinik;
use App\Models\Paket;
use App\Models\Randevu;
use App\Models\SiteAyari;
use App\Models\Yonetici;
use App\Models\Yorum;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class YonetimController extends Controller
{
    /**
     * Show the login form.
     */
    public function girisFormu()
    {
        if (Auth::guard('yonetici')->check()) {
            return redirect()->route('yonetim.panel');
        }

        return view('yonetim.giris');
    }

    /**
     * Handle the login request with brute-force protection.
     */
    public function girisYap(Request $request)
    {
        $request->validate([
            'e_posta' => ['required', 'email'],
            'sifre' => ['required', 'string'],
        ], [
            'e_posta.required' => 'E-posta adresi zorunludur.',
            'e_posta.email' => 'Lütfen geçerli bir e-posta adresi girin.',
            'sifre.required' => 'Şifre zorunludur.',
        ]);

        $throttleKey = 'yonetim-giris:'.Str::lower($request->input('e_posta')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $saniye = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'e_posta' => "Çok fazla başarısız giriş denemesi. Lütfen {$saniye} saniye sonra tekrar deneyin.",
            ])->withInput($request->only('e_posta'));
        }

        $yonetici = Yonetici::where('e_posta', $request->e_posta)->first();

        if ($yonetici && ! $yonetici->aktif_mi) {
            return back()->withErrors([
                'e_posta' => 'Hesabınız askıya alınmıştır. Lütfen sistem yöneticisiyle iletişime geçin.',
            ])->withInput($request->only('e_posta'));
        }

        $credentials = [
            'e_posta' => $request->e_posta,
            'password' => $request->sifre,
        ];

        if (Auth::guard('yonetici')->attempt($credentials, $request->boolean('hatirla'))) {
            RateLimiter::clear($throttleKey);

            /** @var Yonetici $user */
            $user = Auth::guard('yonetici')->user();
            if ($user->hasTwoFactorEnabled()) {
                $remember = $request->boolean('hatirla');
                Auth::guard('yonetici')->logout();
                \App\Http\Controllers\TwoFactorController::beginChallenge('yonetici', $user->id, $remember);

                return redirect()->route('two-factor.challenge');
            }

            $request->session()->regenerate();

            return redirect()->intended(route('yonetim.panel'));
        }

        RateLimiter::hit($throttleKey, 300);

        return back()->withErrors([
            'e_posta' => 'Girdiğiniz bilgiler kayıtlarımızla eşleşmiyor.',
        ])->withInput($request->only('e_posta'));
    }

    /**
     * Show the admin panel dashboard (live platform stats).
     */
    public function panel()
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();
        $bugun = Carbon::today();

        $stats = [
            'doktor_toplam' => Doktor::count(),
            'doktor_aktif' => Doktor::where('aktif_mi', true)->count(),
            'klinik_toplam' => Klinik::count(),
            'hasta_toplam' => Hasta::count(),
            'randevu_toplam' => Randevu::count(),
            'randevu_bugun' => Randevu::whereDate('tarih', $bugun)
                ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi'])
                ->count(),
            'randevu_beklemede' => Randevu::where('durum', 'beklemede')->count(),
            'yorum_beklemede' => Yorum::where('onay_durumu', 'beklemede')->count(),
            'uyelik_biten_7gun' => Doktor::whereNotNull('uyelik_bitis')
                ->whereBetween('uyelik_bitis', [$bugun, $bugun->copy()->addDays(7)])
                ->count(),
            'uyelik_suresi_dolmus' => Doktor::whereNotNull('uyelik_bitis')
                ->where('uyelik_bitis', '<', $bugun)
                ->where('aktif_mi', true)
                ->count(),
            'platform_gizli' => Doktor::where('platformda_gorunur', false)->count(),
            'blog_aktif' => Blog::where('aktif_mi', true)->count(),
            'paket_aktif' => Paket::where('aktif_mi', true)->count(),
        ];

        $sonRandevular = Randevu::with(['doktor', 'hizmet', 'hasta'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $sonDoktorlar = Doktor::with('paket')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('yonetim.panel', compact('yonetici', 'stats', 'sonRandevular', 'sonDoktorlar'));
    }

    /**
     * Platform-wide appointments (read-only admin view).
     */
    public function randevular(Request $request)
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();

        $query = Randevu::with(['doktor', 'hizmet', 'hasta'])->orderByDesc('tarih')->orderByDesc('saat');

        if ($request->filled('durum')) {
            $query->where('durum', $request->input('durum'));
        }
        if ($request->filled('tarih')) {
            $query->whereDate('tarih', $request->input('tarih'));
        }
        if ($request->filled('arama')) {
            $a = $request->input('arama');
            $query->where(function ($q) use ($a) {
                $q->where('ad', 'like', "%{$a}%")
                    ->orWhere('soyad', 'like', "%{$a}%")
                    ->orWhere('telefon', 'like', "%{$a}%")
                    ->orWhere('e_posta', 'like', "%{$a}%")
                    ->orWhereHas('doktor', fn ($dq) => $dq->where('ad_soyad', 'like', "%{$a}%"));
            });
        }

        $randevular = $query->paginate(25)->withQueryString();

        $ozet = [
            'beklemede' => Randevu::where('durum', 'beklemede')->count(),
            'onaylandi' => Randevu::where('durum', 'onaylandi')->count(),
            'bugun' => Randevu::whereDate('tarih', today())->count(),
        ];

        return view('yonetim.randevular.index', compact('yonetici', 'randevular', 'ozet'));
    }

    /**
     * Platform-wide patients.
     */
    public function hastalar(Request $request)
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();

        $query = Hasta::query()->withCount('randevular')->orderByDesc('id');

        if ($request->filled('arama')) {
            $a = $request->input('arama');
            $query->where(function ($q) use ($a) {
                $q->where('ad', 'like', "%{$a}%")
                    ->orWhere('soyad', 'like', "%{$a}%")
                    ->orWhere('e_posta', 'like', "%{$a}%")
                    ->orWhere('telefon', 'like', "%{$a}%");
            });
        }
        if ($request->filled('aktif')) {
            $query->where('aktif_mi', $request->input('aktif') === '1');
        }

        $hastalar = $query->paginate(25)->withQueryString();

        return view('yonetim.hastalar.index', compact('yonetici', 'hastalar'));
    }

    /**
     * Subscriptions / membership overview for doctors.
     */
    public function uyelikler(Request $request)
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();
        $bugun = Carbon::today();

        $query = Doktor::with('paket')->orderByRaw('uyelik_bitis is null')->orderBy('uyelik_bitis');

        if ($request->input('filtre') === 'biten') {
            $query->whereNotNull('uyelik_bitis')->where('uyelik_bitis', '<', $bugun);
        } elseif ($request->input('filtre') === 'yakinda') {
            $query->whereNotNull('uyelik_bitis')
                ->whereBetween('uyelik_bitis', [$bugun, $bugun->copy()->addDays(14)]);
        } elseif ($request->input('filtre') === 'gizli') {
            $query->where('platformda_gorunur', false);
        } elseif ($request->input('filtre') === 'aktif') {
            $query->where('aktif_mi', true)
                ->where(function ($q) use ($bugun) {
                    $q->whereNull('uyelik_bitis')->orWhere('uyelik_bitis', '>=', $bugun);
                });
        }

        if ($request->filled('arama')) {
            $a = $request->input('arama');
            $query->where(function ($q) use ($a) {
                $q->where('ad_soyad', 'like', "%{$a}%")
                    ->orWhere('e_posta', 'like', "%{$a}%");
            });
        }

        $doktorlar = $query->paginate(25)->withQueryString();

        $ozet = [
            'aktif_uyelik' => Doktor::where('aktif_mi', true)->where(function ($q) use ($bugun) {
                $q->whereNull('uyelik_bitis')->orWhere('uyelik_bitis', '>=', $bugun);
            })->count(),
            'suresi_dolmus' => Doktor::whereNotNull('uyelik_bitis')->where('uyelik_bitis', '<', $bugun)->count(),
            'yakinda' => Doktor::whereNotNull('uyelik_bitis')
                ->whereBetween('uyelik_bitis', [$bugun, $bugun->copy()->addDays(14)])
                ->count(),
            'gizli_vitrin' => Doktor::where('platformda_gorunur', false)->count(),
        ];

        return view('yonetim.uyelikler.index', compact('yonetici', 'doktorlar', 'ozet'));
    }

    /**
     * Log the admin out.
     */
    public function cikisYap(Request $request)
    {
        Auth::guard('yonetici')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('yonetim.giris');
    }

    /**
     * Show the SEO Settings form.
     */
    public function seoFormu()
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();
        $seo = SiteAyari::first() ?? SiteAyari::create([
            'meta_baslik' => config('app.name'),
        ]);

        return view('yonetim.seo', compact('yonetici', 'seo'));
    }

    /**
     * Update the SEO Settings.
     */
    public function seoGuncelle(Request $request)
    {
        $request->validate([
            'meta_baslik' => ['nullable', 'string', 'max:255'],
            'meta_aciklama' => ['nullable', 'string'],
            'meta_anahtar_kelimeler' => ['nullable', 'string'],
            'meta_yazar' => ['nullable', 'string', 'max:255'],
            'gtm_container_id' => ['nullable', 'string', 'max:40', 'regex:/^GTM-[A-Z0-9]+$/i'],
            'ga4_measurement_id' => ['nullable', 'string', 'max:40', 'regex:/^G-[A-Z0-9]+$/i'],
            'meta_pixel_id' => ['nullable', 'string', 'max:40', 'regex:/^[0-9]+$/'],
            'google_ads_id' => ['nullable', 'string', 'max:40', 'regex:/^AW-[0-9]+$/i'],
            'recaptcha_site_key' => ['nullable', 'string', 'max:100'],
            'recaptcha_secret_key' => ['nullable', 'string', 'max:100'],
            'recaptcha_enabled' => ['nullable', 'boolean'],
        ], [
            'meta_baslik.max' => 'Meta başlık en fazla 255 karakter olabilir.',
            'meta_yazar.max' => 'Meta yazar en fazla 255 karakter olabilir.',
            'gtm_container_id.regex' => 'GTM kodu GTM-XXXX formatında olmalıdır.',
            'ga4_measurement_id.regex' => 'GA4 kodu G-XXXX formatında olmalıdır.',
            'meta_pixel_id.regex' => 'Meta Pixel yalnızca rakam olmalıdır.',
            'google_ads_id.regex' => 'Google Ads kimliği AW-XXXXXXXXXX formatında olmalıdır.',
        ]);

        $seo = SiteAyari::first() ?? SiteAyari::create([
            'meta_baslik' => config('app.name'),
        ]);

        $data = $request->only([
            'meta_baslik',
            'meta_aciklama',
            'meta_anahtar_kelimeler',
            'meta_yazar',
            'gtm_container_id',
            'ga4_measurement_id',
            'meta_pixel_id',
            'google_ads_id',
            'recaptcha_site_key',
            'recaptcha_secret_key',
        ]);
        $data['recaptcha_enabled'] = $request->boolean('recaptcha_enabled');
        foreach (['gtm_container_id', 'ga4_measurement_id', 'meta_pixel_id', 'google_ads_id'] as $k) {
            if (isset($data[$k]) && is_string($data[$k])) {
                $data[$k] = trim($data[$k]) === '' ? null : trim($data[$k]);
            }
        }
        if (! empty($data['gtm_container_id'])) {
            $data['gtm_container_id'] = strtoupper($data['gtm_container_id']);
        }
        if (! empty($data['ga4_measurement_id'])) {
            $data['ga4_measurement_id'] = strtoupper($data['ga4_measurement_id']);
        }
        if (! empty($data['google_ads_id'])) {
            $data['google_ads_id'] = strtoupper($data['google_ads_id']);
        }
        foreach (['recaptcha_site_key', 'recaptcha_secret_key'] as $k) {
            if (isset($data[$k])) {
                $data[$k] = trim((string) $data[$k]) ?: null;
            }
        }

        $seo->update($data);

        return redirect()->back()->with('basarili', 'SEO, analitik ve reklam kodları güncellendi.');
    }

    public function odemeAyarlariFormu()
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();
        $ayarlar = SiteAyari::first() ?? SiteAyari::create(['meta_baslik' => config('app.name')]);

        return view('yonetim.odeme-ayarlari', compact('yonetici', 'ayarlar'));
    }

    public function odemeAyarlariGuncelle(Request $request)
    {
        $request->validate([
            'iyzico_api_key' => ['nullable', 'string', 'max:500'],
            'iyzico_secret_key' => ['nullable', 'string', 'max:500'],
            'iyzico_base_url' => ['nullable', 'url', 'max:255'],
            'banka_adi' => ['nullable', 'string', 'max:150'],
            'banka_hesap_sahibi' => ['nullable', 'string', 'max:150'],
            'banka_iban' => ['nullable', 'string', 'max:34', 'regex:/^TR[0-9]{24}$/'],
            'banka_aciklama' => ['nullable', 'string', 'max:2000'],
        ], [
            'banka_iban.regex' => 'IBAN, boşluksuz TR ile başlayan 26 karakter olmalıdır.',
        ]);

        $ayarlar = SiteAyari::first() ?? SiteAyari::create(['meta_baslik' => config('app.name')]);
        $data = $request->only(['iyzico_base_url', 'banka_adi', 'banka_hesap_sahibi', 'banka_aciklama']);
        $data['iyzico_base_url'] = trim((string) ($data['iyzico_base_url'] ?? '')) ?: null;
        $data['banka_adi'] = trim((string) ($data['banka_adi'] ?? '')) ?: null;
        $data['banka_hesap_sahibi'] = trim((string) ($data['banka_hesap_sahibi'] ?? '')) ?: null;
        $data['banka_aciklama'] = trim((string) ($data['banka_aciklama'] ?? '')) ?: null;
        $data['banka_iban'] = strtoupper(preg_replace('/\s+/', '', (string) $request->input('banka_iban')) ?: '') ?: null;

        // Empty credential inputs retain existing encrypted values.
        foreach (['iyzico_api_key', 'iyzico_secret_key'] as $field) {
            $value = trim((string) $request->input($field));
            if ($value !== '') {
                $data[$field] = $value;
            }
        }

        $ayarlar->update($data);

        return back()->with('basarili', 'Ödeme ayarları güncellendi.');
    }
}
