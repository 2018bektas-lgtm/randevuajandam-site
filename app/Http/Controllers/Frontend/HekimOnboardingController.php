<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Paket;
use App\Services\DomainInclusionService;
use App\Services\WebsiteProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * Domain seçimi:
 * - pre_payment: paket seçimi → domain → ödeme (önerilen)
 * - post_payment: eski üyeler / atlananlar için sonradan kurulum
 */
class HekimOnboardingController extends Controller
{
    public const SESSION_PENDING = 'pending_domain';

    public function __construct(
        protected DomainInclusionService $domains,
        protected WebsiteProvisioningService $provisioning,
    ) {}

    public function domain(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();
        if (! $doktor) {
            return redirect()->route('frontend.hekim.giris');
        }

        $paketId = $request->query('paket');
        $periyot = $request->query('periyot', 'aylik');
        if (! in_array($periyot, ['aylik', 'yillik'], true)) {
            $periyot = 'aylik';
        }

        // --- PRE-PAYMENT: paket + periyot query ---
        if ($paketId) {
            $paket = Paket::where('aktif_mi', true)->with('sistemOzellikleri')->find($paketId);
            if (! $paket) {
                return redirect()->route('frontend.hekim.paket_sec')->with('hata', 'Lütfen geçerli bir paket seçin.');
            }
            if (! $this->packageNeedsDomain($paket)) {
                return redirect()->route('frontend.hekim.paket_ode', [
                    'paket' => $paket->id,
                    'periyot' => $periyot,
                ]);
            }

            $target = $paket->hasFeature('klinik_web_sitesi') || $paket->klinikPaketiMi()
                ? 'clinic'
                : 'doctor';

            $eligibility = $this->eligibilityForSelectedPackage($paket);
            $steps = $this->wizardSteps('pre', $target);

            return view('frontend.hekim.onboarding_domain', [
                'doktor' => $doktor,
                'target' => $target,
                'phase' => 'pre_payment',
                'secilenPaket' => $paket,
                'periyot' => $periyot,
                'eligibility' => $eligibility,
                'steps' => $steps,
                'checkUrl' => route('frontend.hekim.onboarding.domain.check'),
                'pending' => session(self::SESSION_PENDING),
            ]);
        }

        // --- POST-PAYMENT: aktif üyelik, site yok ---
        $target = $doktor->websiteOnboardingTarget();
        if (! $target) {
            return redirect()->route('frontend.hekim.basarili');
        }

        $owner = $target === 'clinic' ? $doktor->klinik : $doktor;
        $eligibility = $this->publicEligibility($owner);
        $steps = $this->wizardSteps('post', $target);

        session(['onboarding_domain' => true, 'onboarding_target' => $target]);

        return view('frontend.hekim.onboarding_domain', [
            'doktor' => $doktor,
            'target' => $target,
            'phase' => 'post_payment',
            'secilenPaket' => $doktor->aktifPaket(),
            'periyot' => $doktor->odeme_periyodu ?? 'aylik',
            'eligibility' => $eligibility,
            'steps' => $steps,
            'checkUrl' => $target === 'clinic'
                ? route('hekim.klinik.web-sitesi.domain.check')
                : route('hekim.web-sitesi.domain.check'),
            'pending' => null,
        ]);
    }

