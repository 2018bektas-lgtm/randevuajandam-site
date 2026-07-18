<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brans;
use App\Models\Doktor;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Unvan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HekimController extends Controller
{
    /**
     * Show the doctor login form.
     */
    public function girisFormu()
    {
        return view('frontend.hekim.giris');
    }

    /**
     * Handle the doctor login request with brute-force protection.
     */
    public function girisYap(Request $request)
    {
        $request->validate([
            'e_posta' => ['required', 'email'],
            'sifre' => ['required', 'string'],
        ], [
            'e_posta.required' => 'E-posta adresi zorunludur.',
            'e_posta.email' => 'Lütfen geçerli bir e-posta adresi girin.',
            'sifre.required' => 'Şifre alanı zorunludur.',
        ]);

        $throttleKey = 'hekim-giris:'.Str::lower($request->input('e_posta')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $saniye = RateLimiter::availableIn($throttleKey);

            return redirect()->back()
                ->withInput($request->only('e_posta', 'remember'))
                ->withErrors(['e_posta' => "Çok fazla başarısız giriş denemesi. Lütfen {$saniye} saniye sonra tekrar deneyin."]);
        }

        // Check if account is active BEFORE attempting login
        $doktor = Doktor::where('e_posta', $request->e_posta)->first();

        if ($doktor && ! $doktor->aktif_mi) {
            return redirect()->back()
                ->withInput($request->only('e_posta', 'remember'))
                ->withErrors(['e_posta' => 'Hesabınız pasif durumdadır. Lütfen yönetici ile iletişime geçin.']);
        }

        $credentials = [
            'e_posta' => $request->e_posta,
            'password' => $request->sifre,
        ];

        if (Auth::guard('doktor')->attempt($credentials, $request->has('remember'))) {
            RateLimiter::clear($throttleKey);
            session()->forget('url.intended');

            /** @var Doktor $user */
            $user = Auth::guard('doktor')->user();
            if ($user->hasTwoFactorEnabled()) {
                $remember = $request->has('remember');
                Auth::guard('doktor')->logout();
                \App\Http\Controllers\TwoFactorController::beginChallenge('doktor', $user->id, $remember);

                return redirect()->route('two-factor.challenge');
            }

            return redirect()->route('hekim.panel');
        }

        RateLimiter::hit($throttleKey, 300);

        return redirect()->back()
            ->withInput($request->only('e_posta', 'remember'))
            ->withErrors(['e_posta' => 'E-posta adresi veya şifre hatalı.']);
    }

    /**
     * Handle doctor logout.
     */
    public function cikisYap()
    {
        Auth::guard('doktor')->logout();

        return redirect()->route('frontend.hekim.giris')->with('basarili', 'Başarıyla çıkış yaptınız.');
    }

    /**
     * Display the doctor dashboard.
     */
    public function panel()
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $doktor->load('il', 'ilce');

        $toplamRandevu = $doktor->randevular()->count();
        $kayitliHasta = $doktor->randevular()->distinct('hasta_id')->count('hasta_id');
        $bekleyenTalep = $doktor->randevular()->where('durum', 'beklemede')->count();
        $klinikDurumu = $doktor->randevuya_acik_mi;

        $davetiyeler = \App\Models\KlinikDavetiye::where('davet_edilen_eposta', $doktor->e_posta)
            ->where('durum', 'beklemede')
            ->where('son_kullanma_tarihi', '>', now())
            ->with('klinik')
            ->get();

        return view('hekim.panel', compact('doktor', 'toplamRandevu', 'kayitliHasta', 'bekleyenTalep', 'klinikDurumu', 'davetiyeler'));
    }

    /**
     * Display the public directory of individual doctors.
     */
    public function doktorlarListesi(Request $request, ?string $il_slug = null, ?string $ilce_slug = null, ?string $brans_slug = null)
    {
        // Header spotlight: ?brans=diyetisyen (slug) → uzmanlik filtresi
        if ($request->filled('brans') && ! $request->filled('uzmanlik')) {
            $bransKey = (string) $request->input('brans');
            $bransFromQuery = Brans::query()
                ->where(function ($q) use ($bransKey) {
                    $q->where('slug', $bransKey)->orWhere('ad', $bransKey);
                })
                ->first();
            if ($bransFromQuery) {
                $request->merge(['uzmanlik' => $bransFromQuery->ad]);
            }
        }

        if ($il_slug) {
            $ilModel = Il::where('slug', $il_slug)->firstOrFail();
            $request->merge(['il' => $ilModel->id]);
        }

        if ($ilce_slug) {
            $ilceModel = Ilce::where('il_id', $request->input('il'))->where('slug', $ilce_slug)->firstOrFail();
            $request->merge(['ilce' => $ilceModel->id]);
        }

        if ($brans_slug) {
            $bransModel = Brans::where('slug', $brans_slug)->firstOrFail();
            $request->merge(['uzmanlik' => $bransModel->ad]);
        }

        $query = Doktor::platformdaListelenen()
            ->where('tur', 'bireysel')
            ->with(['paket', 'branslar', 'il', 'ilce', 'randevuAyari', 'calismaSaatleri']);

        // Search filter
        if ($request->filled('arama')) {
            $arama = $request->input('arama');
            $query->where(function ($q) use ($arama) {
                $q->where('ad_soyad', 'like', "%{$arama}%")
                    ->orWhere('uzmanlik_alani', 'like', "%{$arama}%")
                    ->orWhereHas('branslar', function ($sq) use ($arama) {
                        $sq->where('ad', 'like', "%{$arama}%");
                    });
            });
        }

        // Specialty filter
        if ($request->filled('uzmanlik')) {
            $query->whereHas('branslar', function ($q) use ($request) {
                $q->where('ad', $request->input('uzmanlik'));
            });
        }

        // Title filter
        if ($request->filled('unvan')) {
            $query->where('unvan', $request->input('unvan'));
        }

        // City (il) filter
        if ($request->filled('il')) {
            $query->where('il_id', $request->input('il'));
        }

        // District (ilce) filter
        if ($request->filled('ilce')) {
            $query->where('ilce_id', $request->input('ilce'));
        }

        // Distance/Nearby geolocation filter
        if ($request->input('yakindaki') && $request->filled('user_lat') && $request->filled('user_lng')) {
            $userLat = (float) $request->input('user_lat');
            $userLng = (float) $request->input('user_lng');
            $radius = (float) $request->input('cap', 15); // Default 15km

            if (DB::getDriverName() === 'sqlite') {
                // Bounding box approximation for SQLite during tests
                // 1 degree of latitude is approx 111 km
                $latDelta = $radius / 111;
                // 1 degree of longitude is approx 86 km in Turkey
                $lngDelta = $radius / 86;

                $query->whereBetween('enlem', [$userLat - $latDelta, $userLat + $latDelta])
                    ->whereBetween('boylam', [$userLng - $lngDelta, $userLng + $lngDelta]);
            } else {
                // Haversine formula query for MySQL (production)
                $query->select('doktorlar.*')
                    ->selectRaw(
                        '(6371 * acos(cos(radians(?)) * cos(radians(enlem)) * cos(radians(boylam) - radians(?)) + sin(radians(?)) * sin(radians(enlem)))) AS mesafe',
                        [$userLat, $userLng, $userLat]
                    )
                    ->whereNotNull('enlem')
                    ->whereNotNull('boylam')
                    ->having('mesafe', '<=', $radius)
                    ->orderBy('mesafe');
            }
        }

        $klinikQuery = \App\Models\Klinik::where('aktif_mi', true)->with('il', 'ilce', 'doktorlar');

        if ($request->filled('arama')) {
            $arama = $request->input('arama');
            $klinikQuery->where(function ($q) use ($arama) {
                $q->where('ad', 'like', "%{$arama}%")
                    ->orWhere('aciklama', 'like', "%{$arama}%");
            });
        }

        if ($request->filled('il')) {
            $klinikQuery->where('il_id', $request->input('il'));
        }

        if ($request->filled('ilce')) {
            $klinikQuery->where('ilce_id', $request->input('ilce'));
        }

        if ($request->filled('uzmanlik')) {
            $uzmanlik = $request->input('uzmanlik');
            $klinikQuery->whereHas('doktorlar.branslar', function ($q) use ($uzmanlik) {
                $q->where('ad', $uzmanlik);
            });
        }

        $klinikler = $klinikQuery->get();
        $toplamKlinikSayisi = $klinikler->count();

        if ($request->input('sadece_klinik')) {
            $toplamDoktorSayisi = $query->count();
            $doktorlar = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);
        } else {
            $doktorlar = $query->paginate(12)->withQueryString();
            $toplamDoktorSayisi = $doktorlar->total();

            // En yakın müsait randevu (kartlarda gösterim)
            $slotService = app(\App\Services\SlotService::class);
            foreach ($doktorlar as $d) {
                $next = $slotService->findNextAvailable($d);
                $d->setAttribute('en_yakin_randevu', $next);
            }
        }

        // Get filter options from cache (refreshed every 24 hours)
        $uzmanliklar = Cache::remember('branslar_listesi', 86400, function () {
            return Brans::orderBy('ad')->pluck('ad')->all();
        });

        $unvanlar = Cache::remember('unvanlar_listesi', 86400, function () {
            return Doktor::platformdaListelenen()
                ->where('tur', 'bireysel')
                ->whereNotNull('unvan')
                ->distinct()
                ->pluck('unvan')
                ->all();
        });

        $iller = Cache::remember('iller_listesi', 86400, function () {
            return Il::orderBy('ad')->get();
        });

        if ($request->ajax()) {
            $html = view('frontend.hekimler.partials.doctor_cards', compact('doktorlar', 'klinikler'))->render();

            $mapClinics = $klinikler->filter(function ($k) {
                return $k->enlem && $k->boylam;
            })->map(function ($k) {
                return [
                    'ad_soyad' => $k->ad,
                    'klinik_adi' => 'Klinik',
                    'uzmanlik_alani' => $k->doktorlar->count() . ' Hekim',
                    'url' => route('frontend.klinik.profil', ['il_slug' => $k->il->slug ?? 'il', 'ilce_slug' => $k->ilce->slug ?? 'ilce', 'klinik_slug' => $k->slug]),
                    'enlem' => (float) $k->enlem,
                    'boylam' => (float) $k->boylam,
                    'profil_resmi' => $k->logo ? asset($k->logo) : null,
                    'kisa_ad' => mb_strtoupper(mb_substr($k->ad, 0, 2)),
                ];
            })->values();

            $mapDoctors = $doktorlar->filter(function ($d) {
                return $d->enlem && $d->boylam;
            })->map(function ($d) {
                return [
                    'ad_soyad' => ($d->unvan ? $d->unvan.' ' : '').$d->ad_soyad,
                    'klinik_adi' => $d->klinik_adi ?? 'Bireysel Muayenehane',
                    'uzmanlik_alani' => $d->uzmanlik_alani ?? 'Uzman Hekim',
                    'url' => $d->profil_url,
                    'enlem' => (float) $d->enlem,
                    'boylam' => (float) $d->boylam,
                    'profil_resmi' => $d->profil_resmi ? asset($d->profil_resmi) : null,
                    'kisa_ad' => (function () use ($d) {
                        $words = explode(' ', $d->ad_soyad);
                        $kisaAd = mb_strtoupper(mb_substr($words[0], 0, 1));
                        if (count($words) > 1) {
                            $kisaAd .= mb_strtoupper(mb_substr(end($words), 0, 1));
                        }

                        return $kisaAd;
                    })(),
                ];
            })->values();

            if ($request->input('sadece_klinik')) {
                $mapDoctors = $mapClinics;
            }

            return response()->json([
                'html' => $html,
                'total' => $request->input('sadece_klinik') ? $toplamKlinikSayisi : $toplamDoktorSayisi,
                'mapDoctors' => $mapDoctors,
                'toplamDoktorSayisi' => $toplamDoktorSayisi,
                'toplamKlinikSayisi' => $toplamKlinikSayisi,
            ]);
        }

        return view('frontend.hekimler.index', compact('doktorlar', 'uzmanliklar', 'unvanlar', 'iller', 'klinikler', 'toplamDoktorSayisi', 'toplamKlinikSayisi'));
    }

    /**
     * Public day slots for profile booking wizard (bos + dolu).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function publicSlots(Request $request, int $id, \App\Services\SlotService $slotService)
    {
        $doktor = Doktor::platformdaListelenen()->findOrFail($id);
        $request->validate(['tarih' => ['required', 'date_format:Y-m-d']]);

        $tarih = \Carbon\Carbon::parse($request->string('tarih')->toString())->startOfDay();
        $periyot = $slotService->getPeriyot($doktor);

        $randevular = $doktor->randevular()
            ->whereDate('tarih', $tarih->toDateString())
            ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi'])
            ->get();

        $izinler = method_exists($doktor, 'izinler')
            ? $doktor->izinler()->get()
            : collect();

        $gunluk = $slotService->generateGunlukSlotlar($doktor, $tarih, $randevular, $izinler, $periyot);

        $slots = collect($gunluk)
            ->filter(fn ($s) => is_array($s))
            ->map(function ($s) {
                $saat = substr((string) ($s['saat_string'] ?? $s['saat_baslangic'] ?? ''), 0, 5);
                $durum = (string) ($s['durum'] ?? 'bos');

                return [
                    'saat' => $saat,
                    'durum' => $durum,
                    'musait' => $durum === 'bos',
                    'etiket' => match ($durum) {
                        'bos' => 'Müsait',
                        'dolu' => 'Dolu',
                        'ogle' => 'Öğle',
                        'izin' => 'İzin',
                        default => $durum,
                    },
                ];
            })
            ->filter(fn ($s) => $s['saat'] !== '')
            // Öğle aralarını listede gösterme (sadece dolu + müsait + izin)
            ->filter(fn ($s) => $s['durum'] !== 'ogle')
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'tarih' => $tarih->toDateString(),
                'slots' => $slots,
                'kapali' => $slots->isEmpty(),
            ],
        ]);
    }

    /**
     * Header spotlight JSON arama: /doktorlar/arama?q=
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function spotlightArama(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $results = [];

        $branslar = Brans::query()
            ->where(function ($query) use ($q) {
                $query->where('ad', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%");
            })
            ->orderBy('ad')
            ->limit(5)
            ->get();

        foreach ($branslar as $b) {
            $results[] = [
                'name' => $b->ad,
                'subtitle' => 'Branş',
                'url' => route('frontend.hekimler', ['brans' => $b->slug]),
                'icon' => '🏥',
            ];
        }

        $doktorlar = Doktor::platformdaListelenen()
            ->where('tur', 'bireysel')
            ->with(['branslar', 'il', 'ilce'])
            ->where(function ($query) use ($q) {
                $query->where('ad_soyad', 'like', "%{$q}%")
                    ->orWhere('uzmanlik_alani', 'like', "%{$q}%")
                    ->orWhereHas('branslar', function ($sq) use ($q) {
                        $sq->where('ad', 'like', "%{$q}%");
                    });
            })
            ->orderBy('ad_soyad')
            ->limit(8)
            ->get();

        foreach ($doktorlar as $d) {
            $bransAd = $d->branslar->first()?->ad ?? ($d->uzmanlik_alani ?: 'Hekim');
            $results[] = [
                'name' => trim(($d->unvan ? $d->unvan.' ' : '').$d->ad_soyad),
                'subtitle' => $bransAd.($d->il?->ad ? ' · '.$d->il->ad : ''),
                'url' => $d->profil_url ?? route('frontend.hekimler', ['arama' => $d->ad_soyad]),
                'icon' => '👨‍⚕️',
            ];
        }

        $klinikler = \App\Models\Klinik::query()
            ->where('aktif_mi', true)
            ->with(['il', 'ilce'])
            ->where(function ($query) use ($q) {
                $query->where('ad', 'like', "%{$q}%")
                    ->orWhere('aciklama', 'like', "%{$q}%");
            })
            ->orderBy('ad')
            ->limit(5)
            ->get();

        foreach ($klinikler as $k) {
            $results[] = [
                'name' => $k->ad,
                'subtitle' => 'Klinik'.($k->il?->ad ? ' · '.$k->il->ad : ''),
                'url' => route('frontend.klinik.profil', [
                    'il_slug' => $k->il->slug ?? 'il',
                    'ilce_slug' => $k->ilce->slug ?? 'ilce',
                    'klinik_slug' => $k->slug,
                ]),
                'icon' => '🏥',
            ];
        }

        // Her zaman "tüm sonuçlarda ara" satırı
        $results[] = [
            'name' => '“'.$q.'” için tüm sonuçlar',
            'subtitle' => 'Doktor listesinde göster',
            'url' => route('frontend.hekimler', ['arama' => $q]),
            'icon' => '🔍',
        ];

        return response()->json($results);
    }

    /**
     * Display a specific doctor's profile page by slug.
     */
    public function hekimDetay(string $il_slug, string $ilce_slug, string $brans_slug, string $doctor_slug)
    {
        $il = Il::where('slug', $il_slug)->firstOrFail();
        $ilce = Ilce::where('il_id', $il->id)->where('slug', $ilce_slug)->firstOrFail();

        $query = Doktor::where('aktif_mi', true)
            ->where('il_id', $il->id)
            ->where('ilce_id', $ilce->id)
            ->where('slug', $doctor_slug);

        $brans = Brans::where('slug', $brans_slug)->firstOrFail();
        $query->whereHas('branslar', function ($q) use ($brans) {
            $q->where('branslar.id', $brans->id);
        });

        $doktor = $query->with([
            'paket',
            'il',
            'ilce',
            'branslar',
            'hizmetler',
            'calismaSaatleri',
            'randevuAyari',
            'galeriler',
            'bloglar' => function ($q) {
                $q->where('aktif_mi', true)->latest();
            },
        ])->firstOrFail();

        if (! $doktor->isListedOnPlatform()) {
            abort(404, 'Bu hekim profili platform vitrininde yayınlanmıyor.');
        }

        return view('frontend.hekimler.detay', compact('doktor'));
    }

    /**
     * Display a specific doctor's blog post by slug.
     */
    public function blogDetay(string $il_slug, string $ilce_slug, string $brans_slug, string $doctor_slug, string $blog_slug)
    {
        $il = Il::where('slug', $il_slug)->firstOrFail();
        $ilce = Ilce::where('il_id', $il->id)->where('slug', $ilce_slug)->firstOrFail();

        $query = Doktor::where('aktif_mi', true)
            ->where('il_id', $il->id)
            ->where('ilce_id', $ilce->id)
            ->where('slug', $doctor_slug);

        $brans = Brans::where('slug', $brans_slug)->firstOrFail();
        $query->whereHas('branslar', function ($q) use ($brans) {
            $q->where('branslar.id', $brans->id);
        });

        $doktor = $query->firstOrFail();
        if (! $doktor->isListedOnPlatform()) {
            abort(404, 'Bu hekim profili platform vitrininde yayınlanmıyor.');
        }
        $blog = $doktor->bloglar()->where('aktif_mi', true)->where('slug', $blog_slug)->firstOrFail();

        // Increment view count
        $blog->increment('okunma_sayisi');

        return view('frontend.hekimler.blog_detay', compact('doktor', 'blog'));
    }

    /**
     * Display a specific doctor's service page by slug.
     */
    public function hizmetDetay(string $il_slug, string $ilce_slug, string $brans_slug, string $doctor_slug, string $hizmet_slug)
    {
        $il = Il::where('slug', $il_slug)->firstOrFail();
        $ilce = Ilce::where('il_id', $il->id)->where('slug', $ilce_slug)->firstOrFail();

        $query = Doktor::where('aktif_mi', true)
            ->where('il_id', $il->id)
            ->where('ilce_id', $ilce->id)
            ->where('slug', $doctor_slug);

        $brans = Brans::where('slug', $brans_slug)->firstOrFail();
        $query->whereHas('branslar', function ($q) use ($brans) {
            $q->where('branslar.id', $brans->id);
        });

        $doktor = $query->firstOrFail();
        if (! $doktor->isListedOnPlatform()) {
            abort(404, 'Bu hekim profili platform vitrininde yayınlanmıyor.');
        }
        $hizmet = $doktor->hizmetler()->where('aktif_mi', true)->where('slug', $hizmet_slug)->firstOrFail();

        return view('frontend.hekimler.hizmet_detay', compact('doktor', 'hizmet'));
    }

    /**
     * Show the profile edit form.
     */
    public function profilDuzenle()
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $doktor->load('il', 'ilce');
        $unvanlar = Unvan::orderBy('ad')->get();

        return view('hekim.profil', compact('doktor', 'unvanlar'));
    }

    /**
     * Update the doctor's profile.
     */
    public function profilGuncelle(Request $request)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $request->validate([
            'ad_soyad' => 'required|string|max:255',
            'telefon' => ['required', 'string', 'regex:/^0\s\(5[0-9]{2}\)\s[0-9]{3}\s[0-9]{2}\s[0-9]{2}$/'],
            'unvan' => 'required|string|exists:unvanlar,ad',
            'il' => 'required|string|max:255',
            'ilce' => 'required|string|max:255',
            'adres' => 'nullable|string|max:1000',
            'profil_resmi' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'instagram' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',
            'linkedin' => 'nullable|string|max:255',
            'youtube' => 'nullable|string|max:255',
            'web_sitesi' => 'nullable|string|max:255',
            'enlem' => 'nullable|numeric|between:-90,90',
            'boylam' => 'nullable|numeric|between:-180,180',
        ], [
            'ad_soyad.required' => 'Ad Soyad alanı zorunludur.',
            'telefon.required' => 'Telefon numarası zorunludur.',
            'telefon.regex' => 'Telefon numarası 0 (5xx) xxx xx xx formatında olmalıdır.',
            'unvan.required' => 'Unvan seçimi zorunludur.',
            'il.required' => 'Hizmet verilen il zorunludur.',
            'ilce.required' => 'Hizmet verilen ilçe zorunludur.',
            'profil_resmi.image' => 'Profil resmi geçerli bir görsel dosyası olmalıdır.',
            'profil_resmi.max' => 'Profil resmi boyutu en fazla 10 MB olabilir.',
        ]);

        $ilModel = Il::where('ad', $request->il)->first();
        $ilceModel = Ilce::where('il_id', $ilModel?->id)->where('ad', $request->ilce)->first();

        $data = [
            'ad_soyad' => $request->ad_soyad,
            'telefon' => $request->telefon,
            'unvan' => $request->unvan,
            'il_id' => $ilModel?->id,
            'ilce_id' => $ilceModel?->id,
            'adres' => $request->adres,
            'instagram' => $request->instagram,
            'facebook' => $request->facebook,
            'twitter' => $request->twitter,
            'linkedin' => $request->linkedin,
            'youtube' => $request->youtube,
            'web_sitesi' => $request->web_sitesi,
            'enlem' => $request->enlem,
            'boylam' => $request->boylam,
        ];

        // Handle Profile Image Upload
        if ($request->hasFile('profil_resmi')) {
            // Delete old file if exists
            if ($doktor->profil_resmi) {
                Storage::disk('public')->delete($doktor->profil_resmi);
            }

            $data['profil_resmi'] = $request->file('profil_resmi')->store('uploads/profil', 'public');
        }

        $doktor->update($data);

        return redirect()->back()->with('basarili', 'Profil bilgileriniz başarıyla güncellendi.');
    }

    /**
     * Show the change password form.
     */
    public function sifreFormu()
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        return view('hekim.sifre', compact('doktor'));
    }

    /**
     * Update the doctor's password.
     */
    public function sifreGuncelle(Request $request)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $request->validate([
            'mevcut_sifre' => 'required|string',
            'sifre' => 'required|string|min:8|confirmed',
        ], [
            'mevcut_sifre.required' => 'Mevcut şifreniz zorunludur.',
            'sifre.required' => 'Yeni şifre alanı zorunludur.',
            'sifre.min' => 'Yeni şifre en az 6 karakter olmalıdır.',
            'sifre.confirmed' => 'Yeni şifreleriniz uyuşmuyor.',
        ]);

        // Validate current password
        if (! Hash::check($request->mevcut_sifre, $doktor->sifre)) {
            return redirect()->back()->withErrors(['mevcut_sifre' => 'Mevcut şifrenizi hatalı girdiniz.']);
        }

        $doktor->update([
            'sifre' => Hash::make($request->sifre),
        ]);

        return redirect()->back()->with('basarili', 'Şifreniz başarıyla güncellendi.');
    }

    /**
     * Show the edit "About Me" form.
     */
    public function hakkimdaFormu()
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $branslar = Brans::orderBy('ad')->get();

        return view('hekim.hakkimda', compact('doktor', 'branslar'));
    }

    /**
     * Update the doctor's "About Me" details.
     */
    public function hakkimdaGuncelle(Request $request)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $request->validate([
            'branslar' => 'required|array|min:1',
            'branslar.*' => 'exists:branslar,id',
            'mezuniyet' => 'nullable|array',
            'mezuniyet.*' => 'nullable|string|max:255',
            'biyografi' => 'nullable|string',
            'klinik_adi' => 'nullable|string|max:255',
        ], [
            'branslar.required' => 'En az bir uzmanlık alanı / branş seçmelisiniz.',
            'klinik_adi.required' => 'Çalıştığınız klinik adı zorunludur.',
        ]);

        $bransIsimleri = Brans::whereIn('id', $request->branslar)->pluck('ad')->toArray();
        $uzmanlikAlaniString = implode(', ', $bransIsimleri);

        $mezuniyetDizisi = array_values(array_filter($request->input('mezuniyet', []), function ($val) {
            return ! is_null($val) && trim($val) !== '';
        }));

        $data = [
            'uzmanlik_alani' => $uzmanlikAlaniString,
            'mezuniyet' => $mezuniyetDizisi,
            'biyografi' => \App\Services\HtmlSanitizer::clean($request->biyografi),
            'klinik_adi' => $request->klinik_adi,
        ];

        $doktor->update($data);
        $doktor->branslar()->sync($request->branslar);

        return redirect()->back()->with('basarili', 'Özgeçmiş ve klinik bilgileriniz başarıyla güncellendi.');
    }

    /**
     * Display a listing of all public blog posts with filtering.
     */
    public function bloglarListesi(Request $request)
    {
        $query = \App\Models\Blog::where('aktif_mi', true)
            ->whereHas('doktor', function ($q) {
                $q->platformdaListelenen()->whereNull('deleted_at');
            })
            ->with(['doktor', 'doktor.il', 'doktor.ilce', 'doktor.branslar']);

        // Search filter
        if ($request->filled('arama')) {
            $arama = $request->input('arama');
            $query->where(function ($q) use ($arama) {
                $q->where('baslik', 'like', "%{$arama}%")
                  ->orWhere('icerik', 'like', "%{$arama}%")
                  ->orWhereHas('doktor', function($dq) use ($arama) {
                      $dq->where('ad_soyad', 'like', "%{$arama}%");
                  });
            });
        }

        // Branch/Specialty filter
        if ($request->filled('brans')) {
            $bransAd = $request->input('brans');
            $query->whereHas('doktor.branslar', function($q) use ($bransAd) {
                $q->where('ad', $bransAd);
            });
        }

        // Ordering
        $siralama = $request->input('sirala', 'yeni');
        if ($siralama === 'populer') {
            $query->orderBy('okunma_sayisi', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $bloglar = $query->paginate(9)->withQueryString();
        $toplamBlogSayisi = $bloglar->total();

        // Get branches list for filters
        $branslar = Cache::remember('branslar_listesi', 86400, function () {
            return Brans::orderBy('ad')->pluck('ad')->all();
        });

        if ($request->ajax()) {
            $html = view('frontend.bloglar.partials.blog_cards', compact('bloglar'))->render();
            return response()->json([
                'html' => $html,
                'total' => $toplamBlogSayisi
            ]);
        }

        return view('frontend.bloglar.index', compact('bloglar', 'branslar', 'toplamBlogSayisi'));
    }
}
