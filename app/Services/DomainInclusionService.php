<?php

namespace App\Services;

use App\Models\DomainOrder;
use App\Models\Doktor;
use App\Models\Klinik;
use App\Models\Paket;
use App\Services\Hostinger\HostingerClient;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;

/**
 * Web sitesi paketinde domain dahil (ayrı ücret yok).
 * Aylık + yıllık: 1 yıl domain. TLD: com, net (com.tr faz 2).
 */
class DomainInclusionService
{
    public function __construct(protected HostingerClient $hostinger) {}

    /**
     * @return array{eligible:bool,reason:?string,paket:?Paket,tlds:list<string>,yil:int,already_claimed:bool,active_domain:?string,hostinger_ready:bool}
     */
    public function eligibility(Doktor|Klinik $owner): array
    {
        $paket = $this->resolvePackage($owner);
        $tlds = $this->includedTlds($paket);
        $claimed = $this->activeIncludedOrder($owner);

        $eligible = $paket
            && (bool) ($paket->domain_dahil_mi ?? false)
            && $this->hasWebsiteFeature($owner, $paket)
            && ! $claimed;

        $reason = null;
        if (! $paket) {
            $reason = 'Aktif paket bulunamadı.';
        } elseif (! ($paket->domain_dahil_mi ?? false)) {
            $reason = 'Domain yalnızca Özel Web Sitesi / Klinik Kurumsal paketinde dahildir.';
        } elseif (! $this->hasWebsiteFeature($owner, $paket)) {
            $reason = 'Web sitesi yetkisi bu pakette yok.';
        } elseif ($claimed) {
            $reason = 'Pakete dahil domain hakkınız kullanılmış: '.$claimed->domain;
        }

        return [
            'eligible' => (bool) $eligible,
            'reason' => $reason,
            'paket' => $paket,
            'tlds' => $tlds,
            'yil' => (int) ($paket->domain_dahil_yil ?? 1),
            'already_claimed' => (bool) $claimed,
            'active_domain' => $claimed?->domain,
            'hostinger_ready' => $this->hostinger->enabled(),
        ];
    }

    /**
     * @param  list<string>|null  $tlds
     * @return list<array{domain:?string,is_available:bool,is_alternative:bool,restriction:?string,mock?:bool}>
     */
    public function check(Doktor|Klinik $owner, string $sld, ?array $tlds = null): array
    {
        $elig = $this->eligibility($owner);
        if (! $this->hasWebsiteFeature($owner, $elig['paket'] ?? null)) {
            throw new RuntimeException($elig['reason'] ?? 'Domain sorgusu için yetkiniz yok.');
        }

        $tlds = $tlds ?: $elig['tlds'];
        $key = 'hostinger-avail:'.(request()->ip() ?? 'cli');
        $max = (int) config('hostinger.availability_per_minute', 8);
        if (RateLimiter::tooManyAttempts($key, $max)) {
            throw new RuntimeException('Çok fazla domain sorgusu. Lütfen bir dakika sonra tekrar deneyin.');
        }
        RateLimiter::hit($key, 60);

        if (! $this->hostinger->enabled()) {
            return array_map(fn ($tld) => [
                'domain' => strtolower(preg_replace('/[^a-z0-9\-]/i', '', $sld) ?? $sld).'.'.$tld,
                'is_available' => true,
                'is_alternative' => false,
                'restriction' => null,
                'mock' => true,
            ], $tlds);
        }

        return $this->hostinger->checkAvailability($sld, $tlds, true);
    }

    /**
     * Ödeme öncesi müsaitlik — seçilen pakete göre (üyelik henüz yok).
     *
     * @param  list<string>|null  $tlds
     * @return list<array{domain:?string,is_available:bool,is_alternative:bool,restriction:?string,mock?:bool}>
     */
    public function checkByPackage(Paket $paket, string $sld, ?array $tlds = null): array
    {
        $tlds = $tlds ?: $this->includedTlds($paket);
        $key = 'hostinger-avail:'.(request()->ip() ?? 'cli');
        $max = (int) config('hostinger.availability_per_minute', 8);
        if (RateLimiter::tooManyAttempts($key, $max)) {
            throw new RuntimeException('Çok fazla domain sorgusu. Lütfen bir dakika sonra tekrar deneyin.');
        }
        RateLimiter::hit($key, 60);

        if (! $this->hostinger->enabled()) {
            return array_map(fn ($tld) => [
                'domain' => strtolower(preg_replace('/[^a-z0-9\-]/i', '', $sld) ?? $sld).'.'.$tld,
                'is_available' => true,
                'is_alternative' => false,
                'restriction' => null,
                'mock' => true,
            ], $tlds);
        }

        return $this->hostinger->checkAvailability($sld, $tlds, true);
    }

    /**
     * @return list<string>
     */
    public function tldsForPackage(?Paket $paket): array
    {
        return $this->includedTlds($paket);
    }

