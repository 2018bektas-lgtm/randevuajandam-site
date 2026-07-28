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

    /**
     * Hekim web sitesi temaları (randevuajandam-hekim ile aynı id'ler).
     * Klinik temaları ayrıdır — burada listelenmez.
     *
     * @return list<array{id: string, name: string, description: string, preview_image: ?string, premium?: bool, renk?: string}>
     */
    public function getThemes(): array
    {
        $catalog = (array) config('hekim_themes.catalog', []);
        $out = [];
        foreach ($catalog as $id => $t) {
            $out[] = [
                'id' => (string) ($t['id'] ?? $id),
                'name' => (string) ($t['name'] ?? $t['ad'] ?? $id),
                'description' => (string) ($t['description'] ?? $t['aciklama'] ?? ''),
                'preview_image' => $t['preview_image'] ?? null,
                'premium' => (bool) ($t['premium'] ?? false),
                'renk' => $t['renk'] ?? null,
            ];
        }

        return $out;
    }

    /**
     * Hekim tema id doğrulama (tema-1 | delogis).
     */
    public function isValidHekimTheme(?string $tema): bool
    {
        if (! is_string($tema) || $tema === '') {
            return false;
        }
        $catalog = (array) config('hekim_themes.catalog', []);

        return isset($catalog[$tema]);
    }

    public function defaultHekimTheme(): string
    {
        return (string) config('hekim_themes.default', 'tema-1');
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
