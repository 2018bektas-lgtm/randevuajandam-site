<?php

namespace App\Services;

use App\Models\SiteAyari;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * PayTR iFrame API (tek seferlik kartlı ödeme).
 * Abonelik yenilemesi otomatik değildir — dönem sonunda hekim yeniden öder.
 */
class PaytrService
{
    protected string $merchantId;

    protected string $merchantKey;

    protected string $merchantSalt;

    protected bool $testMode;

    protected bool $debugOn;

    public function __construct()
    {
        $settings = SiteAyari::query()->first();
        $this->merchantId = trim((string) ($settings?->paytr_merchant_id ?: config('services.paytr.merchant_id', '')));
        $this->merchantKey = trim((string) ($settings?->paytr_merchant_key ?: config('services.paytr.merchant_key', '')));
        $this->merchantSalt = trim((string) ($settings?->paytr_merchant_salt ?: config('services.paytr.merchant_salt', '')));
        $this->testMode = (bool) ($settings?->paytr_test_mode ?? config('services.paytr.test_mode', true));
        $this->debugOn = (bool) config('services.paytr.debug_on', ! app()->environment('production'));
    }

    public function isConfigured(): bool
    {
        return $this->merchantId !== ''
            && $this->merchantKey !== ''
            && $this->merchantSalt !== '';
    }

    public function isTestMode(): bool
    {
        return $this->testMode;
    }

    /**
     * Benzersiz mağaza sipariş no (alfanumerik, max 64).
     */
    public function makeMerchantOid(string $prefix = 'RA'): string
    {
        $oid = $prefix.now()->format('YmdHis').Str::upper(Str::random(8));

        return substr(preg_replace('/[^A-Za-z0-9]/', '', $oid) ?? $oid, 0, 64);
    }

