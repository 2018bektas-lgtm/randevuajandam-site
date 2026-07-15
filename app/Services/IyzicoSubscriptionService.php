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

    public function isConfigured(): bool
    {
        return $this->apiKey !== '' && $this->secretKey !== '';
    }

    /**
     * Mock mode only when explicitly allowed AND environment is local/testing.
     * Never available in production.
     */
    protected function allowsMock(): bool
    {
        if (app()->environment('production')) {
            return false;
        }

        return (bool) config('services.iyzico.allow_mock', false)
            && app()->environment('local', 'testing', 'development');
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
                return [
                    'status' => 'success',
                    'referenceCode' => $data['referenceCode'] ?? ($data['data']['referenceCode'] ?? ''),
                    'subscriptionStatus' => $data['subscriptionStatus'] ?? ($data['data']['subscriptionStatus'] ?? 'ACTIVE'),
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
}
