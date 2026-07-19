<?php

namespace App\Services\Hostinger;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class HostingerClient
{
    public function enabled(): bool
    {
        return (bool) config('hostinger.enabled')
            && (string) config('hostinger.api_token') !== '';
    }

    protected function http(): PendingRequest
    {
        if (! $this->enabled()) {
            throw new RuntimeException('Hostinger API yapılandırılmamış (HOSTINGER_ENABLED + HOSTINGER_API_TOKEN).');
        }

        return Http::baseUrl((string) config('hostinger.base_url'))
            ->withToken((string) config('hostinger.api_token'))
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('hostinger.timeout', 25));
    }

    /**
     * @param  list<string>  $tlds
     * @return list<array{domain:?string,is_available:bool,is_alternative:bool,restriction:?string}>
     */
    public function checkAvailability(string $sld, array $tlds, bool $withAlternatives = false): array
    {
        $sld = strtolower(preg_replace('/[^a-z0-9\-]/i', '', $sld) ?? '');
        $tlds = array_values(array_filter(array_map(
            fn ($t) => strtolower(ltrim((string) $t, '.')),
            $tlds
        )));

        if ($sld === '' || $tlds === []) {
            throw new RuntimeException('Domain adı ve en az bir TLD gerekli.');
        }

        $res = $this->http()->post('/api/domains/v1/availability', [
            'domain' => $sld,
            'tlds' => $tlds,
            'with_alternatives' => $withAlternatives,
        ]);

        if (! $res->successful()) {
            Log::warning('Hostinger availability failed', [
                'status' => $res->status(),
                'body' => $res->json() ?? $res->body(),
            ]);
            throw new RuntimeException(
                $res->json('error') ?? $res->json('message') ?? 'Domain müsaitlik sorgusu başarısız ('.$res->status().').'
            );
        }

        $json = $res->json();
        if (isset($json['data']) && is_array($json['data'])) {
            $json = $json['data'];
        }
        if (! is_array($json)) {
            return [];
        }

        return array_values(array_map(function ($row) {
            $row = is_array($row) ? $row : (array) $row;

            return [
                'domain' => $row['domain'] ?? null,
                'is_available' => (bool) ($row['is_available'] ?? false),
                'is_alternative' => (bool) ($row['is_alternative'] ?? false),
                'restriction' => $row['restriction'] ?? null,
            ];
        }, $json));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function domainCatalog(?string $nameFilter = null): array
    {
        $cacheKey = 'hostinger.catalog.domain.'.md5((string) $nameFilter);

        return Cache::remember($cacheKey, (int) config('hostinger.catalog_cache_ttl', 21600), function () use ($nameFilter) {
            $query = array_filter([
                'category' => 'DOMAIN',
                'name' => $nameFilter,
            ]);

            $res = $this->http()->get('/api/billing/v1/catalog', $query);
            if (! $res->successful()) {
                throw new RuntimeException('Hostinger katalog alınamadı ('.$res->status().').');
            }

            $json = $res->json();
            if (isset($json['data']) && is_array($json['data'])) {
                $json = $json['data'];
            }

            return is_array($json) ? $json : [];
        });
    }

    public function resolveDomainItemId(string $tld, string $preferredCurrency = 'USD'): ?string
    {
        $tld = strtolower(ltrim($tld, '.'));
        try {
            $catalog = $this->domainCatalog('*'.$tld.'*');
        } catch (\Throwable) {
            $catalog = $this->domainCatalog();
        }

        $best = null;
        foreach ($catalog as $item) {
            $item = is_array($item) ? $item : (array) $item;
            $name = strtolower((string) ($item['name'] ?? $item['id'] ?? ''));
            $id = (string) ($item['id'] ?? '');
            if ($id === '') {
                continue;
            }
            if (! str_contains($name, $tld) && ! str_contains(strtolower($id), $tld)) {
                continue;
            }
            foreach ($item['prices'] ?? [] as $price) {
                $price = is_array($price) ? $price : (array) $price;
                $periodUnit = (string) ($price['period_unit'] ?? '');
                $period = (int) ($price['period'] ?? 0);
                $currency = strtoupper((string) ($price['currency'] ?? ''));
                $priceId = (string) ($price['id'] ?? '');
                if ($priceId === '' || $periodUnit !== 'year' || $period !== 1) {
                    continue;
                }
                if ($currency === strtoupper($preferredCurrency)) {
                    return $priceId;
                }
                $best ??= $priceId;
            }
        }

        return $best;
    }

    /**
     * @return array<string, mixed>
     */
    public function purchaseDomain(string $domain, string $itemId, ?int $paymentMethodId = null): array
    {
        $payload = [
            'domain' => strtolower(trim($domain)),
            'item_id' => $itemId,
        ];
        $pm = $paymentMethodId ?? config('hostinger.payment_method_id');
        if ($pm) {
            $payload['payment_method_id'] = (int) $pm;
        }

        $res = $this->http()->post('/api/domains/v1/portfolio', $payload);
        if (! $res->successful()) {
            Log::error('Hostinger domain purchase failed', [
                'domain' => $domain,
                'status' => $res->status(),
                'body' => $res->json() ?? $res->body(),
            ]);
            throw new RuntimeException(
                $res->json('error') ?? $res->json('message') ?? 'Domain kaydı başarısız ('.$res->status().').'
            );
        }

        $json = $res->json();

        return is_array($json) ? $json : [];
    }
}
