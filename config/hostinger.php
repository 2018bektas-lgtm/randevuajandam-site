<?php

/**
 * Hostinger API — domain müsaitlik + satın alma (pakete dahil domain).
 * Token: hPanel → Account → API
 * Domain ücreti müşteriye yansıtılmaz; maliyet paket marjındadır.
 */
return [
    'enabled' => (bool) env('HOSTINGER_ENABLED', false),

    'base_url' => rtrim((string) env('HOSTINGER_BASE_URL', 'https://developers.hostinger.com'), '/'),

    'api_token' => (string) env('HOSTINGER_API_TOKEN', ''),

    'payment_method_id' => env('HOSTINGER_PAYMENT_METHOD_ID')
        ? (int) env('HOSTINGER_PAYMENT_METHOD_ID')
        : null,

    'availability_per_minute' => (int) env('HOSTINGER_AVAILABILITY_PER_MINUTE', 8),

    'catalog_cache_ttl' => (int) env('HOSTINGER_CATALOG_CACHE_TTL', 21600),

    /** MVP: com + net. com.tr faz 2 */
    'default_included_tlds' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('HOSTINGER_DEFAULT_TLDS', 'com,net'))
    ))),

    'timeout' => (int) env('HOSTINGER_TIMEOUT', 25),
];
