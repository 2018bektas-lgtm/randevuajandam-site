<?php

/**
 * WhatsApp Business (Cloud API) yapilandirmasi.
 *
 * Model A: Tek sistem numarasi (varsayilan). Her klinik bu config uzerinden gonderir.
 * Model B: Klinigin kendi WhatsApp numarasi. klinikler.whatsapp_config JSON'i doldurulunca
 *          bu config yerine kliniginki kullanilir. Bkz. Klinik::whatsappAyari().
 *
 * Meta hesap kurulumu, is dogrulamasi, sablon onayi ve kalici token uretimi icin
 * /whatsapp-business-api-entegrasyon.md rehberine bakin.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | API surumu
    |--------------------------------------------------------------------------
    | Meta her yil eski surumleri kapatir. Meta docs -> Graph API changelog
    | ile senkron tutun (developers.facebook.com/docs/graph-api/changelog).
    */
    'api_version' => env('WHATSAPP_API_VERSION', 'v21.0'),
    'base_url'    => env('WHATSAPP_BASE_URL', 'https://graph.facebook.com'),

    /*
    |--------------------------------------------------------------------------
    | Model A - sistem varsayilani
    |--------------------------------------------------------------------------
    */
    'default' => [
        'token'           => env('WHATSAPP_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'waba_id'         => env('WHATSAPP_WABA_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook dogrulama
    |--------------------------------------------------------------------------
    | verify_token: Meta'da webhook eklerken girdiginiz random string.
    | app_secret:   Meta uygulama gizli anahtari (payload HMAC dogrulamasi icin sart).
    */
    'webhook_verify_token' => env('WHATSAPP_WEBHOOK_VERIFY_TOKEN'),
    'app_secret'           => env('WHATSAPP_APP_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Model B - Embedded Signup (Tech Provider olduktan sonra)
    |--------------------------------------------------------------------------
    | app_id: Meta uygulama ID'si (FB.init icin de gerekli).
    | embedded_signup_config_id: Meta > WhatsApp > Configurations altinda uretilir.
    */
    'app_id'                    => env('WHATSAPP_APP_ID'),
    'embedded_signup_config_id' => env('WHATSAPP_EMBEDDED_SIGNUP_CONFIG_ID'),

    /*
    |--------------------------------------------------------------------------
    | Varsayilan sablon dili
    |--------------------------------------------------------------------------
    | Sablon Meta panelinde hangi dille onaylandiysa ayni kod verilmeli (tr).
    */
    'language' => env('WHATSAPP_LANGUAGE', 'tr'),

    /*
    |--------------------------------------------------------------------------
    | Ozellik anahtari
    |--------------------------------------------------------------------------
    | Meta hesap/onay hazir degilse false birakin; Job kota yerine SMS'e dusurur.
    */
    'enabled' => (bool) env('WHATSAPP_ENABLED', false),
];
