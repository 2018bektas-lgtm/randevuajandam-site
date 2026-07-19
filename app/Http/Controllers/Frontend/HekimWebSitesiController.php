<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\HekimWebSitesi;
use App\Services\DomainInclusionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class HekimWebSitesiController extends Controller
{
    public function __construct(protected DomainInclusionService $domains) {}

    public function kurulumFormu()
    {
        $doktor = Auth::guard('doktor')->user();
        $webSite = $doktor->webSite;
        $apiKey = ApiKey::query()->where('doktor_id', $doktor->id)->first();
        $plainSecret = session('plain_api_secret');
        $canHide = $doktor->canHideFromPlatform();
        $platformdaGorunur = (bool) ($doktor->platformda_gorunur ?? true);
        $domainEligibility = $this->domains->eligibility($doktor);

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

    /**
     * Pakete dahil domain müsaitlik (Hostinger).
     */
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
     * Pakete dahil domain kaydı (Hostinger purchase — ek ücret yok).
     */
    public function domainClaim(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();
        $data = $request->validate([
            'domain' => ['required', 'string', 'max:150'],
            'mode' => ['nullable', 'in:included,byod'],
        ]);

        $mode = $data['mode'] ?? 'included';
        $domain = $this->domains->normalizeDomain($data['domain']);

        try {
            $order = $mode === 'byod'
                ? $this->domains->claimByod($doktor, $domain)
                : $this->domains->claimIncluded($doktor, $domain);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage())->withInput();
        }

        // Web sitesi kaydı yoksa oluştur
        if (! $doktor->webSite) {
            $request->merge(['domain' => $order->domain]);

            return $this->kurulumYap($request)
                ->with('basarili', $mode === 'byod'
                    ? 'Domain bağlandı (kendi alan adınız). DNS adımlarını tamamlayın.'
                    : 'Domain pakete dahil olarak kaydedildi (1 yıl). Ek ücret yok.');
        }

        $doktor->webSite->forceFill([
            'domain' => $order->domain,
            'hostinger_domain_id' => $order->hostinger_order_id,
        ])->save();

        return redirect()
            ->route('hekim.web-sitesi.kurulum')
            ->with('basarili', $mode === 'byod'
                ? 'Domain güncellendi. DNS adımlarını tamamlayın.'
                : 'Domain pakete dahil kaydedildi.');
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

    /**
     * Ana platform vitrininde görünürlük (yalnızca web_sitesi paketi).
     */
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

        // Checkbox: yoksa false
        $gorunur = $request->boolean('platformda_gorunur');

        $doktor->forceFill([
            'platformda_gorunur' => $gorunur,
        ])->save();

        return redirect()->back()->with(
            'basarili',
            $gorunur
                ? 'Profiliniz Randevu Ajandam arama sonuçlarında yeniden listeleniyor.'
                : 'Profiliniz ana platform vitrininden gizlendi. Panel ve kendi web siteniz çalışmaya devam eder.'
        );
    }

    public function kurulumYap(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();

        if ($doktor->webSite) {
            return redirect()->back()->with('hata', 'Zaten tanımlı bir web siteniz bulunuyor.');
        }

        $request->validate([
            'domain' => 'required|string|max:100|unique:hekim_web_siteleri,domain',
        ], [
            'domain.required' => 'Lütfen web sitenizin alan adını (domain) girin. Örn: doktoradi.com',
            'domain.unique' => 'Bu alan adı daha önce başka bir hekim tarafından kaydedilmiş.',
        ]);

        $domain = strtolower(trim($request->domain));
        $domain = preg_replace('#^https?://(www\.)?#', '', $domain);
        $domain = rtrim($domain, '/');

        if (empty($domain)) {
            return redirect()->back()->with('hata', 'Geçersiz alan adı girdiniz.');
        }

        HekimWebSitesi::create([
            'doktor_id' => $doktor->id,
            'domain' => $domain,
            'tema' => 'custom',
            'durum' => 'aktif',
        ]);

        $apiKeyVal = 'rk_'.strtolower(Str::random(30));
        $secretKeyVal = strtolower(Str::random(60));

        ApiKey::issue([
            'doktor_id' => $doktor->id,
            'klinik_id' => null,
            'api_key' => $apiKeyVal,
            'durum' => true,
            'yetkiler' => ['*'],
        ], $secretKeyVal);

        $webhookUrl = $this->buildWebhookUrl($domain);

        DB::table('webhook_endpoints')->updateOrInsert(
            ['doktor_id' => $doktor->id],
            [
                'url' => $webhookUrl,
                'secret_key' => $secretKeyVal, // webhook HMAC needs plain
                'events' => json_encode(['*']),
                'aktif' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return redirect()
            ->route('hekim.web-sitesi.kurulum')
            ->with('basarili', 'Kişisel alan adınız kaydedildi. Secret key yalnızca bir kez gösterilir — hemen kopyalayın.')
            ->with('plain_api_secret', $secretKeyVal);
    }

    public function apiAnahtariOlustur()
    {
        $doktor = Auth::guard('doktor')->user();

        $apiKey = 'rk_'.strtolower(Str::random(30));
        $secretKey = strtolower(Str::random(60));

        ApiKey::issue([
            'doktor_id' => $doktor->id,
            'klinik_id' => null,
            'api_key' => $apiKey,
            'durum' => true,
            'yetkiler' => ['*'],
        ], $secretKey);

        DB::table('webhook_endpoints')
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

    protected function buildWebhookUrl(string $domain): string
    {
        $webhookUrl = rtrim((string) $domain, '/');
        if (! str_starts_with($webhookUrl, 'http://') && ! str_starts_with($webhookUrl, 'https://')) {
            $scheme = app()->environment('production') ? 'https://' : 'http://';
            if (! str_contains($webhookUrl, '.') && ! str_contains($webhookUrl, 'localhost')) {
                $webhookUrl = $scheme.'localhost/'.$webhookUrl;
            } else {
                $webhookUrl = $scheme.$webhookUrl;
            }
        }
        if (! str_ends_with($webhookUrl, '/webhook/receiver')) {
            $webhookUrl = rtrim($webhookUrl, '/').'/webhook/receiver';
        }
        if (app()->environment('production') && str_starts_with($webhookUrl, 'http://')) {
            $webhookUrl = 'https://'.substr($webhookUrl, 7);
        }

        return $webhookUrl;
    }
}
