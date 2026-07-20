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

        try {
            $dbEnabled = \App\Models\SiteAyari::query()->value('recaptcha_enabled');
            if ($dbEnabled === false || $dbEnabled === 0 || $dbEnabled === '0') {
                return ['ok' => true, 'skipped' => true, 'message' => 'disabled_db'];
            }
        } catch (\Throwable) {
            //
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

        $payload = [
            'secret' => $secret,
            'response' => $token,
        ];
        // Yanlış/proxy IP skoru bozabiliyor; yalnızca genel geçer public IP gönder
        if (config('recaptcha.send_remote_ip', false) && $remoteIp && filter_var($remoteIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            $payload['remoteip'] = $remoteIp;
        }

        try {
            $response = Http::asForm()
                ->timeout(8)
                ->post((string) config('recaptcha.verify_url'), $payload);

            $data = $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::warning('reCAPTCHA verify network error', ['error' => $e->getMessage()]);
            if (config('recaptcha.soft_fail_when_unconfigured', true)) {
                return ['ok' => true, 'skipped' => true, 'message' => 'network_error'];
            }

            return ['ok' => false, 'message' => 'Güvenlik doğrulaması yapılamadı. Tekrar deneyin.'];
        }

        if (empty($data['success'])) {
            $codes = $data['error-codes'] ?? [];
            Log::info('reCAPTCHA success=false', ['errors' => $codes, 'ip' => $remoteIp]);

            $hint = 'Güvenlik doğrulaması başarısız. Sayfayı yenileyip tekrar deneyin.';
            if (is_array($codes) && in_array('timeout-or-duplicate', $codes, true)) {
                $hint = 'Doğrulama süresi doldu veya tekrar kullanıldı. Formu bir kez daha gönderin.';
            }
            if (is_array($codes) && (in_array('invalid-input-response', $codes, true) || in_array('missing-input-response', $codes, true))) {
                $hint = 'Güvenlik jetonu geçersiz. Sayfayı yenileyip tekrar deneyin.';
            }

            return ['ok' => false, 'message' => $hint];
        }

        $score = isset($data['score']) ? (float) $data['score'] : 1.0;
        $action = (string) ($data['action'] ?? '');
        $threshold = (float) config('recaptcha.score_threshold', 0.3);
        $floor = (float) config('recaptcha.score_floor', 0.1);

        if ($score < $threshold) {
            Log::info('reCAPTCHA low score', [
                'score' => $score,
                'threshold' => $threshold,
                'action' => $action,
                'hostname' => $data['hostname'] ?? null,
                'ip' => $remoteIp,
            ]);

            // Sınırda skorlar: bot değil, gürültülü istemci (mobil / VPN / gizlilik tarayıcısı)
            if (config('recaptcha.soft_score', true) && $score >= $floor) {
                Log::info('reCAPTCHA soft pass low score', ['score' => $score, 'floor' => $floor]);

                return ['ok' => true, 'score' => $score, 'action' => $action, 'soft' => true];
            }

            return [
                'ok' => false,
                'score' => $score,
                'action' => $action,
                'message' => 'Güvenlik kontrolü başarısız (düşük skor). Sayfayı yenileyip tekrar deneyin; gizli sekme/VPN kapatmayı deneyin.',
            ];
        }

        if ($expectedAction !== '' && $action !== '' && $action !== $expectedAction) {
            // v3 bazen action’ı farklı döndürür — engelleme, sadece log
            Log::info('reCAPTCHA action mismatch', ['expected' => $expectedAction, 'got' => $action, 'score' => $score]);
        }

        return ['ok' => true, 'score' => $score, 'action' => $action];
    }
}
