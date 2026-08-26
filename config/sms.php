<?php

/**
 * SMS driver yapilandirmasi.
 *
 * Tek desteklenen production driver: NetGSM (XML API).
 * 'log' driver'i sadece local/staging icin — production'da kullanilamaz.
 */
return [
    'driver' => env('SMS_DRIVER', 'log'),

    'netgsm' => [
        'user' => env('NETGSM_USER'),
        'pass' => env('NETGSM_PASS'),
        'header' => env('NETGSM_HEADER'),
    ],
];
