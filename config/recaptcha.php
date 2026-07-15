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

    /** v3 skor eşiği (0.0–1.0). Düşük = bot şüphesi */
    'score_threshold' => (float) env('RECAPTCHA_SCORE_THRESHOLD', 0.5),

    'verify_url' => 'https://www.google.com/recaptcha/api/siteverify',

    /** true: anahtar tanımlı değilse formu engelleme (sadece honeypot) */
    'soft_fail_when_unconfigured' => filter_var(env('RECAPTCHA_SOFT_FAIL', true), FILTER_VALIDATE_BOOLEAN),
];
