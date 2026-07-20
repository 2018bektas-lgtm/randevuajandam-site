<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\DomainOrder;
use App\Models\Doktor;
use App\Models\HekimWebSitesi;
use App\Models\Klinik;
use App\Models\KlinikWebSitesi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Web sitesi + domain + API anahtarı + webhook — tek seferde otomatik.
 * Domain ücreti pakete dahil; Hostinger kaydı DomainInclusionService üzerinden.
 */
class WebsiteProvisioningService
{
    public function __construct(protected DomainInclusionService $domains) {}

    /**
     * Domain seçimi (included veya byod) + site kaydı + API key.
     *
     * @return array{order: DomainOrder, domain: string, plain_secret: string, created_site: bool}
     */
    public function provisionDoktor(Doktor $doktor, string $domain, string $mode = 'included'): array
    {
        $domain = $this->domains->normalizeDomain($domain);
        if ($domain === '') {
            throw new RuntimeException('Geçersiz alan adı.');
        }

        $order = $mode === 'byod'
            ? $this->domains->claimByod($doktor, $domain)
            : $this->domains->claimIncluded($doktor, $domain);

        $domain = $order->domain;
        $created = false;
        $plainSecret = null;

        DB::transaction(function () use ($doktor, $domain, $order, &$created, &$plainSecret) {
            $site = $doktor->webSite;
            if (! $site) {
                if (HekimWebSitesi::query()->where('domain', $domain)->where('doktor_id', '!=', $doktor->id)->exists()) {
                    throw new RuntimeException('Bu alan adı başka bir hekim tarafından kullanılıyor.');
                }
                $site = HekimWebSitesi::create([
                    'doktor_id' => $doktor->id,
                    'domain' => $domain,
                    'tema' => 'custom',
                    'durum' => $order->kaynak === DomainOrder::KAYNAK_BYOD ? 'dns_bekliyor' : 'aktif',
                    'hostinger_domain_id' => $order->hostinger_order_id,
                ]);
                $created = true;
            } else {
                $site->forceFill([
                    'domain' => $domain,
                    'hostinger_domain_id' => $order->hostinger_order_id ?? $site->hostinger_domain_id,
                    'durum' => $order->kaynak === DomainOrder::KAYNAK_BYOD ? 'dns_bekliyor' : 'aktif',
                ])->save();
            }

            $apiKeyVal = 'rk_'.strtolower(Str::random(30));
            $secretKeyVal = strtolower(Str::random(60));
            $plainSecret = $secretKeyVal;

            ApiKey::issue([
                'doktor_id' => $doktor->id,
                'klinik_id' => null,
                'api_key' => $apiKeyVal,
                'durum' => true,
                'yetkiler' => ['*'],
            ], $secretKeyVal);

            DB::table('webhook_endpoints')->updateOrInsert(
                ['doktor_id' => $doktor->id],
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

        return [
            'order' => $order->fresh(),
            'domain' => $domain,
            'plain_secret' => (string) $plainSecret,
            'created_site' => $created,
        ];
    }

    /**
     * @return array{order: DomainOrder, domain: string, plain_secret: string, created_site: bool}
     */
    public function provisionKlinik(Klinik $klinik, string $domain, string $mode = 'included'): array
    {
        $domain = $this->domains->normalizeDomain($domain);
        if ($domain === '') {
            throw new RuntimeException('Geçersiz alan adı.');
        }

        $order = $mode === 'byod'
            ? $this->domains->claimByod($klinik, $domain)
            : $this->domains->claimIncluded($klinik, $domain);

        $domain = $order->domain;
        $created = false;
        $plainSecret = null;

        DB::transaction(function () use ($klinik, $domain, $order, &$created, &$plainSecret) {
            $site = $klinik->webSite;
            if (! $site) {
                if (KlinikWebSitesi::query()->where('domain', $domain)->where('klinik_id', '!=', $klinik->id)->exists()) {
                    throw new RuntimeException('Bu alan adı başka bir klinik tarafından kullanılıyor.');
                }
                $site = KlinikWebSitesi::create([
                    'klinik_id' => $klinik->id,
                    'domain' => $domain,
                    'tema' => 'custom',
                    'durum' => $order->kaynak === DomainOrder::KAYNAK_BYOD ? 'dns_bekliyor' : 'aktif',
                    'hostinger_domain_id' => $order->hostinger_order_id,
                ]);
                $created = true;
            } else {
                $attrs = [
                    'domain' => $domain,
                    'durum' => $order->kaynak === DomainOrder::KAYNAK_BYOD ? 'dns_bekliyor' : 'aktif',
                ];
                if (\Illuminate\Support\Facades\Schema::hasColumn($site->getTable(), 'hostinger_domain_id')) {
                    $attrs['hostinger_domain_id'] = $order->hostinger_order_id ?? $site->hostinger_domain_id;
                }
                $site->forceFill($attrs)->save();
            }

            $apiKeyVal = 'rk_'.strtolower(Str::random(30));
            $secretKeyVal = strtolower(Str::random(60));
            $plainSecret = $secretKeyVal;

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

        return [
            'order' => $order->fresh(),
            'domain' => $domain,
            'plain_secret' => (string) $plainSecret,
            'created_site' => $created,
        ];
    }

    public function buildWebhookUrl(string $domain): string
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
