<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Services\DomainInclusionService;
use App\Services\WebsiteProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class KlinikWebSitesiController extends Controller
{
    public function __construct(
        protected DomainInclusionService $domains,
        protected WebsiteProvisioningService $provisioning,
    ) {}

    protected function klinikOrAbort()
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor?->klinik;

        abort_unless($klinik, 403);
        abort_unless($doktor->klinikSahibiMi() || $doktor->hasClinicPermission('klinik_ayarlari'), 403);

        if (! $klinik->hasWebSitesiFeature()) {
            abort(403, 'Klinik web sitesi yalnızca Klinik Kurumsal paketinde sunulur.');
        }

        return [$doktor, $klinik];
    }

    public function kurulumFormu()
    {
        [, $klinik] = $this->klinikOrAbort();

        $webSite = $klinik->webSite;
        $apiKey = ApiKey::query()->where('klinik_id', $klinik->id)->first();
        $plainSecret = session('plain_api_secret');
        $domainEligibility = $this->publicEligibility($klinik);

        return view('klinik.web_site.kurulum', compact('klinik', 'webSite', 'apiKey', 'plainSecret', 'domainEligibility'));
    }

    public function domainCheck(Request $request)
    {
        [, $klinik] = $this->klinikOrAbort();
        $data = $request->validate([
            'sld' => ['required', 'string', 'min:2', 'max:63'],
            'tlds' => ['nullable', 'array'],
            'tlds.*' => ['string', 'max:20'],
        ]);

        try {
            $results = $this->domains->check($klinik, $data['sld'], $data['tlds'] ?? null);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'results' => $results,
                'eligibility' => $this->publicEligibility($klinik),
            ],
        ]);
    }

    public function domainClaim(Request $request)
    {
        [, $klinik] = $this->klinikOrAbort();
        $data = $request->validate([
            'domain' => ['required', 'string', 'max:150'],
            'mode' => ['nullable', 'in:included,byod'],
        ]);

        $mode = $data['mode'] ?? 'included';

        try {
            $result = $this->provisioning->provisionKlinik($klinik, $data['domain'], $mode);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage())->withInput();
        }

        $msg = $mode === 'byod'
            ? 'Klinik web sitesi bağlandı (kendi domain). DNS adımlarını tamamlayın. Secret key bir kez gösterilir.'
            : 'Domain pakete dahil kaydedildi ve klinik web sitesi otomatik açıldı (1 yıl, ek ücret yok).';

        return redirect()
            ->route('hekim.klinik.web-sitesi.kurulum')
            ->with('basari', $msg)
            ->with('plain_api_secret', $result['plain_secret']);
    }

    public function kurulumYap(Request $request)
    {
        [, $klinik] = $this->klinikOrAbort();

        if ($klinik->webSite) {
            return redirect()->back()->with('hata', 'Zaten tanımlı bir klinik web sitesi bulunuyor.');
        }

        $request->validate([
            'domain' => 'required|string|max:150',
        ], [
            'domain.required' => 'Alan adı zorunludur. Örn: kliniginiz.com',
        ]);

        try {
            $result = $this->provisioning->provisionKlinik($klinik, $request->input('domain'), 'byod');
        } catch (RuntimeException $e) {
            return redirect()->back()->with('hata', $e->getMessage())->withInput();
        }

        return redirect()
            ->route('hekim.klinik.web-sitesi.kurulum')
            ->with('basari', 'Klinik web sitesi otomatik kuruldu. Secret key yalnızca bir kez gösterilir.')
            ->with('plain_api_secret', $result['plain_secret']);
    }

    public function apiAnahtariYenile(Request $request)
    {
        [, $klinik] = $this->klinikOrAbort();

        if (! $klinik->webSite) {
            return redirect()->back()->with('hata', 'Önce domain tanımlayın.');
        }

        $apiKeyVal = 'rk_'.strtolower(Str::random(30));
        $secretKeyVal = strtolower(Str::random(60));

        ApiKey::issue([
            'klinik_id' => $klinik->id,
            'doktor_id' => null,
            'api_key' => $apiKeyVal,
            'durum' => true,
            'yetkiler' => ['*'],
        ], $secretKeyVal);

        DB::table('webhook_endpoints')
            ->where('klinik_id', $klinik->id)
            ->update([
                'secret_key' => $secretKeyVal,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('hekim.klinik.web-sitesi.kurulum')
            ->with('basari', 'API anahtarları yenilendi. Secret key yalnızca bu sefer görünür.')
            ->with('plain_api_secret', $secretKeyVal);
    }

    protected function publicEligibility($klinik): array
    {
        $e = $this->domains->eligibility($klinik);

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
