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
use App\Rules\TcKimlikNo;
use App\Services\IyzicoSubscriptionService;
use App\Services\PaytrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
            if (! $doktor->canProceedToPayment()) {
                return redirect()->route('frontend.hekim.meslek.bekleme');
            }

            return redirect()->to($doktor->checkoutUrlAfterMeslek());
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
     * Show the doctor registration form.
     * Akış: önce paket seç (/paketler) → kayıt → meslek → ödeme (aynı paket).
     */
    public function kayitFormu(Request $request)
    {
        $paketId = $request->query('paket') ?: session('kayit_paket_id');
        $periyot = $request->query('periyot', session('kayit_periyot', 'aylik'));
        if (! in_array($periyot, ['aylik', 'yillik'], true)) {
            $periyot = 'aylik';
        }

        $secilenPaket = $paketId
            ? Paket::where('aktif_mi', true)->find($paketId)
            : null;

        if (! $secilenPaket) {
            return redirect()
                ->route('frontend.paketler')
                ->with('hata', 'Kayıt için önce bir paket seçin. Onay sonrası aynı paketle ödemeye geçersiniz.');
        }

        session([
            'kayit_paket_id' => $secilenPaket->id,
            'kayit_periyot' => $periyot,
        ]);

        $branslar = Brans::orderBy('ad')->get();
        $unvanlar = Unvan::orderBy('ad')->get();

        return view('frontend.hekim.kayit', compact('branslar', 'unvanlar', 'secilenPaket', 'periyot'));
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
            'tc_kimlik_no' => ['required', 'string', 'size:11', 'unique:doktorlar,tc_kimlik_no', new TcKimlikNo],
            'diploma_no' => ['required', 'string', 'min:3', 'max:64'],
            'edevlet_barkod' => ['nullable', 'string', 'max:64', 'regex:/^[A-Za-z0-9\-]+$/'],
            'meslek_belgesi' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'unvan' => 'required|string|exists:unvanlar,ad',
            'il' => 'required|string|max:255',
            'ilce' => 'required|string|max:255',
            'branslar' => 'required|array|min:1',
            'branslar.*' => 'exists:branslar,id',
            'mezuniyet' => 'nullable|array',
            'mezuniyet.*' => 'nullable|string|max:255',
            'biyografi' => 'nullable|string',
            'kvkk_onay' => 'accepted',
            'sozlesme_onay' => 'accepted',
            'paket_id' => 'required|exists:paketler,id',
            'odeme_periyodu' => 'required|in:aylik,yillik',
        ], [
            'ad_soyad.required' => 'Ad Soyad alanı zorunludur.',
            'paket_id.required' => 'Kayıt için paket seçimi zorunludur.',
            'paket_id.exists' => 'Seçilen paket geçersiz. Lütfen paketler sayfasından yeniden seçin.',
            'e_posta.required' => 'E-posta adresi zorunludur.',
            'e_posta.email' => 'Lütfen geçerli bir e-posta adresi girin.',
            'e_posta.unique' => 'Bu e-posta adresi zaten sisteme kayıtlı.',
            'sifre.required' => 'Şifre alanı zorunludur.',
            'sifre.min' => 'Şifre en az 8 karakter olmalıdır.',
            'sifre.regex' => 'Şifreniz en az bir büyük harf, bir küçük harf, bir sayı ve bir özel karakter içermelidir.',
            'sifre.confirmed' => 'Şifre tekrarı uyuşmuyor.',
            'telefon.required' => 'Telefon numarası zorunludur.',
            'telefon.regex' => 'Telefon numarası 0 (5xx) xxx xx xx formatında olmalıdır.',
            'tc_kimlik_no.required' => 'T.C. kimlik numarası zorunludur.',
            'tc_kimlik_no.unique' => 'Bu T.C. kimlik numarası ile kayıtlı bir hekim zaten var.',
            'diploma_no.required' => 'Diploma / tescil numarası zorunludur.',
            'edevlet_barkod.regex' => 'e-Devlet barkod numarası yalnızca harf, rakam ve tire içerebilir.',
            'meslek_belgesi.required' => 'Diploma veya hekimlik belgesi yüklemeniz zorunludur.',
            'meslek_belgesi.mimes' => 'Belge PDF, JPG veya PNG olmalıdır.',
            'meslek_belgesi.max' => 'Belge en fazla 5 MB olabilir.',
            'unvan.required' => 'Mesleki unvan seçimi zorunludur.',
            'il.required' => 'Hizmet verilen il seçimi zorunludur.',
            'ilce.required' => 'Hizmet verilen ilçe seçimi zorunludur.',
            'branslar.required' => 'En az bir uzmanlık alanı / branş seçmelisiniz.',
            'kvkk_onay.accepted' => 'KVKK aydınlatma metnini kabul etmelisiniz.',
            'sozlesme_onay.accepted' => 'Kullanım koşullarını kabul etmelisiniz.',
        ]);

        $kayitPaket = Paket::where('aktif_mi', true)->findOrFail($request->input('paket_id'));
        $kayitPeriyot = $request->input('odeme_periyodu', 'aylik');

        $ilModel = Il::where('ad', $request->il)->first();
        $ilceModel = Ilce::where('il_id', $ilModel?->id)->where('ad', $request->ilce)->first();

        $bransIsimleri = Brans::whereIn('id', $request->branslar)->pluck('ad')->toArray();
        $uzmanlikAlaniString = implode(', ', $bransIsimleri);

        $mezuniyetDizisi = array_values(array_filter($request->input('mezuniyet', []), function ($val) {
            return ! is_null($val) && trim($val) !== '';
        }));

        $tc = preg_replace('/\D/', '', (string) $request->tc_kimlik_no) ?? '';

        $belgeRel = $request->file('meslek_belgesi')->store(
            'private/meslek-belgeleri',
            'local'
        );

        $doktor = DB::transaction(function () use ($request, $uzmanlikAlaniString, $mezuniyetDizisi, $ilModel, $ilceModel, $tc, $belgeRel, $kayitPaket, $kayitPeriyot) {
            $doktor = Doktor::create([
                'ad_soyad' => $request->ad_soyad,
                'e_posta' => $request->e_posta,
                'sifre' => Hash::make($request->sifre),
                'telefon' => $request->telefon,
                'tc_kimlik_no' => $tc,
                'diploma_no' => trim((string) $request->diploma_no),
                'edevlet_barkod' => $request->filled('edevlet_barkod')
                    ? strtoupper(trim((string) $request->edevlet_barkod))
                    : null,
                'meslek_belge_yolu' => $belgeRel,
                'meslek_dogrulama_durumu' => 'beklemede',
                'il_id' => $ilModel?->id,
                'ilce_id' => $ilceModel?->id,
                'unvan' => $request->unvan,
                'uzmanlik_alani' => $uzmanlikAlaniString,
                'mezuniyet' => $mezuniyetDizisi,
                'biyografi' => $request->biyografi,
                'tur' => $kayitPaket->klinikPaketiMi() ? 'klinik' : 'bireysel',
                'paket_id' => null,
                'kayit_paket_id' => $kayitPaket->id,
                'kayit_periyot' => $kayitPeriyot,
                'odeme_periyodu' => null,
                'uyelik_baslangic' => null,
                'uyelik_bitis' => null,
                'iyzico_subscription_reference_code' => null,
                'iyzico_subscription_status' => null,
                // Onaylanana kadar platformda görünmesin / ödeme yok
                'aktif_mi' => true,
                'platformda_gorunur' => false,
            ]);

            $doktor->branslar()->attach($request->branslar);

            return $doktor;
        });

        Auth::guard('doktor')->login($doktor);

        session()->forget(['kayit_paket_id', 'kayit_periyot']);

        // Ödeme YOK — önce meslek belgesi onayı; paket zaten seçili (kayit_paket_id)
        return redirect()
            ->route('frontend.hekim.meslek.bekleme')
            ->with('basarili', 'Kaydınız alındı. Belgeleriniz onaylandıktan sonra seçtiğiniz paket için ödemeye geçeceksiniz.');
    }

    /**
     * Meslek belgesi onay bekleme ekranı + reddedildiyse yeniden yükleme.
     */
    public function meslekBekleme()
    {
        $doktor = Auth::guard('doktor')->user();
        if (! $doktor) {
            return redirect()->route('frontend.hekim.giris');
        }

        if ($doktor->canProceedToPayment()) {
            return redirect()->to($doktor->checkoutUrlAfterMeslek());
        }

        $doktor->loadMissing('kayitPaketi');

        return view('frontend.hekim.meslek_bekleme', compact('doktor'));
    }

    /**
     * Poll: meslek durumu JSON (bekleme ekranı auto-redirect).
     */
    public function meslekDurumJson()
    {
        $doktor = Auth::guard('doktor')->user();
        if (! $doktor) {
            return response()->json(['ok' => false], 401);
        }

        $doktor->refresh();

        return response()->json([
            'ok' => true,
            'durum' => $doktor->meslek_dogrulama_durumu ?? 'beklemede',
            'can_proceed' => $doktor->canProceedToPayment(),
            'redirect' => $doktor->canProceedToPayment()
                ? $doktor->checkoutUrlAfterMeslek()
                : null,
        ]);
    }

    /**
     * Hekim kendi meslek belgesini görüntüler (private storage).
     */
    public function meslekBelgeGoster()
    {
        $doktor = Auth::guard('doktor')->user();
        if (! $doktor) {
            return redirect()->route('frontend.hekim.giris');
        }

        $path = (string) ($doktor->meslek_belge_yolu ?? '');
        if ($path === '') {
            abort(404);
        }

        if (str_starts_with($path, 'private/') || str_starts_with($path, 'meslek-belgeleri/')) {
            $diskPath = str_starts_with($path, 'private/') ? $path : 'private/'.$path;
            if (! Storage::disk('local')->exists($diskPath) && Storage::disk('local')->exists($path)) {
                $diskPath = $path;
            }
            if (! Storage::disk('local')->exists($diskPath)) {
                abort(404);
            }

            return Storage::disk('local')->response($diskPath, basename($diskPath), [
                'Content-Type' => Storage::disk('local')->mimeType($diskPath) ?: 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="'.basename($diskPath).'"',
            ]);
        }

        $full = public_path(ltrim($path, '/'));
        if (is_file($full)) {
            return response()->file($full);
        }

        abort(404);
    }

    /**
     * Reddedilen / eksik belgeyi yeniden yükle.
     */
    public function meslekBelgeYenile(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();
        if (! $doktor) {
            return redirect()->route('frontend.hekim.giris');
        }

        if ($doktor->isMeslekOnayli()) {
            return redirect()->to($doktor->checkoutUrlAfterMeslek());
        }

        $request->validate([
            'tc_kimlik_no' => ['required', 'string', 'size:11', 'unique:doktorlar,tc_kimlik_no,'.$doktor->id, new TcKimlikNo],
            'diploma_no' => ['required', 'string', 'min:3', 'max:64'],
            'edevlet_barkod' => ['nullable', 'string', 'max:64', 'regex:/^[A-Za-z0-9\-]+$/'],
            'meslek_belgesi' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ], [
            'tc_kimlik_no.required' => 'T.C. kimlik numarası zorunludur.',
            'diploma_no.required' => 'Diploma / tescil numarası zorunludur.',
            'meslek_belgesi.required' => 'Belge yüklemeniz zorunludur.',
        ]);

        $tc = preg_replace('/\D/', '', (string) $request->tc_kimlik_no) ?? '';
        $belgeRel = $request->file('meslek_belgesi')->store(
            'private/meslek-belgeleri',
            'local'
        );

        $doktor->forceFill([
            'tc_kimlik_no' => $tc,
            'diploma_no' => trim((string) $request->diploma_no),
            'edevlet_barkod' => $request->filled('edevlet_barkod')
                ? strtoupper(trim((string) $request->edevlet_barkod))
                : null,
            'meslek_belge_yolu' => $belgeRel,
            'meslek_dogrulama_durumu' => 'beklemede',
            'meslek_dogrulama_notu' => null,
            'meslek_dogrulandi_at' => null,
            'meslek_dogrulayan_yonetici_id' => null,
        ])->save();

        return back()->with('basarili', 'Belgeleriniz yeniden gönderildi. İnceleme sonrası bilgilendirileceksiniz.');
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
     * Kayıtta paket zaten seçildiyse ödeme/domain'e yönlendir (?degistir=1 ile değiştirilebilir).
     */
    public function paketSecFormu(Request $request)
    {
        $doktorGate = Auth::guard('doktor')->user();
        if ($doktorGate && ! $doktorGate->canProceedToPayment()) {
            return redirect()
                ->route('frontend.hekim.meslek.bekleme')
                ->with('hata', 'Ödeme için önce meslek belgenizin onaylanması gerekir.');
        }

        $doktor = Auth::guard('doktor')->user();
        $doktor->loadMissing('kayitPaketi');

        // Kayıt niyeti varsa ve değiştirmek istemiyorsa tekrar seçtirme
        if ($doktor->hasKayitPaketNiyeti() && ! $request->boolean('degistir')) {
            return redirect()->to($doktor->checkoutUrlAfterMeslek());
        }

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
            'kayit_paket_id' => null,
            'kayit_periyot' => null,
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
        $doktorGate = Auth::guard('doktor')->user();
        if ($doktorGate && ! $doktorGate->canProceedToPayment()) {
            return redirect()
                ->route('frontend.hekim.meslek.bekleme')
                ->with('hata', 'Ödeme adımına geçmeden önce meslek belgenizin onaylanması gerekir.');
        }

        $paketId = $request->query('paket');
        $periyot = $request->query('periyot', 'aylik');

        $secilenPaket = Paket::where('aktif_mi', true)->with('sistemOzellikleri')->find($paketId);
        if (! $secilenPaket) {
            return redirect()->route('frontend.hekim.paket_sec')->with('hata', 'Lütfen geçerli bir paket seçin.');
        }

        // Ödeme adımında seçim = kayıt niyetini güncelle (paket değiştiyse)
        $doktor = Auth::guard('doktor')->user();
        if ($doktor && (! $doktor->hasKayitPaketNiyeti()
            || (int) $doktor->kayit_paket_id !== (int) $secilenPaket->id
            || $doktor->kayit_periyot !== $periyot)) {
            $doktor->forceFill([
                'kayit_paket_id' => $secilenPaket->id,
                'kayit_periyot' => $periyot,
            ])->save();
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
        $paytrAvailable = app(PaytrService::class)->isConfigured();
        $iyzicoAvailable = $paytrAvailable; // blade geriye uyum (kartlı ödeme var mı)
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
            'paytrAvailable',
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
        $doktorGate = Auth::guard('doktor')->user();
        if ($doktorGate && ! $doktorGate->canProceedToPayment()) {
            return redirect()
                ->route('frontend.hekim.meslek.bekleme')
                ->with('hata', 'Meslek belgesi onaylanmadan ödeme yapılamaz.');
        }

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
            'mesafeli_onay' => 'accepted',
            'kvkk_odeme_onay' => 'accepted',
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
            $rules['odeme_yontemi'] = 'required|in:paytr,iyzico,havale';
        }

        $request->validate($rules, [
            'paket_id.exists' => 'Lütfen geçerli bir üyelik paketi seçin.',
            'odeme_periyodu.in' => 'Ödeme periyodu aylık veya yıllık olmalıdır.',
            'klinik_adi.required' => 'Klinik adı zorunludur.',
            'telefon.required' => 'Klinik telefon numarası zorunludur.',
            'adres.required' => 'Klinik adresi zorunludur.',
            'il_id.required' => 'İl seçimi zorunludur.',
            'ilce_id.required' => 'İlçe seçimi zorunludur.',
            'mesafeli_onay.accepted' => 'Mesafeli satış sözleşmesini kabul etmelisiniz.',
            'kvkk_odeme_onay.accepted' => 'KVKK aydınlatma metnini kabul etmelisiniz.',
        ]);

        // Eski formlar iyzico gönderebilir → paytr kabul et
        $odemeYontemi = $request->input('odeme_yontemi', 'paytr');
        if ($odemeYontemi === 'iyzico') {
            $odemeYontemi = 'paytr';
        }

        $paymentSettings = SiteAyari::query()->first();

        if ($isFree) {
            $paymentResult = [
                'status' => 'success',
                'referenceCode' => 'free_trial_'.Str::random(12),
                'subscriptionStatus' => 'ACTIVE',
            ];
        } elseif ($odemeYontemi === 'havale') {
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
                'provider' => 'banka',
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
            // PayTR iFrame — kart formu sitede yok; güvenli iframe
            $paytr = app(PaytrService::class);
            if (! $paytr->isConfigured()) {
                return back()->withInput()->withErrors(['odeme_yontemi' => 'Kartlı ödeme (PayTR) şu anda kullanıma açık değil.']);
            }

            $merchantOid = $paytr->makeMerchantOid();
            UyelikOdeme::create([
                'doktor_id' => $doktor->id,
                'paket_id' => $paket->id,
                'odeme_yontemi' => 'paytr',
                'provider' => 'paytr',
                'odeme_periyodu' => $request->odeme_periyodu,
                'tutar' => $tutar,
                'durum' => 'beklemede',
                'merchant_oid' => $merchantOid,
                'kurulum_verisi' => $paket->klinikPaketiMi() ? $request->only([
                    'klinik_adi',
                    'telefon',
                    'e_posta',
                    'adres',
                    'il_id',
                    'ilce_id',
                ]) : null,
            ]);

            $tokenResult = $paytr->createIframeToken([
                'merchant_oid' => $merchantOid,
                'email' => (string) $doktor->e_posta,
                'payment_amount' => $tutar,
                'user_name' => (string) $doktor->ad_soyad,
                'user_address' => (string) ($doktor->adres ?: ($doktor->il?->ad ?? 'Turkiye')),
                'user_phone' => (string) $doktor->telefon,
                'user_ip' => $request->ip(),
                'basket_name' => 'Randevu Ajandam - '.$paket->ad.' ('.$request->odeme_periyodu.')',
            ]);

            if (($tokenResult['status'] ?? '') !== 'success') {
                UyelikOdeme::where('merchant_oid', $merchantOid)->update(['durum' => 'reddedildi']);

                return back()->withInput()->withErrors([
                    'odeme_yontemi' => $tokenResult['errorMessage'] ?? 'PayTR ödeme oturumu açılamadı.',
                ]);
            }

            session(['paytr_iframe_token_'.$merchantOid => $tokenResult['token']]);

            return redirect()->route('frontend.odeme.paytr.iframe', ['merchantOid' => $merchantOid]);
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
                    'kayit_paket_id' => null,
                    'kayit_periyot' => null,
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
                    'kayit_paket_id' => null,
                    'kayit_periyot' => null,
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
