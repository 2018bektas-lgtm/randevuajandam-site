<?php

/**
 * Google reCAPTCHA v3
 *
 * Ana site: SiteAyari veya env (platform sahibi).
 * Public siteler: hekim/klinik site ayarları (kendi domain anahtarları).
 * Anahtar yoksa doğrulama atlanır (local/dev).
 */
return [
    'enabled' => filter_var(env('RECAPTCHA_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

    /** env yedek (panel boşsa) — ana site / API */
    'site_key' => env('RECAPTCHA_SITE_KEY', ''),
    'secret_key' => env('RECAPTCHA_SECRET_KEY', ''),

    /**
     * v3 skor eşiği (0.0–1.0).
     * 0.5 Google varsayılanı; gerçek kullanıcıda (mobil/VPN) sık düşük gelir → 0.3 önerilir.
     */
    'score_threshold' => (float) env('RECAPTCHA_SCORE_THRESHOLD', 0.3),

    /**
     * soft_score=true iken floor ile threshold arası skorlar geçer (loglanır).
     * Gerçek botlar genelde ~0.1 altı kalır.
     */
    'soft_score' => filter_var(env('RECAPTCHA_SOFT_SCORE', true), FILTER_VALIDATE_BOOLEAN),
    'score_floor' => (float) env('RECAPTCHA_SCORE_FLOOR', 0.1),

    /** Proxy arkasında yanlış IP skoru bozabiliyor; varsayılan kapalı */
    'send_remote_ip' => filter_var(env('RECAPTCHA_SEND_REMOTE_IP', false), FILTER_VALIDATE_BOOLEAN),

    'verify_url' => 'https://www.google.com/recaptcha/api/siteverify',

    /** true: anahtar tanımlı değilse formu engelleme */
    'soft_fail_when_unconfigured' => filter_var(env('RECAPTCHA_SOFT_FAIL', true), FILTER_VALIDATE_BOOLEAN),
];
