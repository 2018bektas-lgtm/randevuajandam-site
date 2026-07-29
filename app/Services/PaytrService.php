<?php

namespace App\Services;

use App\Models\SiteAyari;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * PayTR ödeme servisi.
 *
 * Model (dokümantasyon):
 * 1) İlk ödeme: Direkt API + 3D (non_3d=0) + store_card=1 → utoken/ctoken notify'da
 *    @see https://dev.paytr.com/direkt-api/direkt-api-1-adim
 *    @see https://dev.paytr.com/direkt-api/kart-saklama-api/yeni-kart-ekleme
 * 2) Otomatik yenileme: kayıtlı kart + non_3d=1 + recurring_payment=1
 *    @see https://dev.paytr.com/direkt-api/kart-saklama-api/kayitli-kart-tekrarlayan-odeme
 *
 * iFrame tek seferlik ödeme de desteklenir (ek koltuk vb.).
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
     * Kayıtlı kart ile tekrarlayan ödeme (Non3D).
     * POST https://www.paytr.com/odeme — utoken + ctoken, non_3d=1, recurring_payment=1
     *
     * @return array{status: string, errorMessage?: string, merchant_oid?: string, raw?: array, try_again?: bool}
     */
    public function chargeStoredCardRecurring(string $utoken, string $ctoken, array $payload): array
    {
        if (! $this->isConfigured()) {
            return ['status' => 'failure', 'errorMessage' => 'PayTR yapılandırılmamış.'];
        }
        if ($utoken === '' || $ctoken === '') {
            return ['status' => 'failure', 'errorMessage' => 'Kayıtlı kart token eksik (utoken/ctoken).'];
        }

        $amountTl = (float) ($payload['payment_amount'] ?? 0);
        if ($amountTl <= 0) {
            return ['status' => 'failure', 'errorMessage' => 'Geçersiz tutar.'];
        }

        // Direkt API: payment_amount TL string "100.99" (iFrame kuruş değil!)
        // @see https://dev.paytr.com/direkt-api/direkt-api-1-adim
        $paymentAmount = number_format($amountTl, 2, '.', '');
        $merchantOid = (string) ($payload['merchant_oid'] ?? $this->makeMerchantOid('REN'));
        $email = $this->asciiEmail((string) ($payload['email'] ?? ''));
        $userIp = (string) ($payload['user_ip'] ?? config('services.paytr.fallback_ip', '85.34.78.112'));
        $userName = Str::limit((string) ($payload['user_name'] ?? 'Musteri'), 60, '');
        $userAddress = Str::limit((string) ($payload['user_address'] ?? 'Turkiye'), 400, '');
        $userPhone = Str::limit(preg_replace('/\D+/', '', (string) ($payload['user_phone'] ?? '05000000000')) ?: '05000000000', 20, '');
        $basketName = (string) ($payload['basket_name'] ?? 'Randevu Ajandam Uyelik Yenileme');
        // Direkt API sepet: düz JSON (iFrame base64 kullanır)
        $userBasket = json_encode([[$basketName, $paymentAmount, 1]], JSON_UNESCAPED_UNICODE);

        $paymentType = 'card';
        $installmentCount = '0';
        $currency = 'TL';
        $testMode = $this->testMode ? '1' : '0';
        $non3d = '1'; // Tekrarlayan ödeme: Non3D zorunlu

        // Token: merchant_id + user_ip + merchant_oid + email + payment_amount + payment_type + installment_count + currency + test_mode + non_3d
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

        $post = [
            'merchant_id' => $this->merchantId,
            'user_ip' => $userIp,
            'merchant_oid' => $merchantOid,
            'email' => $email,
            'payment_type' => $paymentType,
            'payment_amount' => $paymentAmount,
            'installment_count' => $installmentCount,
            'currency' => $currency,
            'test_mode' => $testMode,
            'non_3d' => $non3d,
            'merchant_ok_url' => (string) ($payload['merchant_ok_url'] ?? route('frontend.odeme.paytr.ok')),
            'merchant_fail_url' => (string) ($payload['merchant_fail_url'] ?? route('frontend.odeme.paytr.fail')),
            'user_name' => $userName,
            'user_address' => $userAddress,
            'user_phone' => $userPhone,
            'user_basket' => $userBasket,
            'debug_on' => $this->debugOn ? '1' : '0',
            'client_lang' => 'tr',
            'paytr_token' => $paytrToken,
            'utoken' => $utoken,
            'ctoken' => $ctoken,
            'recurring_payment' => '1',
        ];

        try {
            $response = Http::asForm()->timeout(35)->post('https://www.paytr.com/odeme', $post);
            $body = $response->json();
            if (! is_array($body)) {
                Log::error('PayTR stored recurring non-JSON', [
                    'oid' => $merchantOid,
                    'body' => substr($response->body(), 0, 300),
                ]);

                return ['status' => 'failure', 'errorMessage' => 'PayTR beklenmeyen yanıt.', 'merchant_oid' => $merchantOid];
            }

            $status = (string) ($body['status'] ?? 'failed');
            // success | wait_callback | failed
            if (in_array($status, ['success', 'wait_callback'], true)) {
                return [
                    'status' => $status === 'wait_callback' ? 'wait_callback' : 'success',
                    'merchant_oid' => $merchantOid,
                    'raw' => $body,
                ];
            }

            $reason = (string) ($body['msg'] ?? $body['reason'] ?? $body['err_msg'] ?? 'Kayıtlı kart çekimi başarısız');
            Log::error('PayTR stored recurring failed', [
                'oid' => $merchantOid,
                'reason' => $reason,
                'try_again' => $body['try_again'] ?? null,
            ]);

            return [
                'status' => 'failure',
                'errorMessage' => $reason,
                'merchant_oid' => $merchantOid,
                'try_again' => (bool) ($body['try_again'] ?? false),
                'raw' => $body,
            ];
        } catch (\Throwable $e) {
            Log::error('PayTR stored recurring exception: '.$e->getMessage());

            return ['status' => 'failure', 'errorMessage' => 'PayTR bağlantı hatası: '.$e->getMessage()];
        }
    }

    /**
     * @deprecated Eski /odeme/tekrar + recurring_id — resmi kart saklama utoken/ctoken kullanın.
     */
    public function chargeRecurring(string $recurringId, array $payload): array
    {
        // Geriye uyum: recurring_id bazen ctoken olarak saklanmış olabilir — tercih utoken+ctoken
        return $this->chargeStoredCardRecurring(
            (string) ($payload['utoken'] ?? ''),
            $recurringId !== '' ? $recurringId : (string) ($payload['ctoken'] ?? ''),
            $payload
        );
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
     * Direkt API 1. adım: ilk ödeme + opsiyonel kart saklama (store_card).
     * non_3d=0 → 3D Secure HTML döner.
     *
     * @return array{status: string, html?: string, errorMessage?: string, utoken?: string, ctoken?: string}
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

        // Direkt API resmi doküman: payment_amount = TL, ondalık nokta, 2 hane (örn. "100.99" veya "1500.00")
        // iFrame API kuruş (×100) kullanır — buraya karıştırma!
        // @see https://dev.paytr.com/direkt-api/direkt-api-1-adim  POST: payment_amount (double)
        // @see https://dev.paytr.com/direkt-api/kart-saklama-api/yeni-kart-ekleme
        $paymentAmount = number_format($amountTl, 2, '.', '');
        $merchantOid = (string) $payload['merchant_oid'];
        $email = $this->asciiEmail((string) ($payload['email'] ?? ''));
        $userIp = (string) ($payload['user_ip'] ?? request()->ip() ?? '127.0.0.1');
        if (in_array($userIp, ['127.0.0.1', '::1'], true) && app()->environment('local', 'testing')) {
            $userIp = (string) config('services.paytr.fallback_ip', '85.34.78.112');
        }

        $userName = Str::limit($this->asciiSafe((string) ($payload['user_name'] ?? 'Musteri')), 60, '');
        $userAddress = Str::limit($this->asciiSafe((string) ($payload['user_address'] ?? 'Turkiye')), 400, '');
        $userPhone = Str::limit(preg_replace('/\D+/', '', (string) ($payload['user_phone'] ?? '05000000000')) ?: '05000000000', 20, '');
        $basketName = $this->asciiSafe((string) ($payload['basket_name'] ?? 'Randevu Ajandam Uyelik'));
        // Direkt API resmi örnek: json_encode sepet (base64 YOK — base64 iFrame'e özel)
        // @see https://dev.paytr.com/direkt-api/direkt-api-1-adim PHP örneği
        $userBasket = json_encode([[$basketName, $paymentAmount, 1]], JSON_UNESCAPED_UNICODE);

        $currency = 'TL';
        $testMode = $this->testMode ? '1' : '0';
        $paymentType = 'card';
        // Direkt API: installment_count zorunlu (0 = peşin). no_installment iFrame alanıdır, burada yok.
        $installmentCount = '0';
        // İlk abonelik ödemesi: 3D Secure
        $non3d = (string) ($payload['non_3d'] ?? '0');

        // Token: merchant_id + user_ip + merchant_oid + email + payment_amount + payment_type + installment_count + currency + test_mode + non_3d
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

        $merchantOkUrl = (string) ($payload['merchant_ok_url'] ?? route('frontend.odeme.paytr.3d.ok'));
        $merchantFailUrl = (string) ($payload['merchant_fail_url'] ?? route('frontend.odeme.paytr.3d.fail'));

        $cardNumber = preg_replace('/\D+/', '', (string) ($payload['card_number'] ?? ''));
        $ccOwner = Str::limit($this->asciiSafe((string) ($payload['card_owner'] ?? $payload['cc_owner'] ?? '')), 50, '');
        $expiryMonth = preg_replace('/\D+/', '', (string) ($payload['expiry_month'] ?? ''));
        $expiryMonth = str_pad($expiryMonth !== '' ? (string) ((int) $expiryMonth) : '', 2, '0', STR_PAD_LEFT);
        if ($expiryMonth === '00') {
            $expiryMonth = '01';
        }
        $expiryYear = preg_replace('/\D+/', '', (string) ($payload['expiry_year'] ?? ''));
        if (strlen($expiryYear) === 4) {
            $expiryYear = substr($expiryYear, -2);
        }
        $cvv = preg_replace('/\D+/', '', (string) ($payload['card_cvv'] ?? $payload['cvv'] ?? ''));

        $storeCard = (bool) ($payload['store_card'] ?? true);
        $existingUtoken = trim((string) ($payload['utoken'] ?? ''));

        $post = [
            'merchant_id' => $this->merchantId,
            'user_ip' => $userIp,
            'merchant_oid' => $merchantOid,
            'email' => $email,
            'payment_type' => $paymentType,
            // Resmi örnek: "100.99" — string, 2 ondalık (integer kuruş DEĞİL)
            'payment_amount' => $paymentAmount,
            'installment_count' => $installmentCount,
            'currency' => $currency,
            'test_mode' => $testMode,
            'non_3d' => $non3d,
            'merchant_ok_url' => $merchantOkUrl,
            'merchant_fail_url' => $merchantFailUrl,
            'user_name' => $userName,
            'user_address' => $userAddress,
            'user_phone' => $userPhone,
            'user_basket' => $userBasket,
            'debug_on' => $this->debugOn ? '1' : '0',
            'client_lang' => 'tr',
            'paytr_token' => $paytrToken,
            'cc_owner' => $ccOwner,
            'card_number' => $cardNumber,
            'expiry_month' => $expiryMonth,
            'expiry_year' => $expiryYear,
            'cvv' => $cvv,
        ];

        if ($storeCard) {
            $post['store_card'] = '1';
            if ($existingUtoken !== '') {
                $post['utoken'] = $existingUtoken;
            }
        }

        try {
            $response = Http::asForm()
                ->timeout(35)
                ->withHeaders(['Accept' => 'text/html,application/json'])
                ->post('https://www.paytr.com/odeme', $post);
            $body = $response->body();

            if ($body === '' || $body === null) {
                return ['status' => 'failure', 'errorMessage' => 'PayTR boş yanıt döndürdü.'];
            }

            $decoded = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $st = (string) ($decoded['status'] ?? '');
                if (in_array($st, ['success', 'wait_callback'], true)) {
                    return [
                        'status' => $st === 'wait_callback' ? 'wait_callback' : 'success',
                        'utoken' => (string) ($decoded['utoken'] ?? ''),
                        'ctoken' => (string) ($decoded['ctoken'] ?? ''),
                    ];
                }
                $reason = (string) ($decoded['reason'] ?? $decoded['msg'] ?? $decoded['err_msg'] ?? 'PayTR ödeme reddedildi.');
                Log::error('PayTR direct payment failed', [
                    'reason' => $reason,
                    'merchant_oid' => $merchantOid,
                    'amount' => $paymentAmount,
                    'test_mode' => $testMode,
                ]);

                return ['status' => 'failure', 'errorMessage' => $reason];
            }

            // HTML = 3D Secure formu
            if (str_contains($body, '<') && (str_contains($body, '<form') || str_contains($body, '<!DOCTYPE') || str_contains($body, '<html'))) {
                return ['status' => '3d', 'html' => $body];
            }

            Log::warning('PayTR direct unexpected response', ['merchant_oid' => $merchantOid, 'body' => substr($body, 0, 300)]);

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
        $email = $this->asciiSafe(trim($email));

        return Str::limit($email !== '' ? $email : 'info@randevuajandam.com', 100, '');
    }

    /** PayTR alanlarında Türkçe karakter / riskli sembolleri sadeleştir. */
    protected function asciiSafe(string $text): string
    {
        $map = [
            'ı' => 'i', 'İ' => 'I', 'ş' => 's', 'Ş' => 'S', 'ğ' => 'g', 'Ğ' => 'G',
            'ü' => 'u', 'Ü' => 'U', 'ö' => 'o', 'Ö' => 'O', 'ç' => 'c', 'Ç' => 'C',
        ];
        $text = strtr($text, $map);
        // Kontrol karakterlerini at
        $text = preg_replace('/[\x00-\x1F\x7F]/', '', $text) ?? $text;

        return trim($text);
    }
}
