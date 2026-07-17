<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'iyzico' => [
        'api_key' => env('IYZICO_API_KEY'),
        'secret_key' => env('IYZICO_SECRET_KEY'),
        // Production default is live API; override with sandbox only in local.
        'base_url' => env('IYZICO_BASE_URL', env('APP_ENV') === 'production'
            ? 'https://api.iyzipay.com'
            : 'https://sandbox-api.iyzipay.com'),
        'webhook_secret' => env('IYZICO_WEBHOOK_SECRET'),
        // Explicit opt-in for mock subscriptions (never auto-mock in production).
        'allow_mock' => env('IYZICO_ALLOW_MOCK', false),
    ],

    'hostinger' => [
        'api_key' => env('HOSTINGER_API_KEY', ''),
        'partner_id' => env('HOSTINGER_PARTNER_ID', ''),
        'base_url' => env('HOSTINGER_BASE_URL', 'https://api.hostinger.com/v1'),
        'allow_mock' => env('HOSTINGER_ALLOW_MOCK', false),
        // Optional DNS target hints shown in mobile/web setup wizard
        'dns_a_record' => env('DNS_A_RECORD', ''),
        'dns_cname_target' => env('DNS_CNAME_TARGET', 'proxy.randevuajandam.com'),
    ],

    'expo_push' => [
        'enabled' => env('EXPO_PUSH_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Mobile IAP (App Store / Play + RevenueCat)
    |--------------------------------------------------------------------------
    | Product IDs: com.randevuajandam.doktor.pkg.{paket_id}.monthly|yearly
    | When REVENUECAT_SECRET_KEY is set, confirmIap verifies subscriber entitlement.
    | REVENUECAT_WEBHOOK_SECRET validates Authorization header on webhook.
    | MOBILE_IAP_TRUST_CLIENT=true is only for staging (trust client transaction_id).
    */
    'mobile_iap' => [
        'trust_client' => (bool) env('MOBILE_IAP_TRUST_CLIENT', false),
        'product_prefix' => env('MOBILE_IAP_PRODUCT_PREFIX', 'com.randevuajandam.doktor.pkg.'),
    ],

    'revenuecat' => [
        'secret_key' => env('REVENUECAT_SECRET_KEY'),
        'webhook_secret' => env('REVENUECAT_WEBHOOK_SECRET'),
        'project_id' => env('REVENUECAT_PROJECT_ID'),
    ],

];