    /**
     * iFrame token al.
     *
     * @param  array{
     *   merchant_oid: string,
     *   email: string,
     *   payment_amount: float|int|string,
     *   user_name: string,
     *   user_address?: string,
     *   user_phone?: string,
     *   user_ip?: string,
     *   basket_name?: string,
     *   merchant_ok_url?: string,
     *   merchant_fail_url?: string,
     *   no_installment?: int,
     *   max_installment?: int,
     *   currency?: string,
     * }  $payload
     * @return array{status: string, token?: string, errorMessage?: string}
     */
    public function createIframeToken(array $payload): array
    {
        if (! $this->isConfigured()) {
            return [
                'status' => 'failure',
                'errorMessage' => 'PayTR ödeme bilgileri yapılandırılmamış.',
            ];
        }

        $amountTl = (float) ($payload['payment_amount'] ?? 0);
        if ($amountTl <= 0) {
            return [
                'status' => 'failure',
                'errorMessage' => 'Geçersiz ödeme tutarı.',
            ];
        }

        // PayTR: tutar kuruş cinsinden integer (9.99 → 999)
        $paymentAmount = (int) round($amountTl * 100);
        $merchantOid = (string) $payload['merchant_oid'];
        $email = $this->asciiEmail((string) ($payload['email'] ?? ''));
        $userName = Str::limit((string) ($payload['user_name'] ?? 'Musteri'), 60, '');
        $userAddress = Str::limit((string) ($payload['user_address'] ?? 'Turkiye'), 400, '');
        $userPhone = Str::limit(preg_replace('/\D+/', '', (string) ($payload['user_phone'] ?? '05000000000')) ?: '05000000000', 20, '');
        $userIp = (string) ($payload['user_ip'] ?? request()->ip() ?? '127.0.0.1');
        // Localhost IP PayTR'de reddedilir; testte dış IP kullanılmalı
        if (in_array($userIp, ['127.0.0.1', '::1'], true) && app()->environment('local', 'testing')) {
            $userIp = (string) config('services.paytr.fallback_ip', '85.34.78.112');
        }

        $basketName = (string) ($payload['basket_name'] ?? 'Randevu Ajandam Uyelik');
        $unitPrice = number_format($amountTl, 2, '.', '');
        $userBasket = base64_encode(json_encode([
            [$basketName, $unitPrice, 1],
        ], JSON_UNESCAPED_UNICODE));

        $noInstallment = (int) ($payload['no_installment'] ?? 1);
        $maxInstallment = (int) ($payload['max_installment'] ?? 0);
        $currency = (string) ($payload['currency'] ?? 'TL');
        $testMode = $this->testMode ? '1' : '0';
        $debugOn = $this->debugOn ? '1' : '0';
        $timeoutLimit = (string) ($payload['timeout_limit'] ?? '30');
        $lang = (string) ($payload['lang'] ?? 'tr');

        $merchantOkUrl = (string) ($payload['merchant_ok_url'] ?? route('frontend.odeme.paytr.ok'));
        $merchantFailUrl = (string) ($payload['merchant_fail_url'] ?? route('frontend.odeme.paytr.fail'));

        $hashStr = $this->merchantId
            .$userIp
            .$merchantOid
            .$email
            .$paymentAmount
            .$userBasket
            .$noInstallment
            .$maxInstallment
            .$currency
            .$testMode;

        $paytrToken = base64_encode(hash_hmac(
            'sha256',
            $hashStr.$this->merchantSalt,
            $this->merchantKey,
            true
        ));

        $post = [
            'merchant_id' => $this->merchantId,
            'user_ip' => $userIp,
            'merchant_oid' => $merchantOid,
            'email' => $email,
            'payment_amount' => $paymentAmount,
            'paytr_token' => $paytrToken,
            'user_basket' => $userBasket,
            'debug_on' => $debugOn,
            'no_installment' => $noInstallment,
            'max_installment' => $maxInstallment,
            'user_name' => $userName,
            'user_address' => $userAddress,
            'user_phone' => $userPhone,
            'merchant_ok_url' => $merchantOkUrl,
            'merchant_fail_url' => $merchantFailUrl,
            'timeout_limit' => $timeoutLimit,
            'currency' => $currency,
            'test_mode' => $testMode,
            'lang' => $lang,
        ];

        try {
            $response = Http::asForm()
                ->timeout(25)
                ->post('https://www.paytr.com/odeme/api/get-token', $post);

            $body = $response->json() ?? [];
            if (($body['status'] ?? '') === 'success' && ! empty($body['token'])) {
                return [
                    'status' => 'success',
                    'token' => (string) $body['token'],
                    'merchant_oid' => $merchantOid,
                    'payment_amount' => $paymentAmount,
                ];
            }

            $reason = (string) ($body['reason'] ?? $response->body() ?: 'Bilinmeyen PayTR hatası');
            Log::error('PayTR get-token failed', [
                'reason' => $reason,
                'merchant_oid' => $merchantOid,
                'http' => $response->status(),
            ]);

            return [
                'status' => 'failure',
                'errorMessage' => $reason,
            ];
        } catch (\Throwable $e) {
            Log::error('PayTR get-token exception', ['message' => $e->getMessage()]);

            return [
                'status' => 'failure',
                'errorMessage' => 'PayTR bağlantı hatası: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Bildirim URL hash doğrulama.
     */
    public function verifyCallbackHash(string $merchantOid, string $status, string $totalAmount, string $hash): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $expected = base64_encode(hash_hmac(
            'sha256',
            $merchantOid.$this->merchantSalt.$status.$totalAmount,
            $this->merchantKey,
            true
        ));

        return hash_equals($expected, $hash);
    }

    public function referenceCodeFromOid(string $merchantOid): string
    {
        return 'PAYTR:'.$merchantOid;
    }

    public function isPaytrReference(?string $ref): bool
    {
        return is_string($ref) && str_starts_with($ref, 'PAYTR:');
    }

    protected function asciiEmail(string $email): string
    {
        $email = trim($email);
        // PayTR Türkçe karakter istemez
        $map = ['ı' => 'i', 'İ' => 'I', 'ş' => 's', 'Ş' => 'S', 'ğ' => 'g', 'Ğ' => 'G', 'ü' => 'u', 'Ü' => 'U', 'ö' => 'o', 'Ö' => 'O', 'ç' => 'c', 'Ç' => 'C'];
        $email = strtr($email, $map);

        return Str::limit($email !== '' ? $email : 'info@randevuajandam.com', 100, '');
    }
}
