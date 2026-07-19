<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\DomainInclusionService;
use App\Services\WebsiteProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * Kayıt sonrası aşamalı kurulum: domain seçimi (pakete dahil / BYOD / atla).
 */
class HekimOnboardingController extends Controller
{
    public function __construct(
        protected DomainInclusionService $domains,
        protected WebsiteProvisioningService $provisioning,
    ) {}

    public function domain()
    {
        $doktor = Auth::guard('doktor')->user();
        if (! $doktor) {
            return redirect()->route('frontend.hekim.giris');
        }

        $target = $doktor->websiteOnboardingTarget();
        if (! $target) {
            return redirect()->route('frontend.hekim.basarili');
        }

        $owner = $target === 'clinic' ? $doktor->klinik : $doktor;
        $eligibility = $this->publicEligibility($owner);
        $steps = $this->wizardSteps($doktor, $target);

        session(['onboarding_domain' => true, 'onboarding_target' => $target]);

        return view('frontend.hekim.onboarding_domain', [
            'doktor' => $doktor,
            'target' => $target,
            'eligibility' => $eligibility,
            'steps' => $steps,
            'checkUrl' => $target === 'clinic'
                ? route('hekim.klinik.web-sitesi.domain.check')
                : route('hekim.web-sitesi.domain.check'),
            'claimUrl' => $target === 'clinic'
                ? route('hekim.klinik.web-sitesi.domain.claim')
                : route('hekim.web-sitesi.domain.claim'),
        ]);
    }

    /**
     * Kendi domaini (BYOD) — kayıt sihirbazından.
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

        session()->forget(['onboarding_domain', 'onboarding_target']);

        return redirect()
            ->route('frontend.hekim.basarili')
            ->with('basarili', 'Domain bağlandı. Kendi DNS yönlendirmenizi tamamlayın. Secret key bir kez gösterilir.')
            ->with('plain_api_secret', $result['plain_secret'])
            ->with('onboarding_domain_done', $result['domain']);
    }

    /**
     * Pakete dahil domain claim (form POST — JSON değil).
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

        session()->forget(['onboarding_domain', 'onboarding_target']);

        return redirect()
            ->route('frontend.hekim.basarili')
            ->with('basarili', 'Domain pakete dahil kaydedildi ve web siteniz açıldı (ek ücret yok). Secret key bir kez gösterilir.')
            ->with('plain_api_secret', $result['plain_secret'])
            ->with('onboarding_domain_done', $result['domain']);
    }

    /**
     * Domain adımını ertele — panele / başarı sayfasına geç.
     */
    public function domainSkip()
    {
        $doktor = Auth::guard('doktor')->user();
        session()->forget(['onboarding_domain', 'onboarding_target']);
        session(['onboarding_domain_skipped' => true]);

        return redirect()
            ->route('frontend.hekim.basarili')
            ->with('basarili', 'Domain kurulumunu daha sonra panelden tamamlayabilirsiniz: Web Sitesi → Kurulum.');
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
    protected function wizardSteps($doktor, string $target): array
    {
        return [
            ['key' => 'hesap', 'label' => 'Hesap', 'status' => 'done'],
            ['key' => 'paket', 'label' => 'Paket', 'status' => 'done'],
            ['key' => 'odeme', 'label' => 'Ödeme', 'status' => 'done'],
            [
                'key' => 'domain',
                'label' => $target === 'clinic' ? 'Klinik domain' : 'Site domaini',
                'status' => 'current',
            ],
            ['key' => 'tamam', 'label' => 'Tamam', 'status' => 'todo'],
        ];
    }
}
