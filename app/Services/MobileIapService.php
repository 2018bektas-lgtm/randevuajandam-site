<?php

namespace App\Services;

use App\Models\Doktor;
use App\Models\Paket;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MobileIapService
{
    /**
     * Expected store product id for a backend package + period.
     */
    public static function productIdFor(int $paketId, string $period): string
    {
        $prefix = (string) config('services.mobile_iap.product_prefix', 'com.randevuajandam.doktor.pkg.');
        $suffix = $period === 'yillik' ? 'yearly' : 'monthly';

        return $prefix.$paketId.'.'.$suffix;
    }

    public static function parseProductId(string $productId): ?array
    {
        // com.randevuajandam.doktor.pkg.{id}.monthly|yearly
        if (! preg_match('/\.pkg\.(\d+)\.(monthly|yearly)$/', $productId, $m)) {
            return null;
        }

        return [
            'paket_id' => (int) $m[1],
            'period' => $m[2] === 'yearly' ? 'yillik' : 'aylik',
        ];
    }

    /**
     * Activate membership on doctor for given package/period.
     */
    public function activate(Doktor $doktor, Paket $paket, string $period, array $meta = []): void
    {
        $baslangic = now();
        $bitis = $period === 'yillik'
            ? $baslangic->copy()->addYear()
            : $baslangic->copy()->addMonth();

        $doktor->update([
            'paket_id' => $paket->id,
            'odeme_periyodu' => $period,
            'uyelik_baslangic' => $baslangic,
            'uyelik_bitis' => $bitis,
            'iyzico_subscription_status' => 'ACTIVE',
            'platformda_gorunur' => true,
        ]);

        if (! empty($meta['transaction_id'])) {
            Cache::put(
                'mobile_iap_tx_'.$meta['transaction_id'],
                [
                    'doktor_id' => $doktor->id,
                    'paket_id' => $paket->id,
                    'at' => now()->toIso8601String(),
                ],
                now()->addYears(2)
            );
        }

        Log::info('mobile_iap_activated', [
            'doktor_id' => $doktor->id,
            'paket_id' => $paket->id,
            'period' => $period,
            'source' => $meta['source'] ?? 'unknown',
            'transaction_id' => $meta['transaction_id'] ?? null,
        ]);

        // IAP için sentetik UyelikOdeme yoksa referans yine de davet_eden üzerinden işlenebilir:
        // ödül sadece UyelikOdeme ile — mobil ilk ödeme web PayTR ile tamamlanırsa ödül orada.
        // İsteğe bağlı: IAP tutarı bilinmiyorsa komisyon günü varsayılan aylık/yıllık.
        if ($doktor->davet_eden_id) {
            try {
                $odeme = \App\Models\UyelikOdeme::query()->create([
                    'doktor_id' => $doktor->id,
                    'paket_id' => $paket->id,
                    'odeme_yontemi' => 'iap',
                    'provider' => 'revenuecat',
                    'odeme_periyodu' => $period,
                    'tutar' => max(1, (float) ($period === 'yillik'
                        ? ($paket->yillik_indirimli_fiyat ?? $paket->yillik_fiyat)
                        : ($paket->aylik_indirimli_fiyat ?? $paket->aylik_fiyat))),
                    'durum' => 'onaylandi',
                    'onaylandi_at' => now(),
                    'kurulum_verisi' => [
                        'tutar_brut' => (float) ($period === 'yillik' ? $paket->yillik_fiyat : $paket->aylik_fiyat),
                        'iap' => true,
                        'transaction_id' => $meta['transaction_id'] ?? null,
                    ],
                ]);
                app(ReferansService::class)->odullendir($odeme);
            } catch (\Throwable $e) {
                Log::warning('iap_referans_odul: '.$e->getMessage());
            }
        }
    }

    public function transactionAlreadyUsed(string $transactionId): bool
    {
        return Cache::has('mobile_iap_tx_'.$transactionId);
    }

    /**
     * Verify purchase via RevenueCat REST (secret key) or trust_client flag.
     *
     * @return array{ok: bool, message?: string}
     */
    public function verifyPurchase(array $opts): array
    {
        $productId = (string) ($opts['product_id'] ?? '');
        $transactionId = (string) ($opts['transaction_id'] ?? '');
        $appUserId = (string) ($opts['app_user_id'] ?? '');

        if ($transactionId !== '' && $this->transactionAlreadyUsed($transactionId)) {
            return ['ok' => false, 'message' => 'Bu işlem zaten kullanıldı.'];
        }

        $expected = self::productIdFor((int) $opts['paket_id'], (string) $opts['period']);
        if ($productId !== '' && $productId !== $expected) {
            // Allow alternate product id if parse matches paket+period
            $parsed = self::parseProductId($productId);
            if (! $parsed || $parsed['paket_id'] !== (int) $opts['paket_id'] || $parsed['period'] !== $opts['period']) {
                return ['ok' => false, 'message' => 'Ürün kimliği paket ile uyuşmuyor.'];
            }
        }

        $secret = config('services.revenuecat.secret_key');
        if ($secret && $appUserId !== '') {
            try {
                $res = Http::withToken($secret)
                    ->acceptJson()
                    ->timeout(12)
                    ->get('https://api.revenuecat.com/v1/subscribers/'.rawurlencode($appUserId));

                if (! $res->successful()) {
                    Log::warning('revenuecat_subscriber_lookup_failed', [
                        'status' => $res->status(),
                        'app_user_id' => $appUserId,
                    ]);

                    return ['ok' => false, 'message' => 'Mağaza aboneliği doğrulanamadı (RevenueCat).'];
                }

                $body = $res->json();
                $entitlements = data_get($body, 'subscriber.entitlements', []);
                $subscriptions = data_get($body, 'subscriber.subscriptions', []);

                // Active if product appears in subscriptions or any entitlement product_identifier matches
                $active = false;
                foreach ($subscriptions as $pid => $sub) {
                    if ((string) $pid === $productId || str_contains((string) $pid, '.pkg.'.(int) $opts['paket_id'].'.')) {
                        $expires = data_get($sub, 'expires_date');
                        if (! $expires || now()->lt($expires)) {
                            $active = true;
                            break;
                        }
                    }
                }
                if (! $active) {
                    foreach ($entitlements as $ent) {
                        $pid = (string) data_get($ent, 'product_identifier', '');
                        if ($pid === $productId || str_contains($pid, '.pkg.'.(int) $opts['paket_id'].'.')) {
                            $expires = data_get($ent, 'expires_date');
                            if (! $expires || now()->lt($expires)) {
                                $active = true;
                                break;
                            }
                        }
                    }
                }

                if (! $active) {
                    return ['ok' => false, 'message' => 'RevenueCat üzerinde aktif abonelik bulunamadı.'];
                }

                return ['ok' => true];
            } catch (\Throwable $e) {
                Log::error('revenuecat_verify_exception', ['error' => $e->getMessage()]);

                return ['ok' => false, 'message' => 'Doğrulama servisi hatası.'];
            }
        }

        // Yalnızca açıkça trust_client + non-production (staging)
        if (config('services.mobile_iap.trust_client')
            && ! app()->environment('production')
            && $transactionId !== '') {
            Log::warning('mobile_iap_trust_client', [
                'product_id' => $productId,
                'transaction_id' => $transactionId,
            ]);

            return ['ok' => true, 'message' => 'trust_client'];
        }

        return [
            'ok' => false,
            'message' => 'IAP doğrulama yapılandırılmamış. REVENUECAT_SECRET_KEY ekleyin veya web’den PayTR ile ödeyin.',
        ];
    }
}