    /**
     * Ödeme öncesi Hostinger müsaitlik (seçilen paket).
     */
    public function domainCheck(Request $request)
    {
        $data = $request->validate([
            'sld' => ['required', 'string', 'min:2', 'max:63'],
            'paket_id' => ['required', 'integer', 'exists:paketler,id'],
            'tld' => ['nullable', 'string', 'max:20'],
            'tlds' => ['nullable', 'array'],
            'tlds.*' => ['string', 'max:20'],
        ]);

        $paket = Paket::where('aktif_mi', true)->findOrFail($data['paket_id']);
        if (! $this->packageNeedsDomain($paket)) {
            return response()->json(['success' => false, 'message' => 'Bu pakette domain adımı yok.'], 422);
        }

        $allowed = $this->domains->tldsForPackage($paket);
        $tlds = $data['tlds'] ?? null;
        if (! empty($data['tld'])) {
            $tlds = [strtolower(ltrim($data['tld'], '.'))];
        }
        if (is_array($tlds) && $tlds !== []) {
            $tlds = array_values(array_filter(array_map(
                fn ($t) => strtolower(ltrim((string) $t, '.')),
                $tlds
            ), fn ($t) => in_array($t, $allowed, true)));
            if ($tlds === []) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seçilen uzantı pakete dahil değil. İzin verilen: .'.implode(', .', $allowed),
                ], 422);
            }
        }

        try {
            $results = $this->domains->checkByPackage($paket, $data['sld'], $tlds);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'results' => $results,
                'eligibility' => $this->eligibilityForSelectedPackage($paket),
            ],
        ]);
    }

    /**
     * Ödeme öncesi: domain tercihini session'a yaz → ödeme sayfası.
     */
    public function domainSave(Request $request)
    {
        $data = $request->validate([
            'paket_id' => ['required', 'integer', 'exists:paketler,id'],
            'periyot' => ['required', 'in:aylik,yillik'],
            'mode' => ['required', 'in:included,byod'],
            'domain' => ['required', 'string', 'max:150'],
        ]);

        $paket = Paket::where('aktif_mi', true)->findOrFail($data['paket_id']);
        if (! $this->packageNeedsDomain($paket)) {
            return redirect()->route('frontend.hekim.paket_ode', [
                'paket' => $paket->id,
                'periyot' => $data['periyot'],
            ]);
        }

        $domain = $this->domains->normalizeDomain($data['domain']);
        if ($domain === '' || ! str_contains($domain, '.')) {
            return back()->with('hata', 'Geçerli bir alan adı girin (ör. dr-ahmet.com).')->withInput();
        }

        if ($data['mode'] === 'included') {
            $elig = $this->eligibilityForSelectedPackage($paket);
            if (! $elig['eligible']) {
                return back()->with('hata', $elig['reason'] ?? 'Pakete dahil domain bu paket için yok.')->withInput();
            }
            $tld = $this->extractTld($domain);
            if ($tld === '' || ! in_array($tld, $elig['tlds'], true)) {
                return back()->with(
                    'hata',
                    'Pakete dahil uzantılar: .'.implode(', .', $elig['tlds'])
                )->withInput();
            }
        }

        session([
            self::SESSION_PENDING => [
                'mode' => $data['mode'],
                'domain' => $domain,
                'paket_id' => (int) $paket->id,
                'periyot' => $data['periyot'],
                'target' => ($paket->hasFeature('klinik_web_sitesi') || $paket->klinikPaketiMi()) ? 'clinic' : 'doctor',
                'saved_at' => now()->toIso8601String(),
            ],
            'onboarding_domain_skipped' => false,
        ]);

        return redirect()->route('frontend.hekim.paket_ode', [
            'paket' => $paket->id,
            'periyot' => $data['periyot'],
        ])->with('basarili', 'Domain seçildi: '.$domain.' — ödeme sonrası otomatik kurulacak.');
    }

    /**
     * Ödeme öncesi: domain atla → yine ödeme (sonra panelden).
     */
    public function domainSkipPre(Request $request)
    {
        $data = $request->validate([
            'paket_id' => ['required', 'integer', 'exists:paketler,id'],
            'periyot' => ['required', 'in:aylik,yillik'],
        ]);

        session()->forget(self::SESSION_PENDING);
        session(['onboarding_domain_skipped' => true]);

        return redirect()->route('frontend.hekim.paket_ode', [
            'paket' => $data['paket_id'],
            'periyot' => $data['periyot'],
        ])->with('basarili', 'Domain adımını atladınız. Ödeme sonrası panelden kurabilirsiniz.');
    }

    /**
     * Kendi domaini (BYOD) — post-payment.
     */
    public function domainByod(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();
        $target = $doktor->websiteOnboardingTarget() ?? session('onboarding_target', 'doctor');

        $request->validate([
            'domain' => ['required', 'string', 'max:150'],
        ], [
            'domain.required' => 'Alan adınızı yazın (ör. dr-ahmet.com).',
        ]);

        try {
            if ($target === 'clinic' && $doktor->klinik) {
                $result = $this->provisioning->provisionKlinik($doktor->klinik, $request->input('domain'), 'byod');
            } else {
                $result = $this->provisioning->provisionDoktor($doktor, $request->input('domain'), 'byod');
            }
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage())->withInput();
        }

        session()->forget(['onboarding_domain', 'onboarding_target', self::SESSION_PENDING]);

        return redirect()
            ->route('frontend.hekim.basarili')
            ->with('basarili', 'Domain bağlandı. Kendi DNS yönlendirmenizi tamamlayın. Secret key bir kez gösterilir.')
            ->with('plain_api_secret', $result['plain_secret'])
            ->with('onboarding_domain_done', $result['domain']);
    }

    /**
     * Pakete dahil domain claim — post-payment.
     */
    public function domainClaim(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();
        $target = $doktor->websiteOnboardingTarget() ?? session('onboarding_target', 'doctor');

        $data = $request->validate([
            'domain' => ['required', 'string', 'max:150'],
        ]);

        try {
            if ($target === 'clinic' && $doktor->klinik) {
                $result = $this->provisioning->provisionKlinik($doktor->klinik, $data['domain'], 'included');
            } else {
                $result = $this->provisioning->provisionDoktor($doktor, $data['domain'], 'included');
            }
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage())->withInput();
        }

        session()->forget(['onboarding_domain', 'onboarding_target', self::SESSION_PENDING]);

        return redirect()
            ->route('frontend.hekim.basarili')
            ->with('basarili', 'Domain pakete dahil kaydedildi ve web siteniz açıldı (ek ücret yok). Secret key bir kez gösterilir.')
            ->with('plain_api_secret', $result['plain_secret'])
            ->with('onboarding_domain_done', $result['domain']);
    }

    /**
     * Domain adımını ertele — post-payment.
     */
    public function domainSkip()
    {
        session()->forget(['onboarding_domain', 'onboarding_target']);
        session(['onboarding_domain_skipped' => true]);

        return redirect()
            ->route('frontend.hekim.basarili')
            ->with('basarili', 'Domain kurulumunu daha sonra panelden tamamlayabilirsiniz: Web Sitesi → Kurulum.');
    }

    public static function packageNeedsDomain(Paket $paket): bool
    {
        return $paket->hasFeature('web_sitesi')
            || $paket->hasFeature('klinik_web_sitesi')
            || (bool) ($paket->domain_dahil_mi ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    protected function eligibilityForSelectedPackage(Paket $paket): array
    {
        $tlds = $this->domains->tldsForPackage($paket);
        $domainDahil = (bool) ($paket->domain_dahil_mi ?? false)
            || $paket->hasFeature('web_sitesi')
            || $paket->hasFeature('klinik_web_sitesi');

        // Ödeme öncesi: domain_dahil_mi yoksa bile web paketi BYOD yapabilir; included sadece domain_dahil
        $includedOk = (bool) ($paket->domain_dahil_mi ?? false);

        return [
            'eligible' => $includedOk,
            'reason' => $includedOk
                ? null
                : ($domainDahil
                    ? 'Bu pakette yeni domain hakkı kapalı; kendi domaininizi bağlayabilirsiniz.'
                    : 'Domain bu pakette yok.'),
            'tlds' => $tlds,
            'yil' => (int) ($paket->domain_dahil_yil ?? 1),
            'already_claimed' => false,
            'active_domain' => null,
            'hostinger_ready' => (bool) config('hostinger.enabled') && (string) config('hostinger.api_token') !== '',
            'paket_ad' => $paket->ad,
        ];
    }

    /**
     * @param  \App\Models\Doktor|\App\Models\Klinik  $owner
     * @return array<string, mixed>
     */
    protected function publicEligibility($owner): array
    {
        $e = $this->domains->eligibility($owner);

        return [
            'eligible' => $e['eligible'],
            'reason' => $e['reason'],
            'tlds' => $e['tlds'],
            'yil' => $e['yil'],
            'already_claimed' => $e['already_claimed'],
            'active_domain' => $e['active_domain'],
            'hostinger_ready' => $e['hostinger_ready'],
            'paket_ad' => $e['paket']?->ad,
        ];
    }

    /**
     * @return list<array{key:string,label:string,status:string}>
     */
    protected function wizardSteps(string $phase, string $target): array
    {
        $domainLabel = $target === 'clinic' ? 'Klinik domain' : 'Site domaini';

        if ($phase === 'pre') {
            return [
                ['key' => 'hesap', 'label' => 'Hesap', 'status' => 'done'],
                ['key' => 'paket', 'label' => 'Paket', 'status' => 'done'],
                ['key' => 'domain', 'label' => $domainLabel, 'status' => 'current'],
                ['key' => 'odeme', 'label' => 'Ödeme', 'status' => 'todo'],
                ['key' => 'tamam', 'label' => 'Tamam', 'status' => 'todo'],
            ];
        }

        return [
            ['key' => 'hesap', 'label' => 'Hesap', 'status' => 'done'],
            ['key' => 'paket', 'label' => 'Paket', 'status' => 'done'],
            ['key' => 'odeme', 'label' => 'Ödeme', 'status' => 'done'],
            ['key' => 'domain', 'label' => $domainLabel, 'status' => 'current'],
            ['key' => 'tamam', 'label' => 'Tamam', 'status' => 'todo'],
        ];
    }

    protected function extractTld(string $domain): string
    {
        $parts = explode('.', strtolower($domain));
        if (count($parts) < 2) {
            return '';
        }
        if (count($parts) >= 3 && end($parts) === 'tr') {
            return $parts[count($parts) - 2].'.tr';
        }

        return (string) end($parts);
    }
}
