<?php

namespace App\Services;

use App\Models\SiteAyari;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IyzicoSubscriptionService
{
    protected string $apiKey;

    protected string $secretKey;

    protected string $baseUrl;

    public function __construct()
    {
        $settings = SiteAyari::query()->first();
        $this->apiKey = trim((string) ($settings?->iyzico_api_key ?: config('services.iyzico.api_key', '')));
        $this->secretKey = trim((string) ($settings?->iyzico_secret_key ?: config('services.iyzico.secret_key', '')));
        $defaultBase = app()->environment('production')
            ? 'https://api.iyzipay.com'
            : 'https://sandbox-api.iyzipay.com';
        $this->baseUrl = rtrim((string) ($settings?->iyzico_base_url ?: config('services.iyzico.base_url', $defaultBase)), '/');
    }

    /**
     * iyzico aktif ve anahtarlar tanımlı mı?
     * site_ayarlari.iyzico_enabled + api/secret key kontrolü.
     */
    public function isConfigured(): bool
    {
        $settings = SiteAyari::cached();
        $enabled  = (bool) ($settings?->iyzico_enabled ?? config('services.iyzico.enabled', false));
        if (! $enabled) {
            return false;
        }

        return $this->apiKey !== '' && $this->secretKey !== '';
    }

    protected function allowsMock(): bool
    {
        return ! app()->environment('production');
    }

    /**
     * Generate iyzico API v2 signature and headers for Subscription API.
     */
    protected function getHeaders(string $uri, string $requestBody = ''): array
    {
        $randomString = (string) (microtime(true) * 10000).rand(100000, 999999);
        $dataToEncrypt = $randomString.$uri.$requestBody;
        $signature = hash_hmac('sha256', $dataToEncrypt, $this->secretKey);
        $authString = 'apiKey:'.$this->apiKey.'&randomKey:'.$randomString.'&signature:'.$signature;
        $authorization = 'IYZWSv2 '.base64_encode($authString);

        return [
            'Authorization' => $authorization,
            'x-iy-client-header' => 'iyzipay-php-2.0.0',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Subscribe a doctor to an iyzico pricing plan.
     */
    public function subscribeDoctor($doktor, $paket, string $periyot, array $cardDetails): array
    {
        $planCode = $periyot === 'aylik' ? $paket->iyzico_plan_aylik : $paket->iyzico_plan_yillik;

        if (empty($planCode)) {
            if ($this->allowsMock()) {
                $planCode = 'plan-'.Str::slug((string) $paket->ad).'-'.$periyot;
                Log::warning('Iyzico mock plan code used (package missing iyzico plan fields)', [
                    'paket_id' => $paket->id ?? null,
                    'periyot' => $periyot,
                ]);
            } else {
                Log::error('Iyzico plan code missing on package', [
                    'paket_id' => $paket->id ?? null,
                    'periyot' => $periyot,
                ]);

                return [
                    'status' => 'failure',
                    'errorMessage' => 'Ödeme planı tanımlı değil. Lütfen yöneticiye bildirin (iyzico plan kodu eksik).',
                ];
            }
        }

        $nameParts = explode(' ', trim((string) $doktor->ad_soyad));
        $surname = array_pop($nameParts) ?: 'Hekim';
        $name = implode(' ', $nameParts);
        if ($name === '') {
            $name = $surname;
            $surname = 'Hekim';
        }

        $skt = trim((string) ($cardDetails['kart_skt'] ?? ''));
        $sktParts = explode('/', $skt);
        $expireMonth = str_pad(trim($sktParts[0] ?? '12'), 2, '0', STR_PAD_LEFT);
        $expireYear = trim($sktParts[1] ?? '28');
        if (strlen($expireYear) === 2) {
            $expireYear = '20'.$expireYear;
        }

        $cardNumber = preg_replace('/\D/', '', (string) ($cardDetails['kart_no'] ?? '')) ?? '';

        $gsmNumber = preg_replace('/[^\d+]/', '', (string) $doktor->telefon) ?? '';
        if (! str_starts_with($gsmNumber, '+')) {
            if (str_starts_with($gsmNumber, '0')) {
                $gsmNumber = '+90'.substr($gsmNumber, 1);
            } elseif (str_starts_with($gsmNumber, '90')) {
                $gsmNumber = '+'.$gsmNumber;
            } else {
                $gsmNumber = '+90'.$gsmNumber;
            }
        }

        // Production: prefer real TC; sandbox-safe checksum number only outside production.
        $identityNumber = $doktor->tc_kimlik_no ?? null;
        if (! filled($identityNumber)) {
            if (app()->environment('production')) {
                return [
                    'status' => 'failure',
                    'errorMessage' => 'Ödeme için kimlik numarası (T.C.) gereklidir. Profil bilgilerinizi tamamlayın.',
                ];
            }
            $identityNumber = '74300864791';
        }

        $payload = [
            'locale' => 'tr',
            'conversationId' => (string) rand(100000000, 999999999),
            'pricingPlanReferenceCode' => $planCode,
            'subscriptionInitialStatus' => 'ACTIVE',
            'paymentCard' => [
                'cardHolderName' => $cardDetails['kart_sahibi'] ?? '',
                'cardNumber' => $cardNumber,
                'expireYear' => $expireYear,
                'expireMonth' => $expireMonth,
                'cvc' => $cardDetails['kart_cvv'] ?? '',
            ],
            'customer' => [
                'name' => $name,
                'surname' => $surname,
                'identityNumber' => $identityNumber,
                'email' => $doktor->e_posta,
                'gsmNumber' => $gsmNumber,
                'billingAddress' => [
                    'contactName' => $doktor->ad_soyad,
                    'city' => $doktor->il?->ad ?? 'İstanbul',
                    'country' => 'Turkey',
                    'address' => ($doktor->ilce?->ad ?? 'Merkez').', '.($doktor->il?->ad ?? 'İstanbul'),
                    'zipCode' => '34000',
                ],
                'shippingAddress' => [
                    'contactName' => $doktor->ad_soyad,
                    'city' => $doktor->il?->ad ?? 'İstanbul',
                    'country' => 'Turkey',
                    'address' => ($doktor->ilce?->ad ?? 'Merkez').', '.($doktor->il?->ad ?? 'İstanbul'),
                    'zipCode' => '34000',
                ],
            ],
        ];

        $requestJson = json_encode($payload);

        if (! $this->isConfigured()) {
            if ($this->allowsMock()) {
                Log::info('Iyzico Subscription mock mode (missing keys, allow_mock=true): '.$planCode);

                return [
                    'status' => 'success',
                    'referenceCode' => 'sub_mock_'.Str::random(16),
                    'subscriptionStatus' => 'ACTIVE',
                ];
            }

            return [
                'status' => 'failure',
                'errorMessage' => 'Ödeme yapılandırması eksik. Lütfen yöneticinize başvurun.',
            ];
        }

        // Production must not hit sandbox URL accidentally
        if (app()->environment('production') && str_contains($this->baseUrl, 'sandbox')) {
            Log::critical('Iyzico production config points to sandbox URL', ['baseUrl' => $this->baseUrl]);

            return [
                'status' => 'failure',
                'errorMessage' => 'Ödeme yapılandırması hatalı (sandbox). Yöneticiye bildirin.',
            ];
        }

        try {
            $response = Http::withHeaders($this->getHeaders('/v2/subscription/initialize/with-card', $requestJson))
                ->post($this->baseUrl.'/v2/subscription/initialize/with-card', $payload);

            $data = $response->json();

            if ($response->successful() && isset($data['status']) && $data['status'] === 'success') {
                // NON3D yanıtı çoğunlukla data.referenceCode altındadır
                $ref = (string) (
                    data_get($data, 'data.referenceCode')
                    ?? data_get($data, 'referenceCode')
                    ?? data_get($data, 'data.subscriptionReferenceCode')
                    ?? ''
                );
                $subStatus = (string) (
                    data_get($data, 'data.subscriptionStatus')
                    ?? data_get($data, 'subscriptionStatus')
                    ?? 'ACTIVE'
                );

                if ($ref === '' && app()->environment('production')) {
                    Log::error('iyzico subscription success but empty referenceCode', [
                        'keys' => array_keys(is_array($data) ? $data : []),
                    ]);

                    return [
                        'status' => 'failure',
                        'errorMessage' => 'Abonelik oluşturuldu ancak referans kodu alınamadı. Destek ile iletişime geçin (çift çekim riski).',
                    ];
                }

                return [
                    'status' => 'success',
                    'referenceCode' => $ref,
                    'subscriptionStatus' => $subStatus,
                    'customerReferenceCode' => (string) (data_get($data, 'data.customerReferenceCode') ?? ''),
                ];
            }

            Log::error('iyzico subscription failed', [
                'httpStatus' => $response->status(),
                'errorCode' => $data['errorCode'] ?? null,
                'errorMessage' => $data['errorMessage'] ?? null,
            ]);

            return [
                'status' => 'failure',
                'errorMessage' => $data['errorMessage']
                    ?? $data['errorGroup']
                    ?? ('iyzico API bağlantı hatası: '.$response->status()),
                'errorCode' => $data['errorCode'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('iyzico subscription error: '.$e->getMessage());

            if ($this->allowsMock()) {
                Log::warning('Iyzico mock fallback after exception (allow_mock=true)');

                return [
                    'status' => 'success',
                    'referenceCode' => 'sub_mock_err_'.Str::random(12),
                    'subscriptionStatus' => 'ACTIVE',
                ];
            }

            return [
                'status' => 'failure',
                'errorMessage' => 'Ödeme sistemi ile iletişim kurulamadı.',
            ];
        }
    }

    /**
     * Gerçek iyzico abonelik referansı mı? (trial/mock değil)
     */
    public function isRealSubscriptionReference(?string $ref): bool
    {
        $ref = trim((string) $ref);
        if ($ref === '') {
            return false;
        }

        foreach (['sub_mock_', 'trial_', 'free_trial_', 'havale_'] as $prefix) {
            if (str_starts_with($ref, $prefix)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Aboneliği iptal et — iyzico yenilemeyi DURDURUR (CANCELED → yeni çekim yok).
     * Dönem sonu erişim uygulama tarafında uyelik_bitis ile yönetilir.
     *
     * GERÇEK referansta iyzico başarısızsa status=failure döner; yerel iptal yazılmamalı.
     *
     * @return array{status: string, errorMessage?: string, subscriptionStatus?: string, iyzico_canceled?: bool, skipped?: bool}
     */
    public function cancelSubscription(?string $subscriptionReferenceCode): array
    {
        $ref = trim((string) $subscriptionReferenceCode);

        // Referans yok / trial / mock → kartlı yenileme yok
        if (! $this->isRealSubscriptionReference($ref)) {
            return [
                'status' => 'success',
                'skipped' => true,
                'iyzico_canceled' => false,
                'message' => $ref === '' ? 'no_reference' : 'local_or_trial',
                'subscriptionStatus' => 'CANCELED',
            ];
        }

        if (! $this->isConfigured()) {
            if ($this->allowsMock()) {
                return [
                    'status' => 'success',
                    'skipped' => true,
                    'iyzico_canceled' => true,
                    'message' => 'mock_unconfigured',
                    'subscriptionStatus' => 'CANCELED',
                ];
            }

            return [
                'status' => 'failure',
                'errorMessage' => 'Ödeme yapılandırması eksik; iyzico aboneliği iptal edilemedi. Destek ile iletişime geçin (yenileme devam edebilir).',
            ];
        }

        $uri = '/v2/subscription/subscriptions/'.$ref.'/cancel';
        $payload = [
            'locale' => 'tr',
            'conversationId' => (string) (microtime(true) * 10000).random_int(1000, 9999),
        ];
        $requestJson = json_encode($payload);

        try {
            $response = Http::withHeaders($this->getHeaders($uri, $requestJson))
                ->timeout(20)
                ->post($this->baseUrl.$uri, $payload);

            $data = $response->json() ?? [];
            $msg = (string) ($data['errorMessage'] ?? '');
            $code = (string) ($data['errorCode'] ?? '');

            if ($response->successful() && (($data['status'] ?? '') === 'success')) {
                // İsteğe bağlı doğrulama: abonelik detayı CANCELED mi?
                $verified = $this->verifyCanceled($ref);

                Log::info('iyzico subscription canceled', [
                    'ref' => $ref,
                    'verified' => $verified,
                ]);

                return [
                    'status' => 'success',
                    'iyzico_canceled' => true,
                    'subscriptionStatus' => 'CANCELED',
                    'verified' => $verified,
                ];
            }

            // Zaten iptal edilmiş sayılırsa OK
            $lower = strtolower($msg.' '.$code);
            if (
                str_contains($lower, 'cancel')
                || str_contains($lower, 'iptal')
                || str_contains($lower, 'already')
                || in_array($code, ['5001', '5002', '5003'], true)
            ) {
                $verified = $this->verifyCanceled($ref);

                return [
                    'status' => 'success',
                    'skipped' => true,
                    'iyzico_canceled' => true,
                    'message' => $msg ?: 'already_canceled',
                    'subscriptionStatus' => 'CANCELED',
                    'verified' => $verified,
                ];
            }

            Log::error('iyzico subscription cancel FAILED — local cancel must NOT proceed', [
                'http' => $response->status(),
                'code' => $code,
                'message' => $msg,
                'ref' => $ref,
                'body' => $data,
            ]);

            return [
                'status' => 'failure',
                'errorMessage' => $msg !== ''
                    ? 'iyzico iptal hatası: '.$msg
                    : 'Abonelik iyzico tarafında iptal edilemedi (HTTP '.$response->status().'). Yenileme devam edebilir; tekrar deneyin veya destek alın.',
                'errorCode' => $code ?: null,
            ];
        } catch (\Throwable $e) {
            Log::error('iyzico subscription cancel exception: '.$e->getMessage(), ['ref' => $ref]);

            if ($this->allowsMock()) {
                return [
                    'status' => 'success',
                    'skipped' => true,
                    'iyzico_canceled' => true,
                    'message' => 'mock_exception',
                    'subscriptionStatus' => 'CANCELED',
                ];
            }

            return [
                'status' => 'failure',
                'errorMessage' => 'Ödeme sistemi ile iletişim kurulamadı (iptal). Lütfen tekrar deneyin; aksi halde yenileme devam edebilir.',
            ];
        }
    }

    /**
     * GET /v2/subscription/subscriptions/{ref} — durum CANCELED/EXPIRED mi?
     */
    public function verifyCanceled(string $ref): bool
    {
        if (! $this->isConfigured() || ! $this->isRealSubscriptionReference($ref)) {
            return false;
        }

        try {
            $uri = '/v2/subscription/subscriptions/'.$ref;
            // GET için body boş imza
            $response = Http::withHeaders($this->getHeaders($uri, ''))
                ->timeout(15)
                ->get($this->baseUrl.$uri);

            $data = $response->json() ?? [];
            $status = strtoupper((string) (
                data_get($data, 'data.subscriptionStatus')
                ?? data_get($data, 'data.items.0.subscriptionStatus')
                ?? data_get($data, 'subscriptionStatus')
                ?? ''
            ));

            $ok = in_array($status, ['CANCELED', 'CANCELLED', 'EXPIRED'], true);
            if (! $ok) {
                Log::warning('iyzico verifyCanceled: status not canceled', [
                    'ref' => $ref,
                    'status' => $status,
                    'http' => $response->status(),
                ]);
            }

            return $ok;
        } catch (\Throwable $e) {
            Log::warning('iyzico verifyCanceled failed: '.$e->getMessage(), ['ref' => $ref]);

            // Cancel API success sayıldıysa verify opsiyonel; false = bilinmiyor ama cancel OK
            return true;
        }
    }
}
