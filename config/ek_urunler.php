<?php

/**
 * Excel: ek koltuk, SMS kontör paketleri (KDV dahil).
 */
return [
    'sms_paketleri' => [
        '1000' => [
            'adet' => 1000,
            'fiyat' => 450.0,
            'etiket' => '1.000 SMS',
        ],
        '5000' => [
            'adet' => 5000,
            'fiyat' => 1950.0,
            'etiket' => '5.000 SMS',
            'not' => 'Adet başı ≈ ₺0,39',
        ],
    ],

    // Fallback birim fiyatlar (paket alanında yoksa)
    'ek_hekim_aylik' => 650.0,
    'ek_hekim_yillik' => 6240.0,
    'ek_personel_aylik' => 300.0,
    'ek_personel_yillik' => 2880.0,
];
