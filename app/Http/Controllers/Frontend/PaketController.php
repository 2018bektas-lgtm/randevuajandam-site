<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\KlinikKayitRequest;
use App\Models\Brans;
use App\Models\Doktor;
use App\Models\Hasta;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Klinik;
use App\Models\Paket;
use App\Models\SiteAyari;
use App\Models\UyelikOdeme;
use App\Models\Unvan;
use App\Services\IyzicoSubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaketController extends Controller
{
    /**
     * Public pricing page. Logged-in doctors go to package selection/checkout.
     */
    public function index()
    {
        $doktor = Auth::guard('doktor')->user();
        if ($doktor) {
            return redirect()->route('frontend.hekim.paket_sec');
        }

        $bireyselPaketler = Paket::query()
            ->where('tur', 'bireysel')
            ->where('aktif_mi', true)
            ->orderBy('aylik_fiyat')
            ->get();

        $klinikPaketler = Paket::query()
            ->where('tur', 'klinik')
            ->where('aktif_mi', true)
            ->orderBy('sira')
            ->orderBy('aylik_fiyat')
            ->get();

        $maxYillikTasarrufYuzde = $this->maxYillikTasarrufYuzde(
            $bireyselPaketler->concat($klinikPaketler)
        );

        return view('frontend.paketler.index', compact(
            'bireyselPaketler',
            'klinikPaketler',
            'maxYillikTasarrufYuzde'
        ));
    }

    /**
     * Best yearly savings % vs 12 × monthly (indirimli or list).
     */
    protected function maxYillikTasarrufYuzde($paketler): int
    {
        $max = 0;
        foreach ($paketler as $p) {
            $aylik = (float) ($p->aylik_indirimli_fiyat ?? $p->aylik_fiyat ?? 0);
            $yillik = (float) ($p->yillik_indirimli_fiyat ?? $p->yillik_fiyat ?? 0);
            if ($aylik <= 0 || $yillik <= 0) {
                continue;
            }
            $onIkiAy = $aylik * 12;
            if ($onIkiAy <= $yillik) {
                continue;
            }
            $pct = (int) round((($onIkiAy - $yillik) / $onIkiAy) * 100);
            $max = max($max, $pct);
        }

        return $max;
    }

    /**
     * Show the doctor registration/purchase form.
     */
    public function kayitFormu(Request $request)
    {
        $branslar = Brans::orderBy('ad')->get();
        $unvanlar = Unvan::orderBy('ad')->get();

        return view('frontend.hekim.kayit', compact('branslar', 'unvanlar'));
    }

    /**
     * Handle the doctor registration.
     */
    public function kayitOl(Request $request)
    {
        $request->validate([
            'ad_soyad' => 'required|string|max:255',
            'e_posta' => 'required|email|max:255|unique:doktorlar,e_posta',
            'sifre' => [
                'required',
                'string',
                'min:8',
                'regex:~^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>_\-#\[\]\\\/]).+$~',
                'confirmed',
            ],
            'telefon' => ['required', 'string', 'regex:/^0\s\(5[0-9]{2}\)\s[0-9]{3}\s[0-9]{2}\s[0-9]{2}$/'],
            'unvan' => 'required|string|exists:unvanlar,ad',
            'il' => 'required|string|max:255',
            'ilce' => 'required|string|max:255',
            'branslar' => 'required|array|min:1',
            'branslar.*' => 'exists:branslar,id',
            'mezuniyet' => 'nullable|array',
            'mezuniyet.*' => 'nullable|string|max:255',
            'biyografi' => 'nullable|string',
        ], [
            'ad_soyad.required' => 'Ad Soyad alanı zorunludur.',
            'e_posta.required' => 'E-posta adresi zorunludur.',
            'e_posta.email' => 'Lütfen geçerli bir e-posta adresi girin.',
            'e_posta.unique' => 'Bu e-posta adresi zaten sisteme kayıtlı.',
            'sifre.required' => 'Şifre alanı zorunludur.',
            'sifre.min' => 'Şifre en az 8 karakter olmalıdır.',
            'sifre.regex' => 'Şifreniz en az bir büyük harf, bir küçük harf, bir sayı ve bir özel karakter içermelidir.',
            'sifre.confirmed' => 'Şifre tekrarı uyuşmuyor.',
            'telefon.required' => 'Telefon numarası zorunludur.',
            'telefon.regex' => 'Telefon numarası 0 (5xx) xxx xx xx formatında olmalıdır.',
            'unvan.required' => 'Mesleki unvan seçimi zorunludur.',
            'il.required' => 'Hizmet verilen il seçimi zorunludur.',
            'ilce.required' => 'Hizmet verilen ilçe seçimi zorunludur.',
            'branslar.required' => 'En az bir uzmanlık alanı / branş seçmelisiniz.',
        ]);

        $ilModel = Il::where('ad', $request->il)->first();
        $ilceModel = Ilce::where('il_id', $ilModel?->id)->where('ad', $request->ilce)->first();

        // Get branch names as a comma-separated string for compatibility
        $bransIsimleri = Brans::whereIn('id', $request->branslar)->pluck('ad')->toArray();
        $uzmanlikAlaniString = implode(', ', $bransIsimleri);

        // Filter out empty graduation values
        $mezuniyetDizisi = array_values(array_filter($request->input('mezuniyet', []), function ($val) {
            return ! is_null($val) && trim($val) !== '';
        }));

        // Create doctor account and attach branches atomically
        $doktor = DB::transaction(function () use ($request, $uzmanlikAlaniString, $mezuniyetDizisi, $ilModel, $ilceModel) {
            $doktor = Doktor::create([
                'ad_soyad' => $request->ad_soyad,
                'e_posta' => $request->e_posta,
                'sifre' => Hash::make($request->sifre),
                'telefon' => $request->telefon,
                'il_id' => $ilModel?->id,
                'ilce_id' => $ilceModel?->id,
                'unvan' => $request->unvan,
                'uzmanlik_alani' => $uzmanlikAlaniString,
                'mezuniyet' => $mezuniyetDizisi,
                'biyografi' => $request->biyografi,
                'tur' => 'bireysel', // default initially
                'paket_id' => null,  // no package chosen yet
                'odeme_periyodu' => null,
                'uyelik_baslangic' => null,
                'uyelik_bitis' => null,
                'iyzico_subscription_reference_code' => null,
                'iyzico_subscription_status' => null,
                'aktif_mi' => true,
            ]);

            $doktor->branslar()->attach($request->branslar);

            return $doktor;
        });

        // Automatically log in the doctor after registration
        Auth::guard('doktor')->login($doktor);

        return redirect()->route('frontend.hekim.paket_sec');
    }

    /**
     * Show successful registration page.
     * Web sitesi paketinde domain kurulmamışsa önce domain adımına yönlendir.
     */
    public function basarili()
    {
        $doktor = Auth::guard('doktor')->user();
        if (! $doktor) {
            return redirect()->route('frontend.paketler');
        }

        // Deneme aktifken domain adımına zorlama (starter'da web yok)
        // Ödeme sonrası zorunlu domain adımı (atlandıysa veya tamamlandıysa değil)
        if (
            ! $doktor->isOnTrial()
            && $doktor->needsWebsiteDomainOnboarding()
            && ! session('onboarding_domain_skipped')
            && ! session('onboarding_domain_done')
            && ! session('plain_api_secret')
        ) {
            return redirect()->route('frontend.hekim.onboarding.domain');
        }

        if ($doktor->klinikSahibiMi() && $doktor->klinik) {
            $klinik = $doktor->klinik;

            return view('frontend.klinik.basarili', compact('klinik'));
        }

        return view('frontend.paketler.basarili', compact('doktor'));
    }

    /**
     * Üyelik aktifleşince: session'daki domaini kur; yoksa (web paketi) sonradan domain adımı.
     */
    protected function redirectAfterMembership(?Doktor $doktor = null)
    {
        $doktor = $doktor ?? Auth::guard('doktor')->user();
        if (! $doktor) {
            return redirect()->route('frontend.hekim.basarili');
        }

        $doktor = $doktor->fresh(['paket', 'klinik', 'webSite']);
        $pending = session(HekimOnboardingController::SESSION_PENDING);
        $flash = [];

        if (is_array($pending) && ! empty($pending['domain']) && ! empty($pending['mode'])) {
            try {
                $provisioning = app(\App\Services\WebsiteProvisioningService::class);
                $mode = $pending['mode'] === 'byod' ? 'byod' : 'included';
                $target = $pending['target'] ?? 'doctor';

                if ($target === 'clinic' && $doktor->klinik) {
                    $result = $provisioning->provisionKlinik($doktor->klinik, $pending['domain'], $mode);
                } else {
                    $result = $provisioning->provisionDoktor($doktor, $pending['domain'], $mode);
                }

                session()->forget(HekimOnboardingController::SESSION_PENDING);
                $flash = [
                    'basarili' => 'Üyelik aktif. Domain kuruldu: '.$result['domain'],
                    'plain_api_secret' => $result['plain_secret'],
                    'onboarding_domain_done' => $result['domain'],
                ];
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Post-pay domain provision failed', [
                    'error' => $e->getMessage(),
                    'domain' => $pending['domain'] ?? null,
                ]);
                // Domain seçilmişti ama kurulum başarısız → onboarding'e dön
                return redirect()
                    ->route('frontend.hekim.onboarding.domain')
                    ->with('hata', 'Üyelik aktif; domain kurulumu tamamlanamadı: '.$e->getMessage().' Lütfen tekrar deneyin.');
            }
        }

        $redirect = redirect()->route('frontend.hekim.basarili');
        foreach ($flash as $k => $v) {
            $redirect = $redirect->with($k, $v);
        }

        // Domain hiç seçilmediyse ve web paketi varsa sonradan kurulum
        if (
            empty($flash)
            && $doktor->needsWebsiteDomainOnboarding()
            && ! session('onboarding_domain_skipped')
        ) {
            return redirect()->route('frontend.hekim.onboarding.domain');
        }

        return $redirect;
    }

    /**
     * Show the clinic registration/purchase form.
     */
    public function klinikKayitFormu(Request $request)
    {
        $paketId = $request->query('paket');
        $periyot = $request->query('periyot', 'aylik');

        $secilenPaket = Paket::where('aktif_mi', true)->find($paketId);

        if (! $secilenPaket || ! $secilenPaket->klinikPaketiMi()) {
            $secilenPaket = Paket::where('aktif_mi', true)->where('tur', 'klinik')->first();
        }

        if (! $secilenPaket) {
            return redirect()->route('frontend.paketler')->with('hata', 'Lütfen geçerli bir klinik paketi seçin.');
        }

        $iller = Il::orderBy('ad')->get();
        $branslar = Brans::orderBy('ad')->get();
        $unvanlar = Unvan::orderBy('ad')->get();

        return view('frontend.klinik.kayit', compact('secilenPaket', 'periyot', 'iller', 'branslar', 'unvanlar'));
    }

    /**
     * Handle the clinic registration and simulated payment.
     */
    public function klinikKayitOl(KlinikKayitRequest $request)
    {
        $paket = Paket::findOrFail($request->paket_id);

        $ilModel = Il::find($request->il_id);
        $ilceModel = Ilce::where('il_id', $ilModel?->id)->where('ad', $request->ilce_id)->first();

        // Initialize iyzico subscription payment
        $subscriptionService = app(IyzicoSubscriptionService::class);
        $doktorMock = new Doktor([
            'ad_soyad' => $request->ad_soyad,
            'e_posta' => $request->doktor_eposta,
            'telefon' => $request->doktor_telefon,
            'il_id' => $ilModel?->id,
            'ilce_id' => $ilceModel?->id,
        ]);
        $doktorMock->setRelations([
            'il' => $ilModel,
            'ilce' => $ilceModel,
        ]);

        $cardDetails = $request->only(['kart_sahibi', 'kart_no', 'kart_skt', 'kart_cvv']);
        $paymentResult = $subscriptionService->subscribeDoctor($doktorMock, $paket, $request->odeme_periyodu, $cardDetails);

        if ($paymentResult['status'] !== 'success') {
            return back()->withInput()->withErrors([
                'kart_no' => $paymentResult['errorMessage'] ?? 'Ödeme işlemi gerçekleştirilemedi.',
            ]);
        }

        $baslangic = now();
        $bitis = $request->odeme_periyodu === 'aylik' ? now()->addMonth() : now()->addYear();

        $bransIsimleri = Brans::whereIn('id', $request->branslar)->pluck('ad')->toArray();
        $uzmanlikAlaniString = implode(', ', $bransIsimleri);

        $klinik = DB::transaction(function () use ($request, $paket, $baslangic, $bitis, $uzmanlikAlaniString, $ilModel, $ilceModel, $paymentResult) {
            $doktor = Doktor::create([
                'ad_soyad' => $request->ad_soyad,
                'e_posta' => $request->doktor_eposta,
                'sifre' => Hash::make($request->sifre),
                'telefon' => $request->doktor_telefon,
                'il_id' => $ilModel?->id,
                'ilce_id' => $ilceModel?->id,
                'unvan' => $request->unvan,
                'uzmanlik_alani' => $uzmanlikAlaniString,
                'tur' => 'klinik',
                'klinik_adi' => $request->klinik_adi,
                'paket_id' => $paket->id,
                'odeme_periyodu' => $request->odeme_periyodu,
                'uyelik_baslangic' => $baslangic,
                'uyelik_bitis' => $bitis,
                'iyzico_subscription_reference_code' => $paymentResult['referenceCode'],
                'iyzico_subscription_status' => $paymentResult['subscriptionStatus'],
                'aktif_mi' => true,
                'klinik_rolu' => 'sahip',
                'klinik_katilma_tarihi' => now(),
                'klinik_aktif_mi' => true,
            ]);

            $klinik = Klinik::create([
                'ad' => $request->klinik_adi,
                'sahip_doktor_id' => $doktor->id,
                'paket_id' => $paket->id,
                'telefon' => $request->telefon,
                'e_posta' => $request->e_posta,
                'adres' => $request->adres,
                'il_id' => $ilModel?->id,
                'ilce_id' => $ilceModel?->id,
                'odeme_periyodu' => $request->odeme_periyodu,
                'uyelik_baslangic' => $baslangic,
                'uyelik_bitis' => $bitis,
                'max_doktor_sayisi' => $paket->max_doktor_sayisi ?? 3,
                'aktif_mi' => true,
            ]);

            $doktor->update(['klinik_id' => $klinik->id]);
            $doktor->branslar()->attach($request->branslar);

            $doktor->randevuAyari()->create([
                'aktif_mi' => true,
                'sure' => 30,
                'fiyat' => 0,
            ]);

            return $klinik;
        });

        Auth::guard('doktor')->login($klinik->sahipDoktor);

        return $this->redirectAfterMembership($klinik->sahipDoktor);
    }

    /**
     * Show the clinic transition form for individual doctors.
     */
    public function gecisFormu()
    {
        $doktor = Auth::guard('doktor')->user();

        if (! $doktor->bireyselMi()) {
            return redirect()->route('hekim.panel')->with('hata', 'Zaten bir kliniğe üyesiniz.');
        }

        $paketler = Paket::where('aktif_mi', true)->where('tur', 'klinik')->orderBy('sira')->get();
        $iller = Il::orderBy('ad')->get();

        return view('klinik.gecis', compact('doktor', 'paketler', 'iller'));
    }

    /**
     * Handle the clinic transition process (Upgrade).
     */
    public function gecisYap(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();

        if (! $doktor->bireyselMi()) {
            return redirect()->route('hekim.panel')->with('hata', 'Zaten bir kliniğe üyesiniz.');
        }

        $request->validate([
            'klinik_adi' => 'required|string|max:255',
            'telefon' => 'required|string',
            'e_posta' => 'nullable|email|max:255',
            'adres' => 'required|string',
            'il_id' => 'required|exists:iller,id',
            'ilce_id' => 'required|string|max:255|exists:ilceler,ad',
            'paket_id' => 'required|exists:paketler,id',
            'odeme_periyodu' => 'required|in:aylik,yillik',
            'kart_sahibi' => 'required|string|max:255',
            'kart_no' => 'required|string|min:16|max:19',
            'kart_skt' => 'required|string|max:5',
            'kart_cvv' => 'required|string|min:3|max:4',
        ]);

        $paket = Paket::findOrFail($request->paket_id);

        if (! $paket->klinikPaketiMi()) {
            return back()->withErrors(['paket_id' => 'Lütfen geçerli bir klinik paketi seçin.']);
        }

        $ilModel = Il::find($request->il_id);
        $ilceModel = Ilce::where('il_id', $ilModel?->id)->where('ad', $request->ilce_id)->first();

        // Initialize iyzico subscription payment
        $subscriptionService = app(IyzicoSubscriptionService::class);

        $cardDetails = $request->only(['kart_sahibi', 'kart_no', 'kart_skt', 'kart_cvv']);
        $paymentResult = $subscriptionService->subscribeDoctor($doktor, $paket, $request->odeme_periyodu, $cardDetails);

        if ($paymentResult['status'] !== 'success') {
            return back()->withInput()->withErrors([
                'kart_no' => $paymentResult['errorMessage'] ?? 'Ödeme işlemi gerçekleştirilemedi.',
            ]);
        }

        // Eski bireysel aboneliği iyzico'da kapat (çift çekim olmasın)
        $oldRef = $doktor->iyzico_subscription_reference_code;
        if ($subscriptionService->isRealSubscriptionReference($oldRef)) {
            $oldCancel = $subscriptionService->cancelSubscription($oldRef);
            if (($oldCancel['status'] ?? '') !== 'success') {
                Log::error('Failed to cancel old sub before clinic upgrade', [
                    'doktor_id' => $doktor->id,
                    'ref' => $oldRef,
                    'result' => $oldCancel,
                ]);
                // Yine de yeni abonelik açıldı; log + devam (upgrade akışı)
            }
        }

        $baslangic = now();
        $bitis = $request->odeme_periyodu === 'aylik' ? now()->addMonth() : now()->addYear();

        $klinik = DB::transaction(function () use ($request, $doktor, $paket, $baslangic, $bitis, $ilModel, $ilceModel, $paymentResult) {
            // Create clinic
            $klinikAttrs = [
                'ad' => $request->klinik_adi,
                'sahip_doktor_id' => $doktor->id,
                'paket_id' => $paket->id,
                'telefon' => $request->telefon,
                'e_posta' => $request->e_posta,
                'adres' => $request->adres,
                'il_id' => $ilModel?->id,
                'ilce_id' => $ilceModel?->id,
                'odeme_periyodu' => $request->odeme_periyodu,
                'uyelik_baslangic' => $baslangic,
                'uyelik_bitis' => $bitis,
                'max_doktor_sayisi' => $paket->max_doktor_sayisi ?? 3,
                'aktif_mi' => true,
            ];
            if (\Illuminate\Support\Facades\Schema::hasColumn('klinikler', 'iyzico_subscription_reference_code')) {
                $klinikAttrs['iyzico_subscription_reference_code'] = $paymentResult['referenceCode'] ?? null;
                $klinikAttrs['iyzico_subscription_status'] = $paymentResult['subscriptionStatus'] ?? 'ACTIVE';
                $klinikAttrs['abonelik_yenileme_kapali'] = false;
                $klinikAttrs['abonelik_iptal_at'] = null;
            }
            $klinik = Klinik::create($klinikAttrs);

            // Update doctor
            $doktor->update([
                'klinik_id' => $klinik->id,
                'klinik_rolu' => 'sahip',
                'klinik_katilma_tarihi' => now(),
                'klinik_aktif_mi' => true,
                'paket_id' => $paket->id,
                'odeme_periyodu' => $request->odeme_periyodu,
                'uyelik_baslangic' => $baslangic,
                'uyelik_bitis' => $bitis,
                'iyzico_subscription_reference_code' => $paymentResult['referenceCode'],
                'iyzico_subscription_status' => $paymentResult['subscriptionStatus'],
                'abonelik_yenileme_kapali' => false,
                'abonelik_iptal_at' => null,
                'abonelik_iptal_nedeni' => null,
            ]);

            // Copy doctor's patients to clinic patient pool
            $existingPatients = Hasta::whereHas('randevular', function ($query) use ($doktor) {
                $query->where('doktor_id', $doktor->id);
            })->pluck('id')->toArray();

            if (! empty($existingPatients)) {
                $syncData = [];
                foreach ($existingPatients as $pId) {
                    $syncData[$pId] = [
                        'kayit_tarihi' => now(),
                        'notlar' => 'Bireysel hekimlikten kliniğe geçiş sırasında aktarıldı.',
                    ];
                }
                $klinik->hastalar()->syncWithoutDetaching($syncData);
            }

            return $klinik;
        });

        return $this->redirectAfterMembership($doktor->fresh());
    }

    /**
     * Show packages selection for logged-in doctor.
     */
    public function paketSecFormu()
    {
        $doktor = Auth::guard('doktor')->user();

        // Get all active packages
        $paketler = Paket::where('aktif_mi', true)->with('sistemOzellikleri')->orderBy('sira')->get();

        // Separate them by type
        $bireyselPaketler = $paketler->where('tur', 'bireysel')->values();
        $klinikPaketler = $paketler->where('tur', 'klinik')->values();
        $maxYillikTasarrufYuzde = $this->maxYillikTasarrufYuzde($paketler);

        return view('frontend.hekim.paket_sec', compact(
            'doktor',
            'bireyselPaketler',
            'klinikPaketler',
            'maxYillikTasarrufYuzde'
        ));
    }

    /**
     * Başlangıç paketi 14 gün ücretsiz deneme — ödeme yok.
     */
    public function paketDenemeBaslat(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();
        $paket = Paket::where('aktif_mi', true)->findOrFail($request->input('paket_id'));

        if ($paket->klinikPaketiMi()) {
            return redirect()->route('frontend.hekim.paket_sec')
                ->with('hata', 'Deneme yalnızca bireysel Başlangıç paketi içindir.');
        }

        if (! $paket->denemeVarMi()) {
            return redirect()->route('frontend.hekim.paket_ode', [
                'paket' => $paket->id,
                'periyot' => 'aylik',
            ])->with('hata', 'Bu pakette ücretsiz deneme yok.');
        }

        if (! $doktor->canStartTrial($paket)) {
            return redirect()->route('frontend.hekim.paket_ode', [
                'paket' => $paket->id,
                'periyot' => 'aylik',
            ])->with(
                'hata',
                $doktor->deneme_kullanildi
                    ? 'Ücretsiz deneme hakkınızı daha önce kullandınız. Lütfen ödeme ile devam edin.'
                    : 'Deneme başlatılamıyor. Lütfen ödeme ile paket seçin.'
            );
        }

        $gun = $paket->denemeGun();
        $baslangic = now();
        $bitis = now()->addDays($gun);

        $doktor->update([
            'paket_id' => $paket->id,
            'odeme_periyodu' => 'deneme',
            'uyelik_baslangic' => $baslangic,
            'uyelik_bitis' => $bitis,
            'deneme_kullanildi' => true,
            'iyzico_subscription_reference_code' => 'trial_'.$gun.'d_'.Str::random(10),
            'iyzico_subscription_status' => 'TRIAL',
            'tur' => 'bireysel',
            'abonelik_yenileme_kapali' => false,
            'abonelik_iptal_at' => null,
            'abonelik_iptal_nedeni' => null,
        ]);

        return redirect()
            ->route('frontend.hekim.basarili')
            ->with(
                'basarili',
                "{$gun} günlük ücretsiz denemeniz başladı. Süre bitince paket seçip ödeme yapmanız gerekecek."
            );
    }

    /**
     * Show package payment form for logged-in doctor.
     * Web sitesi paketinde domain seçilmediyse önce domain adımına yönlendir.
     */
    public function paketOdeFormu(Request $request)
    {
        $paketId = $request->query('paket');
        $periyot = $request->query('periyot', 'aylik');

        $secilenPaket = Paket::where('aktif_mi', true)->with('sistemOzellikleri')->find($paketId);
        if (! $secilenPaket) {
            return redirect()->route('frontend.hekim.paket_sec')->with('hata', 'Lütfen geçerli bir paket seçin.');
        }

        // Deneme hakkı varsa ödeme formuna girmeden deneme başlat sayfası / otomatik
        $doktor = Auth::guard('doktor')->user();
        if (
            $request->query('mod') === 'deneme'
            || ($secilenPaket->denemeVarMi() && $doktor && $doktor->canStartTrial($secilenPaket) && $request->boolean('auto_trial'))
        ) {
            if ($doktor && $doktor->canStartTrial($secilenPaket)) {
                return $this->paketDenemeBaslat(new Request(['paket_id' => $secilenPaket->id]));
            }
        }

        // Domain adımı zorunlu değil ama web paketinde seçim yoksa ve atlanmamışsa domain'e yönlendir
        if (HekimOnboardingController::packageNeedsDomain($secilenPaket)) {
            $pending = session(HekimOnboardingController::SESSION_PENDING);
            $pendingOk = is_array($pending)
                && (int) ($pending['paket_id'] ?? 0) === (int) $secilenPaket->id
                && ! empty($pending['domain']);
            $skipped = (bool) session('onboarding_domain_skipped');

            if (! $pendingOk && ! $skipped && ! $request->boolean('domain_ok')) {
                return redirect()->route('frontend.hekim.onboarding.domain', [
                    'paket' => $secilenPaket->id,
                    'periyot' => $periyot,
                ]);
            }
        }

        $doktor = Auth::guard('doktor')->user();
        $iller = Il::orderBy('ad')->get();
        $iyzicoAvailable = app(IyzicoSubscriptionService::class)->isConfigured();
        $paymentSettings = SiteAyari::query()->first();
        $bankAvailable = filled($paymentSettings?->banka_adi)
            && filled($paymentSettings?->banka_hesap_sahibi)
            && filled($paymentSettings?->banka_iban);
        $listedPrice = $periyot === 'aylik'
            ? (float) $secilenPaket->aylik_fiyat
            : (float) $secilenPaket->yillik_fiyat;
        $discountedPrice = $periyot === 'aylik'
            ? $secilenPaket->aylik_indirimli_fiyat
            : $secilenPaket->yillik_indirimli_fiyat;
        $tutar = $discountedPrice !== null && (float) $discountedPrice > 0
            ? (float) $discountedPrice
            : $listedPrice;

        $pendingDomain = session(HekimOnboardingController::SESSION_PENDING);

        return view('frontend.hekim.paket_ode', compact(
            'secilenPaket',
            'periyot',
            'doktor',
            'iller',
            'iyzicoAvailable',
            'paymentSettings',
            'bankAvailable',
            'tutar',
            'pendingDomain',
        ));
    }

    /**
     * Handle package payment and subscription activation.
     */
    public function paketOde(Request $request)
    {
        $paket = Paket::where('aktif_mi', true)->findOrFail($request->paket_id);
        $periodPrice = $request->odeme_periyodu === 'yillik'
            ? (float) $paket->yillik_fiyat
            : (float) $paket->aylik_fiyat;
        $discountedPrice = $request->odeme_periyodu === 'yillik'
            ? $paket->yillik_indirimli_fiyat
            : $paket->aylik_indirimli_fiyat;
        $tutar = $discountedPrice !== null && (float) $discountedPrice > 0
            ? (float) $discountedPrice
            : $periodPrice;
        $isFree = $tutar <= 0;
        $doktor = Auth::guard('doktor')->user();

        $rules = [
            'paket_id' => 'required|exists:paketler,id',
            'odeme_periyodu' => 'required|in:aylik,yillik',
        ];

        // If it is a clinic package, we require clinic details
        if ($paket->klinikPaketiMi()) {
            $rules['klinik_adi'] = 'required|string|max:255';
            $rules['telefon'] = 'required|string';
            $rules['e_posta'] = 'nullable|email|max:255';
            $rules['adres'] = 'required|string';
            $rules['il_id'] = 'required|exists:iller,id';
            $rules['ilce_id'] = 'required|string|max:255';
        }

        if (! $isFree) {
            $rules['odeme_yontemi'] = 'required|in:iyzico,havale';
        }

        // Kartlı abonelikte T.C. kimlik (iyzico zorunlu)
        if (! $isFree && $request->input('odeme_yontemi', 'iyzico') === 'iyzico') {
            if (! filled($doktor->tc_kimlik_no)) {
                return redirect()
                    ->route('hekim.profil')
                    ->with('hata', 'Kredi kartı ile abonelik için profilinizde 11 haneli T.C. kimlik numarası kaydedilmelidir.')
                    ->withInput();
            }
        }

        $request->validate($rules, [
            'paket_id.exists' => 'Lütfen geçerli bir üyelik paketi seçin.',
            'odeme_periyodu.in' => 'Ödeme periyodu aylık veya yıllık olmalıdır.',
            'klinik_adi.required' => 'Klinik adı zorunludur.',
            'telefon.required' => 'Klinik telefon numarası zorunludur.',
            'adres.required' => 'Klinik adresi zorunludur.',
            'il_id.required' => 'İl seçimi zorunludur.',
            'ilce_id.required' => 'İlçe seçimi zorunludur.',
            'kart_sahibi.required' => 'Kart sahibi adı zorunludur.',
            'kart_no.required' => 'Kredi kartı numarası zorunludur.',
            'kart_skt.required' => 'Son kullanma tarihi zorunludur.',
            'kart_cvv.required' => 'CVV kodu zorunludur.',
        ]);

        $paymentSettings = SiteAyari::query()->first();

        if ($isFree) {
            $paymentResult = [
                'status' => 'success',
                'referenceCode' => 'free_trial_' . Str::random(12),
                'subscriptionStatus' => 'ACTIVE',
            ];
        } elseif ($request->odeme_yontemi === 'havale') {
            if (! filled($paymentSettings?->banka_adi)
                || ! filled($paymentSettings?->banka_hesap_sahibi)
                || ! filled($paymentSettings?->banka_iban)) {
                return back()->withInput()->withErrors(['odeme_yontemi' => 'Havale bilgileri henüz yönetici tarafından yapılandırılmadı.']);
            }

            $request->validate([
                'havale_referans' => ['required', 'string', 'max:100'],
            ], [
                'havale_referans.required' => 'Havale referansını veya açıklamasını girin.',
            ]);

            UyelikOdeme::create([
                'doktor_id' => $doktor->id,
                'paket_id' => $paket->id,
                'odeme_yontemi' => 'havale',
                'odeme_periyodu' => $request->odeme_periyodu,
                'tutar' => $tutar,
                'durum' => 'beklemede',
                'havale_referans' => trim((string) $request->havale_referans),
                'kurulum_verisi' => $paket->klinikPaketiMi() ? $request->only([
                    'klinik_adi',
                    'telefon',
                    'e_posta',
                    'adres',
                    'il_id',
                    'ilce_id',
                ]) : null,
            ]);

            return redirect()->route('frontend.hekim.paket_sec')->with(
                'basarili',
                'Havale bildiriminiz alındı. Banka hareketi doğrulandığında üyeliğiniz yönetici tarafından aktifleştirilecektir.'
            );
        } else {
            // Initialize iyzico subscription payment
            $subscriptionService = app(IyzicoSubscriptionService::class);
            if (! $subscriptionService->isConfigured()) {
                return back()->withInput()->withErrors(['odeme_yontemi' => 'Kredi kartı ödemesi şu anda kullanıma açık değil.']);
            }

            $request->validate([
                'kart_sahibi' => ['required', 'string', 'max:255'],
                'kart_no' => ['required', 'string', 'min:16', 'max:19'],
                'kart_skt' => ['required', 'string', 'max:5'],
                'kart_cvv' => ['required', 'string', 'min:3', 'max:4'],
            ]);

            $cardDetails = $request->only(['kart_sahibi', 'kart_no', 'kart_skt', 'kart_cvv']);
            $paymentResult = $subscriptionService->subscribeDoctor($doktor, $paket, $request->odeme_periyodu, $cardDetails);

            if ($paymentResult['status'] !== 'success') {
                return back()->withInput()->withErrors([
                    'kart_no' => $paymentResult['errorMessage'] ?? 'Ödeme işlemi gerçekleştirilemedi.',
                ]);
            }
        }

        // Calculate membership dates
        $baslangic = now();
        $bitis = $request->odeme_periyodu === 'aylik' ? now()->addMonth() : now()->addYear();

        DB::transaction(function () use ($request, $paket, $baslangic, $bitis, $paymentResult, $doktor) {
            if ($paket->klinikPaketiMi()) {
                $ilModel = Il::find($request->il_id);
                $ilceModel = Ilce::where('il_id', $ilModel?->id)->where('ad', $request->ilce_id)->first();

                // Create the Clinic
                $klinik = Klinik::create([
                    'ad' => $request->klinik_adi,
                    'sahip_doktor_id' => $doktor->id,
                    'paket_id' => $paket->id,
                    'telefon' => $request->telefon,
                    'e_posta' => $request->e_posta,
                    'adres' => $request->adres,
                    'il_id' => $ilModel?->id,
                    'ilce_id' => $ilceModel?->id,
                    'odeme_periyodu' => $request->odeme_periyodu,
                    'uyelik_baslangic' => $baslangic,
                    'uyelik_bitis' => $bitis,
                    'max_doktor_sayisi' => $paket->max_doktor_sayisi ?? 3,
                    'aktif_mi' => true,
                ]);

                // Update the Doctor
                $doktor->update([
                    'paket_id' => $paket->id,
                    'odeme_periyodu' => $request->odeme_periyodu,
                    'uyelik_baslangic' => $baslangic,
                    'uyelik_bitis' => $bitis,
                    'iyzico_subscription_reference_code' => $paymentResult['referenceCode'],
                    'iyzico_subscription_status' => $paymentResult['subscriptionStatus'],
                    'klinik_id' => $klinik->id,
                    'klinik_rolu' => 'sahip',
                    'klinik_katilma_tarihi' => now(),
                    'klinik_aktif_mi' => true,
                    'tur' => 'klinik',
                ]);
            } else {
                // Individual package (ücretli — deneme bittikten sonra da buraya düşer)
                $doktor->update([
                    'paket_id' => $paket->id,
                    'odeme_periyodu' => $request->odeme_periyodu,
                    'uyelik_baslangic' => $baslangic,
                    'uyelik_bitis' => $bitis,
                    'iyzico_subscription_reference_code' => $paymentResult['referenceCode'],
                    'iyzico_subscription_status' => $paymentResult['subscriptionStatus'],
                    'tur' => 'bireysel',
                    // Yeni ödeme = iptal bayraklarını temizle
                    'abonelik_yenileme_kapali' => false,
                    'abonelik_iptal_at' => null,
                    'abonelik_iptal_nedeni' => null,
                ]);
            }
        });

        return $this->redirectAfterMembership($doktor->fresh());
    }
}
