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
     * PayTR Tekrarlayan Ödeme: kayıtlı kart ile otomatik çekim.
     * Merchant hesabında "Tekrarlayan Ödeme" modülü aktif olmalı.
     *
     * @return array{status: string, errorMessage?: string, payment_amount?: int, merchant_oid?: string}
     */
    public function chargeRecurring(string $recurringId, array $payload): array
    {
        if (! $this->isConfigured()) {
            return ['status' => 'failure', 'errorMessage' => 'PayTR yapılandırılmamış.'];
        }

        $amountTl = (float) ($payload['payment_amount'] ?? 0);
        if ($amountTl <= 0) {
            return ['status' => 'failure', 'errorMessage' => 'Geçersiz tutar.'];
        }

        $paymentAmount = (int) round($amountTl * 100);
        $merchantOid   = (string) ($payload['merchant_oid'] ?? $this->makeMerchantOid('REN'));
        $email         = $this->asciiEmail((string) ($payload['email'] ?? ''));

        $hashStr = $this->merchantId . $recurringId . $merchantOid . $email . $paymentAmount;
        $token   = base64_encode(hash_hmac('sha256', $hashStr . $this->merchantSalt, $this->merchantKey, true));

        $post = [
            'merchant_id'       => $this->merchantId,
            'recurring_id'      => $recurringId,
            'merchant_oid'      => $merchantOid,
            'email'             => $email,
            'payment_amount'    => $paymentAmount,
            'paytr_token'       => $token,
            'currency'          => 'TL',
            'test_mode'         => $this->testMode ? '1' : '0',
        ];

        try {
            $response = Http::asForm()->timeout(30)
                ->post('https://www.paytr.com/odeme/tekrar', $post);

            $body = $response->json() ?? [];

            if (($body['status'] ?? '') === 'success') {
                return [
                    'status'         => 'success',
                    'merchant_oid'   => $merchantOid,
                    'payment_amount' => $paymentAmount,
                ];
            }

            $reason = (string) ($body['reason'] ?? $body['err_msg'] ?? 'PayTR recurring hata');
            Log::error('PayTR recurring charge failed', [
                'recurring_id' => $recurringId,
                'reason'       => $reason,
            ]);

            return ['status' => 'failure', 'errorMessage' => $reason];
        } catch (\Throwable $e) {
            Log::error('PayTR recurring exception: ' . $e->getMessage());

            return ['status' => 'failure', 'errorMessage' => 'PayTR bağlantı hatası.'];
        }
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
     *   recurring?: bool,
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

        $recurringPayment = (bool) ($payload['recurring'] ?? false) ? '1' : '0';

        $post = [
            'merchant_id'        => $this->merchantId,
            'user_ip'            => $userIp,
            'merchant_oid'       => $merchantOid,
            'email'              => $email,
            'payment_amount'     => $paymentAmount,
            'paytr_token'        => $paytrToken,
            'user_basket'        => $userBasket,
            'debug_on'           => $debugOn,
            'no_installment'     => $noInstallment,
            'max_installment'    => $maxInstallment,
            'user_name'          => $userName,
            'user_address'       => $userAddress,
            'user_phone'         => $userPhone,
            'merchant_ok_url'    => $merchantOkUrl,
            'merchant_fail_url'  => $merchantFailUrl,
            'timeout_limit'      => $timeoutLimit,
            'currency'           => $currency,
            'test_mode'          => $testMode,
            'lang'               => $lang,
            'recurring_payment'  => $recurringPayment,
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

    /**
     * PayTR Direct API: kart bilgileri doğrudan gönderilir, 3D Secure HTML döner.
     *
     * @return array{status: string, html?: string, errorMessage?: string}
     */
    public function createDirectPayment(array $payload): array
    {
        if (! $this->isConfigured()) {
            return ['status' => 'failure', 'errorMessage' => 'PayTR ödeme bilgileri yapılandırılmamış.'];
        }

        $amountTl = (float) ($payload['payment_amount'] ?? 0);
        if ($amountTl <= 0) {
            return ['status' => 'failure', 'errorMessage' => 'Geçersiz ödeme tutarı.'];
        }

        $paymentAmount = (int) round($amountTl * 100);
        $merchantOid = (string) $payload['merchant_oid'];
        $email = $this->asciiEmail((string) ($payload['email'] ?? ''));
        $userIp = (string) ($payload['user_ip'] ?? request()->ip() ?? '127.0.0.1');
        if (in_array($userIp, ['127.0.0.1', '::1'], true) && app()->environment('local', 'testing')) {
            $userIp = (string) config('services.paytr.fallback_ip', '85.34.78.112');
        }

        $userName    = Str::limit((string) ($payload['user_name'] ?? 'Musteri'), 60, '');
        $userAddress = Str::limit((string) ($payload['user_address'] ?? 'Turkiye'), 400, '');
        $userPhone   = Str::limit(preg_replace('/\D+/', '', (string) ($payload['user_phone'] ?? '05000000000')) ?: '05000000000', 20, '');

        $basketName  = (string) ($payload['basket_name'] ?? 'Randevu Ajandam Uyelik');
        $unitPrice   = number_format($amountTl, 2, '.', '');
        $userBasket  = base64_encode(json_encode([[$basketName, $unitPrice, 1]], JSON_UNESCAPED_UNICODE));

        $currency         = 'TL';
        $testMode         = $this->testMode ? '1' : '0';
        $paymentType      = 'card';
        $installmentCount = '0';
        $non3d            = '0';

        $hashStr = $this->merchantId
            .$userIp
            .$merchantOid
            .$email
            .$paymentAmount
            .$paymentType
            .$installmentCount
            .$currency
            .$testMode
            .$non3d;

        $paytrToken = base64_encode(hash_hmac('sha256', $hashStr.$this->merchantSalt, $this->merchantKey, true));

        $merchantOkUrl   = (string) ($payload['merchant_ok_url']   ?? route('frontend.odeme.paytr.3d.ok'));
        $merchantFailUrl = (string) ($payload['merchant_fail_url'] ?? route('frontend.odeme.paytr.3d.fail'));

        $recurringPayment = (bool) ($payload['recurring'] ?? false) ? '1' : '0';

        $cardNumber = preg_replace('/\D+/', '', (string) ($payload['card_number'] ?? ''));
        $cardType   = (string) ($payload['card_type'] ?? '');
        if ($cardType === '' && $cardNumber !== '') {
            $cardType = match (true) {
                str_starts_with($cardNumber, '9') => 'troy',
                str_starts_with($cardNumber, '4') => 'visa',
                str_starts_with($cardNumber, '5') => 'mastercard',
                str_starts_with($cardNumber, '3') => 'amex',
                default => '',
            };
        }

        $post = [
            'merchant_id'       => $this->merchantId,
            'user_ip'           => $userIp,
            'merchant_oid'      => $merchantOid,
            'email'             => $email,
            'payment_amount'    => $paymentAmount,
            'paytr_token'       => $paytrToken,
            'user_basket'       => $userBasket,
            'debug_on'          => $this->debugOn ? '1' : '0',
            'no_installment'    => '1',
            'max_installment'   => '0',
            'user_name'         => $userName,
            'user_address'      => $userAddress,
            'user_phone'        => $userPhone,
            'merchant_ok_url'   => $merchantOkUrl,
            'merchant_fail_url' => $merchantFailUrl,
            'currency'          => $currency,
            'test_mode'         => $testMode,
            'lang'              => 'tr',
            'payment_type'      => $paymentType,
            'installment_count' => $installmentCount,
            'non_3d'            => $non3d,
            'card_type'         => $cardType,
            'card_owner'        => (string) ($payload['card_owner'] ?? ''),
            'card_number'       => $cardNumber,
            'card_expire'       => (string) ($payload['card_expire'] ?? ''),
            'card_cvv'          => (string) ($payload['card_cvv'] ?? ''),
            'recurring_payment' => $recurringPayment,
        ];

        try {
            $response = Http::asForm()->timeout(30)->post('https://www.paytr.com/odeme', $post);
            $body     = $response->body();

            if (empty($body)) {
                return ['status' => 'failure', 'errorMessage' => 'PayTR boş yanıt döndürdü.'];
            }

            $decoded = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                if (($decoded['status'] ?? '') === 'success') {
                    return ['status' => 'success'];
                }
                $reason = (string) ($decoded['reason'] ?? $decoded['err_msg'] ?? 'PayTR ödeme reddedildi.');
                Log::error('PayTR direct payment failed', ['reason' => $reason, 'merchant_oid' => $merchantOid]);

                return ['status' => 'failure', 'errorMessage' => $reason];
            }

            // HTML yanıt = 3D Secure yönlendirme formu
            if (str_contains($body, '<') && (str_contains($body, '<form') || str_contains($body, '<!DOCTYPE') || str_contains($body, '<html'))) {
                return ['status' => '3d', 'html' => $body];
            }

            Log::warning('PayTR direct unexpected response', ['merchant_oid' => $merchantOid, 'body' => substr($body, 0, 200)]);

            return ['status' => 'failure', 'errorMessage' => 'PayTR beklenmeyen yanıt döndürdü.'];
        } catch (\Throwable $e) {
            Log::error('PayTR direct payment exception: '.$e->getMessage());

            return ['status' => 'failure', 'errorMessage' => 'PayTR bağlantı hatası.'];
        }
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
