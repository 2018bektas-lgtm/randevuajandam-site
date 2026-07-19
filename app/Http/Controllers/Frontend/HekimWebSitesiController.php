<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Services\DomainInclusionService;
use App\Services\WebsiteProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class HekimWebSitesiController extends Controller
{
    public function __construct(
        protected DomainInclusionService $domains,
        protected WebsiteProvisioningService $provisioning,
    ) {}

    public function kurulumFormu()
    {
        $doktor = Auth::guard('doktor')->user();
        $webSite = $doktor->webSite;
        $apiKey = ApiKey::query()->where('doktor_id', $doktor->id)->first();
        $plainSecret = session('plain_api_secret');
        $canHide = $doktor->canHideFromPlatform();
        $platformdaGorunur = (bool) ($doktor->platformda_gorunur ?? true);
        $domainEligibility = $this->publicEligibility($doktor);

        return view('hekim.web_site.kurulum', compact(
            'webSite',
            'apiKey',
            'plainSecret',
            'canHide',
            'platformdaGorunur',
            'doktor',
            'domainEligibility'
        ));
    }

    public function domainCheck(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();
        $data = $request->validate([
            'sld' => ['required', 'string', 'min:2', 'max:63'],
            'tlds' => ['nullable', 'array'],
            'tlds.*' => ['string', 'max:20'],
        ]);

        try {
            $results = $this->domains->check($doktor, $data['sld'], $data['tlds'] ?? null);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'results' => $results,
                'eligibility' => $this->publicEligibility($doktor),
            ],
        ]);
    }

    /**
     * Domain + web sitesi + API key tek adımda (pakete dahil / BYOD).
     */
    public function domainClaim(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();
        $data = $request->validate([
            'domain' => ['required', 'string', 'max:150'],
            'mode' => ['nullable', 'in:included,byod'],
        ]);

        $mode = $data['mode'] ?? 'included';

        try {
            $result = $this->provisioning->provisionDoktor($doktor, $data['domain'], $mode);
        } catch (RuntimeException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('hata', $e->getMessage())->withInput();
        }

        $msg = $mode === 'byod'
            ? 'Web siteniz bağlandı (kendi domaininiz). DNS + Hostinger kurulumunu tamamlayın. Secret key bir kez gösterilir.'
            : 'Domain pakete dahil kaydedildi ve web sitesi otomatik açıldı (1 yıl, ek ücret yok). Secret key bir kez gösterilir.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'data' => [
                    'domain' => $result['domain'],
                    'created_site' => $result['created_site'],
                ],
            ]);
        }

        return redirect()
            ->route('hekim.web-sitesi.kurulum')
            ->with('basarili', $msg)
            ->with('plain_api_secret', $result['plain_secret']);
    }

    public function platformGorunurluk(Request $request)
    {
        /** @var \App\Models\Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        if (! $doktor->canHideFromPlatform()) {
            return redirect()->back()->with(
                'hata',
                'Ana sitede gizlenme yalnızca Özel Web Sitesi paketinde kullanılabilir.'
            );
        }

        $request->validate([
            'platformda_gorunur' => ['nullable', 'boolean'],
        ]);

        $gorunur = $request->boolean('platformda_gorunur');
        $doktor->forceFill(['platformda_gorunur' => $gorunur])->save();

        return redirect()->back()->with(
            'basarili',
            $gorunur
                ? 'Profiliniz Randevu Ajandam arama sonuçlarında yeniden listeleniyor.'
                : 'Profiliniz ana platform vitrininden gizlendi. Panel ve kendi web siteniz çalışmaya devam eder.'
        );
    }

    /**
     * BYOD / manuel domain — otomatik provision (byod).
     */
    public function kurulumYap(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();

        if ($doktor->webSite) {
            return redirect()->back()->with('hata', 'Zaten tanımlı bir web siteniz bulunuyor.');
        }

        $request->validate([
            'domain' => 'required|string|max:100',
        ], [
            'domain.required' => 'Lütfen web sitenizin alan adını (domain) girin. Örn: doktoradi.com',
        ]);

        try {
            $result = $this->provisioning->provisionDoktor($doktor, $request->input('domain'), 'byod');
        } catch (RuntimeException $e) {
            return redirect()->back()->with('hata', $e->getMessage())->withInput();
        }

        return redirect()
            ->route('hekim.web-sitesi.kurulum')
            ->with('basarili', 'Web siteniz otomatik kuruldu. Secret key yalnızca bir kez gösterilir — hemen kopyalayın.')
            ->with('plain_api_secret', $result['plain_secret']);
    }

    public function apiAnahtariOlustur()
    {
        $doktor = Auth::guard('doktor')->user();
        if (! $doktor->webSite) {
            return redirect()->back()->with('hata', 'Önce domain / web sitesi kurulumunu tamamlayın.');
        }

        $apiKey = 'rk_'.strtolower(\Illuminate\Support\Str::random(30));
        $secretKey = strtolower(\Illuminate\Support\Str::random(60));

        ApiKey::issue([
            'doktor_id' => $doktor->id,
            'klinik_id' => null,
            'api_key' => $apiKey,
            'durum' => true,
            'yetkiler' => ['*'],
        ], $secretKey);

        \Illuminate\Support\Facades\DB::table('webhook_endpoints')
            ->where('doktor_id', $doktor->id)
            ->update([
                'secret_key' => $secretKey,
                'updated_at' => now(),
            ]);

        return redirect()
            ->back()
            ->with('basarili', 'API anahtarları yenilendi. Secret key yalnızca bu sefer görünür — kopyalayın.')
            ->with('plain_api_secret', $secretKey);
    }

    protected function publicEligibility($doktor): array
    {
        $e = $this->domains->eligibility($doktor);

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
}
