<?php

/**
 * Google Calendar entegrasyonu (per-hekim / per-klinik OAuth 2.0).
 *
 * Kurulum: Google Cloud Console -> yeni proje -> "Google Calendar API" enable ->
 *   OAuth consent screen (External, uygulama adi RandevuAjandam) -> Credentials ->
 *   OAuth Client ID (Web application). Authorized redirect URIs:
 *     - https://randevuajandam.com/hekim/google-takvim/callback
 *     - https://randevuajandam.com/hekim/klinik/google-takvim/callback
 *
 * Scopes sensitive kabul edilir; production oncesi Google verification gerekir
 * (test kullanicilari araciligiyla ~100 hesap sinirinda uygulama denenebilir).
 */

return [
    'client_id'     => env('GOOGLE_CALENDAR_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CALENDAR_CLIENT_SECRET'),

    /*
    | Per-guard redirect URI (hekim / klinik). Tek app_url + route uzerinden
    | uretmek yerine sabit tanimlanir; Google Console'daki whitelist ile birebir
    | eslesmeli.
    */
    'redirect_uri_hekim'  => env('GOOGLE_CALENDAR_REDIRECT_URI_HEKIM'),
    'redirect_uri_klinik' => env('GOOGLE_CALENDAR_REDIRECT_URI_KLINIK'),

    /*
    | Google -> RandevuAjandam push notification'i icin verify token.
    | Webhook cagrisinda X-Goog-Channel-Token header'i ile karsilastirilir.
    */
    'webhook_token' => env('GOOGLE_CALENDAR_WEBHOOK_TOKEN'),

    /*
    | Push notification'in ulasacagi HTTPS URL (Google zorunlu HTTPS).
    | Lokal gelistirmede ngrok gibi bir tunel gerekir; bos ise watch atlanir.
    */
    'webhook_url' => env('GOOGLE_CALENDAR_WEBHOOK_URL'),

    /*
    | Uygulama tarafinda takvim etkinliklerinin default TZ'si. Randevu tarih+saat
    | Europe/Istanbul kabul edilir (DB tarih string'i timezone icermiyor).
    */
    'timezone' => env('GOOGLE_CALENDAR_TIMEZONE', 'Europe/Istanbul'),

    /*
    | OAuth scope'lari. events => kendi olusturdugumuz etkinlikleri yonetmek;
    | readonly => hekimin diger etkinliklerini busy olarak okumak.
    */
    'scopes' => [
        'https://www.googleapis.com/auth/calendar.events',
        'https://www.googleapis.com/auth/calendar.readonly',
    ],

    /*
    | Feature switch. false ise controller'lar 503 doner, listener no-op olur.
    | .env'de client_id/secret set edilene kadar false kalabilir.
    */
    'enabled' => (bool) env('GOOGLE_CALENDAR_ENABLED', false),
];
