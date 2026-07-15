<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HostingerService
{
    protected string $apiKey;

    protected string $partnerId;

    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = (string) config('services.hostinger.api_key', '');
        $this->partnerId = (string) config('services.hostinger.partner_id', '');
        $this->baseUrl = rtrim((string) config('services.hostinger.base_url', 'https://api.hostinger.com/v1'), '/');
    }

    /**
     * Mock only in non-production when explicitly allowed or placeholder keys present.
     */
    protected function allowsMock(): bool
    {
        if (app()->environment('production')) {
            return false;
        }

        if ((bool) config('services.hostinger.allow_mock', false)) {
            return true;
        }

        $key = $this->apiKey;

        return $key === ''
            || $key === 'sandbox-hostinger-key'
            || str_contains($key, 'xxx')
            || str_contains($key, 'dummy');
    }

    public function getThemes(): array
    {
        return [
            [
                'id' => 'modern',
                'name' => 'Modern Klinik Teması',
                'description' => 'Geniş karşılama ekranı, modern hizmet kartları ve entegre takvim yapısı.',
                'preview_image' => '/assets/images/themes/modern_preview.jpg',
            ],
            [
                'id' => 'minimalist',
                'name' => 'Elegance Minimalist Tema',
                'description' => 'Yüksek tipografi kalitesi ve hekim özgeçmişini ön plana çıkaran sade tasarım.',
                'preview_image' => '/assets/images/themes/minimalist_preview.jpg',
            ],
            [
                'id' => 'pediatrik',
                'name' => 'Çocuk Sağlığı & Renkli Tema',
                'description' => 'Pediatri ve çocuk branşları için samimi renk paleti.',
                'preview_image' => '/assets/images/themes/pediatrik_preview.jpg',
            ],
        ];
    }

    public function createSubdomain(string $domain): array
    {
        if ($this->allowsMock()) {
            Log::info("Hostinger mock: subdomain {$domain}");

            return [
                'status' => 'success',
                'domain_id' => 'h_dom_'.Str::random(12),
                'message' => 'Alan adı başarıyla oluşturuldu (geliştirme/mock).',
                'mock' => true,
            ];
        }

        if ($this->apiKey === '' || $this->partnerId === '') {
            return [
                'status' => 'failure',
                'message' => 'Hostinger API yapılandırması eksik.',
            ];
        }

        $payload = [
            'domain' => $domain,
            'partnerId' => $this->partnerId,
            'type' => 'subdomain',
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl.'/reseller/domains', $payload);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'status' => 'success',
                    'domain_id' => $data['id'] ?? 'h_dom_'.Str::random(12),
                    'message' => 'Alan adı başarıyla oluşturuldu.',
                ];
            }

            return [
                'status' => 'failure',
                'message' => $response->json('message') ?? 'Hostinger API hatası: '.$response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('Hostinger Subdomain Error: '.$e->getMessage());

            return [
                'status' => 'failure',
                'message' => 'Hostinger sunucusu ile iletişim kurulamadı.',
            ];
        }
    }

    public function deployTheme(string $domain, string $theme, int $doktorId): array
    {
        if ($this->allowsMock()) {
            Log::info("Hostinger mock: theme {$theme} on {$domain}");

            return [
                'status' => 'success',
                'message' => 'Tema dosyaları yüklendi (geliştirme/mock).',
                'mock' => true,
            ];
        }

        if ($this->apiKey === '') {
            return [
                'status' => 'failure',
                'message' => 'Hostinger API yapılandırması eksik.',
            ];
        }

        $payload = [
            'domain' => $domain,
            'theme' => $theme,
            'config' => [
                'DB_DATABASE' => config('database.connections.'.config('database.default').'.database'),
                'DOKTOR_ID' => $doktorId,
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl.'/reseller/deploy', $payload);

            if ($response->successful()) {
                return [
                    'status' => 'success',
                    'message' => 'Tema dosyaları başarıyla kuruldu.',
                ];
            }

            return [
                'status' => 'failure',
                'message' => $response->json('message') ?? 'Dosya yükleme hatası: '.$response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('Hostinger Deploy Error: '.$e->getMessage());

            return [
                'status' => 'failure',
                'message' => 'Tema kurulum sunucusu ile iletişim kurulamadı.',
            ];
        }
    }
}
