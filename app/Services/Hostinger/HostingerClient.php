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
     * Hostinger kuralı: with_alternatives=true yalnızca TEK TLD ile çalışır.
     * Çoklu TLD (com+net) için önce alternatives=false; doluysa benzer SLD + opsiyonel tek-TLD alternatif.
     *
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

        // 1) Asıl istek: tüm TLD'ler, alternatif YOK (Domains:2038 hatasını önler)
        $rows = $this->requestAvailability($sld, $tlds, false);
        $primaryDomains = [];
        foreach ($rows as $row) {
            if (! empty($row['domain'])) {
                $primaryDomains[$row['domain']] = true;
            }
        }

        $anyAvailable = false;
        foreach ($rows as $row) {
            if (! empty($row['is_available'])) {
                $anyAvailable = true;
                break;
            }
        }

        if (! $withAlternatives) {
            return $rows;
        }

        // 2) Hostinger alternatifleri (yalnızca tek TLD) — varsa ekle
        $preferredTld = in_array('com', $tlds, true) ? 'com' : $tlds[0];
        try {
            $altFromApi = $this->requestAvailability($sld, [$preferredTld], true);
            foreach ($altFromApi as $row) {
                $d = $row['domain'] ?? null;
                if (! $d || isset($primaryDomains[$d])) {
                    continue;
                }
                if (! empty($row['is_available'])) {
                    $row['is_alternative'] = true;
                    $rows[] = $row;
                    $primaryDomains[$d] = true;
                }
            }
        } catch (\Throwable $e) {
            Log::debug('Hostinger alternatives skip', ['error' => $e->getMessage()]);
        }

        // 3) Hâlâ müsait yoksa (veya az varsa) benzer SLD önerileri (bizim üretim + müsaitlik)
        if (! $anyAvailable) {
            foreach ($this->similarSlds($sld) as $sim) {
                try {
                    $simRows = $this->requestAvailability($sim, $tlds, false);
                } catch (\Throwable) {
                    continue;
                }
                foreach ($simRows as $row) {
                    $d = $row['domain'] ?? null;
                    if (! $d || isset($primaryDomains[$d]) || empty($row['is_available'])) {
                        continue;
                    }
                    $row['is_alternative'] = true;
                    $rows[] = $row;
                    $primaryDomains[$d] = true;
                }
                // En fazla birkaç öneri yeterli
                $altCount = count(array_filter($rows, fn ($r) => ! empty($r['is_alternative'])));
                if ($altCount >= 6) {
                    break;
                }
            }
        }

        return array_values($rows);
    }

    /**
     * @param  list<string>  $tlds
     * @return list<array{domain:?string,is_available:bool,is_alternative:bool,restriction:?string}>
     */
    protected function requestAvailability(string $sld, array $tlds, bool $withAlternatives): array
    {
        $res = $this->http()->post('/api/domains/v1/availability', [
            'domain' => $sld,
            'tlds' => array_values($tlds),
            // Hostinger: alternatives only with single TLD
            'with_alternatives' => $withAlternatives && count($tlds) === 1,
        ]);

        if (! $res->successful()) {
            $body = $res->json() ?? $res->body();
            Log::warning('Hostinger availability failed', [
                'status' => $res->status(),
                'body' => $body,
                'sld' => $sld,
                'tlds' => $tlds,
                'alternatives' => $withAlternatives,
            ]);
            $msg = $res->json('message') ?? $res->json('error') ?? null;
            // Ham [Domains:2038] mesajını kullanıcıya sadeleştir
            if (is_string($msg) && str_contains($msg, '2038')) {
                $msg = 'Domain sorgusu başarısız. Lütfen tekrar deneyin.';
            }
            throw new RuntimeException($msg ?: 'Domain müsaitlik sorgusu başarısız ('.$res->status().').');
        }

        $json = $res->json();
        if (isset($json['data']) && is_array($json['data'])) {
            $json = $json['data'];
        }
        if (! is_array($json)) {
            return [];
        }

        // Liste değilse (tek obje) sarmala
        if (isset($json['domain']) || array_key_exists('is_available', $json)) {
            $json = [$json];
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
     * Dolu isimler için basit benzer SLD adayları.
     *
     * @return list<string>
     */
    protected function similarSlds(string $sld): array
    {
        $sld = strtolower(trim($sld, '-'));
        if ($sld === '') {
            return [];
        }

        $candidates = [];
        $noHyphen = str_replace('-', '', $sld);
        if ($noHyphen !== $sld && strlen($noHyphen) >= 2) {
            $candidates[] = $noHyphen;
        }

        foreach (['dr', 'hekim', 'klinik', 'med', 'pro'] as $prefix) {
            $c = $prefix.'-'.$sld;
            if (strlen($c) <= 63) {
                $candidates[] = $c;
            }
        }
        foreach (['hekim', 'klinik', 'online', 'tr'] as $suffix) {
            $c = $sld.'-'.$suffix;
            if (strlen($c) <= 63) {
                $candidates[] = $c;
            }
        }

        // Tekilleştir, orijinali çıkar
        $out = [];
        foreach ($candidates as $c) {
            $c = strtolower(preg_replace('/[^a-z0-9\-]/', '', $c) ?? '');
            $c = trim($c, '-');
            if ($c === '' || $c === $sld || isset($out[$c])) {
                continue;
            }
            $out[$c] = $c;
            if (count($out) >= 4) {
                break;
            }
        }

        return array_values($out);
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
