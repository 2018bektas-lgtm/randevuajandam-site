<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\KlinikWebSitesi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KlinikWebSitesiController extends Controller
{
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

        return view('klinik.web_site.kurulum', compact('klinik', 'webSite', 'apiKey', 'plainSecret'));
    }

    public function kurulumYap(Request $request)
    {
        [, $klinik] = $this->klinikOrAbort();

        if ($klinik->webSite) {
            return redirect()->back()->with('hata', 'Zaten tanımlı bir klinik web sitesi bulunuyor.');
        }

        $request->validate([
            'domain' => 'required|string|max:150|unique:klinik_web_siteleri,domain',
        ], [
            'domain.required' => 'Alan adı zorunludur. Örn: kliniginiz.com',
            'domain.unique' => 'Bu alan adı başka bir klinik tarafından kullanılıyor.',
        ]);

        $domain = strtolower(trim($request->domain));
        $domain = preg_replace('#^https?://(www\.)?#', '', $domain) ?? $domain;
        $domain = rtrim($domain, '/');

        if ($domain === '') {
            return redirect()->back()->with('hata', 'Geçersiz alan adı.');
        }

        $apiKeyVal = 'rk_'.strtolower(Str::random(30));
        $secretKeyVal = strtolower(Str::random(60));

        DB::transaction(function () use ($klinik, $domain, $apiKeyVal, $secretKeyVal) {
            KlinikWebSitesi::create([
                'klinik_id' => $klinik->id,
                'domain' => $domain,
                'tema' => 'custom',
                'durum' => 'aktif',
            ]);

            ApiKey::issue([
                'klinik_id' => $klinik->id,
                'doktor_id' => null,
                'api_key' => $apiKeyVal,
                'durum' => true,
                'yetkiler' => ['*'],
            ], $secretKeyVal);

            DB::table('webhook_endpoints')->updateOrInsert(
                ['klinik_id' => $klinik->id, 'doktor_id' => null],
                [
                    'url' => $this->buildWebhookUrl($domain),
                    'secret_key' => $secretKeyVal,
                    'events' => json_encode(['*']),
                    'aktif' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        });

        return redirect()
            ->route('hekim.klinik.web-sitesi.kurulum')
            ->with('basari', 'Klinik web sitesi tanımlandı. Secret key yalnızca bir kez gösterilir — hemen kopyalayın.')
            ->with('plain_api_secret', $secretKeyVal);
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

    protected function buildWebhookUrl(string $domain): string
    {
        $webhookUrl = rtrim($domain, '/');
        if (! str_starts_with($webhookUrl, 'http://') && ! str_starts_with($webhookUrl, 'https://')) {
            $scheme = app()->environment('production') ? 'https://' : 'http://';
            $webhookUrl = $scheme.$webhookUrl;
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
