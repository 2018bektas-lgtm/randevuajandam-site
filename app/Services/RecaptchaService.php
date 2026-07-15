<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Google reCAPTCHA v3 doğrulama.
 */
class RecaptchaService
{
    /**
     * @param  array{site_key?: string|null, secret_key?: string|null}  $override  Public site kendi anahtarları
     */
    public function isConfigured(array $override = []): bool
    {
        $secret = $this->secretKey($override);

        return $secret !== '';
    }

    public function siteKey(array $override = []): string
    {
        $key = trim((string) ($override['site_key'] ?? ''));
        if ($key !== '') {
            return $key;
        }

        try {
            $fromDb = trim((string) (\App\Models\SiteAyari::query()->value('recaptcha_site_key') ?? ''));
            if ($fromDb !== '') {
                return $fromDb;
            }
        } catch (\Throwable) {
            //
        }

        return trim((string) config('recaptcha.site_key', ''));
    }

    public function secretKey(array $override = []): string
    {
        $key = trim((string) ($override['secret_key'] ?? ''));
        if ($key !== '') {
            return $key;
        }

        try {
            $fromDb = trim((string) (\App\Models\SiteAyari::query()->value('recaptcha_secret_key') ?? ''));
            if ($fromDb !== '') {
                return $fromDb;
            }
        } catch (\Throwable) {
            //
        }

        return trim((string) config('recaptcha.secret_key', ''));
    }

    /**
     * @param  array{site_key?: string|null, secret_key?: string|null}  $override
     * @return array{ok: bool, score?: float, action?: string, message?: string, skipped?: bool}
     */
    public function verify(?string $token, string $expectedAction = '', ?string $remoteIp = null, array $override = []): array
    {
        if (! config('recaptcha.enabled', true)) {
            return ['ok' => true, 'skipped' => true, 'message' => 'disabled'];
        }

        $secret = $this->secretKey($override);
        if ($secret === '') {
            if (config('recaptcha.soft_fail_when_unconfigured', true)) {
                return ['ok' => true, 'skipped' => true, 'message' => 'unconfigured'];
            }

            return ['ok' => false, 'message' => 'reCAPTCHA yapılandırılmamış.'];
        }

        $token = trim((string) $token);
        if ($token === '') {
            return ['ok' => false, 'message' => 'Güvenlik doğrulaması eksik. Sayfayı yenileyip tekrar deneyin.'];
        }

        try {
            $response = Http::asForm()
                ->timeout(8)
                ->post((string) config('recaptcha.verify_url'), array_filter([
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $remoteIp,
                ]));

            $data = $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::warning('reCAPTCHA verify network error', ['error' => $e->getMessage()]);
            // Ağ hatasında formu tamamen kilitleme (soft)
            if (config('recaptcha.soft_fail_when_unconfigured', true)) {
                return ['ok' => true, 'skipped' => true, 'message' => 'network_error'];
            }

            return ['ok' => false, 'message' => 'Güvenlik doğrulaması yapılamadı. Tekrar deneyin.'];
        }

        if (empty($data['success'])) {
            return ['ok' => false, 'message' => 'Güvenlik doğrulaması başarısız. Lütfen tekrar deneyin.'];
        }

        $score = isset($data['score']) ? (float) $data['score'] : 1.0;
        $action = (string) ($data['action'] ?? '');
        $threshold = (float) config('recaptcha.score_threshold', 0.5);

        if ($score < $threshold) {
            Log::info('reCAPTCHA low score', ['score' => $score, 'action' => $action, 'ip' => $remoteIp]);

            return [
                'ok' => false,
                'score' => $score,
                'action' => $action,
                'message' => 'Güvenlik kontrolü başarısız (düşük skor). Lütfen daha sonra tekrar deneyin.',
            ];
        }

        if ($expectedAction !== '' && $action !== '' && $action !== $expectedAction) {
            // action mismatch — uyarı ama sıkı engelleme (v3 bazen normalize eder)
            Log::info('reCAPTCHA action mismatch', ['expected' => $expectedAction, 'got' => $action]);
        }

        return ['ok' => true, 'score' => $score, 'action' => $action];
    }
}
