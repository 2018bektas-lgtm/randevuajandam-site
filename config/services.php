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
    ],

];