    public function claimIncluded(Doktor|Klinik $owner, string $domain): DomainOrder
    {
        $elig = $this->eligibility($owner);
        if (! $elig['eligible']) {
            throw new RuntimeException($elig['reason'] ?? 'Domain hakkı yok.');
        }

        $domain = $this->normalizeDomain($domain);
        $tld = $this->extractTld($domain);
        $allowed = $elig['tlds'];
        if ($tld === '' || ! in_array($tld, $allowed, true)) {
            throw new RuntimeException('Bu uzantı pakete dahil değil. İzin verilen: '.implode(', ', $allowed));
        }

        if (DomainOrder::query()->where('domain', $domain)->exists()) {
            throw new RuntimeException('Bu alan adı sistemde zaten kayıtlı.');
        }

        $order = DomainOrder::query()->create([
            'owner_type' => $owner::class,
            'owner_id' => $owner->id,
            'paket_id' => $elig['paket']?->id,
            'domain' => $domain,
            'tld' => $tld,
            'kaynak' => DomainOrder::KAYNAK_INCLUDED,
            'durum' => DomainOrder::DURUM_PURCHASING,
        ]);

        try {
            if ($this->hostinger->enabled()) {
                $itemId = $this->hostinger->resolveDomainItemId($tld)
                    ?? throw new RuntimeException('Bu TLD için Hostinger fiyat kalemi bulunamadı.');
                $order->hostinger_item_id = $itemId;
                $purchase = $this->hostinger->purchaseDomain($domain, $itemId);
                $order->hostinger_order_id = (string) (
                    $purchase['id']
                    ?? data_get($purchase, 'data.id')
                    ?? $purchase['order_id']
                    ?? ''
                ) ?: null;
            }

            $order->durum = DomainOrder::DURUM_ACTIVE;
            $order->registered_at = now();
            $order->expires_at = now()->addYears(max(1, (int) ($elig['yil'] ?: 1)));
            $order->error_message = null;
            $order->save();
        } catch (\Throwable $e) {
            $order->durum = DomainOrder::DURUM_FAILED;
            $order->error_message = $e->getMessage();
            $order->save();
            throw $e;
        }

        return $order->fresh();
    }

    public function claimByod(Doktor|Klinik $owner, string $domain): DomainOrder
    {
        if (! $this->hasWebsiteFeature($owner, $this->resolvePackage($owner))) {
            throw new RuntimeException('Web sitesi yetkisi gerekli.');
        }

        $domain = $this->normalizeDomain($domain);
        if (DomainOrder::query()->where('domain', $domain)->where('durum', DomainOrder::DURUM_ACTIVE)->exists()) {
            throw new RuntimeException('Bu alan adı sistemde zaten aktif.');
        }

        return DomainOrder::query()->create([
            'owner_type' => $owner::class,
            'owner_id' => $owner->id,
            'paket_id' => $this->resolvePackage($owner)?->id,
            'domain' => $domain,
            'tld' => $this->extractTld($domain),
            'kaynak' => DomainOrder::KAYNAK_BYOD,
            'durum' => DomainOrder::DURUM_DNS_PENDING,
            'registered_at' => now(),
        ]);
    }

    protected function resolvePackage(Doktor|Klinik $owner): ?Paket
    {
        if ($owner instanceof Doktor) {
            if (method_exists($owner, 'aktifPaket')) {
                $p = $owner->aktifPaket();
                if ($p instanceof Paket) {
                    return $p;
                }
            }
            $owner->loadMissing('paket');

            return $owner->paket;
        }

        $owner->loadMissing('paket');

        return $owner->paket ?? null;
    }

    protected function hasWebsiteFeature(Doktor|Klinik $owner, ?Paket $paket): bool
    {
        if (! $paket) {
            return false;
        }
        $kod = $owner instanceof Doktor ? 'web_sitesi' : 'klinik_web_sitesi';
        if (method_exists($paket, 'hasFeature')) {
            return (bool) $paket->hasFeature($kod);
        }

        return (bool) ($paket->domain_dahil_mi ?? false);
    }

    protected function activeIncludedOrder(Doktor|Klinik $owner): ?DomainOrder
    {
        return DomainOrder::query()
            ->where('owner_type', $owner::class)
            ->where('owner_id', $owner->id)
            ->where('kaynak', DomainOrder::KAYNAK_INCLUDED)
            ->whereIn('durum', [
                DomainOrder::DURUM_ACTIVE,
                DomainOrder::DURUM_PURCHASING,
                DomainOrder::DURUM_DNS_PENDING,
            ])
            ->latest('id')
            ->first();
    }

    /**
     * @return list<string>
     */
    protected function includedTlds(?Paket $paket): array
    {
        $fromPaket = $paket?->domain_dahil_tlds;
        if (is_array($fromPaket) && $fromPaket !== []) {
            return array_values(array_map(fn ($t) => strtolower(ltrim((string) $t, '.')), $fromPaket));
        }

        return config('hostinger.default_included_tlds', ['com', 'net']);
    }

    public function normalizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^https?://(www\.)?#', '', $domain) ?? $domain;
        $domain = rtrim($domain, '/');

        return preg_replace('/[^a-z0-9\.\-]/', '', $domain) ?? $domain;
    }

    protected function extractTld(string $domain): string
    {
        $parts = explode('.', $domain);
        if (count($parts) < 2) {
            return '';
        }
        if (count($parts) >= 3 && end($parts) === 'tr') {
            return $parts[count($parts) - 2].'.tr';
        }

        return (string) end($parts);
    }
}
