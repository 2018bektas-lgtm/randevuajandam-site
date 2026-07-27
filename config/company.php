<?php

/**
 * Ticari kimlik + yurt içi adres (PayTR sanal POS / abonelik şartı:
 * ödeme sayfalarında iletişim ve adres son kullanıcıya açık görünmeli).
 *
 * env: COMPANY_UNVAN, COMPANY_ADRES, COMPANY_ILCE, COMPANY_IL, COMPANY_POSTA_KODU, ...
 */
return [
    'unvan' => env('COMPANY_UNVAN', ''),
    'adres' => env('COMPANY_ADRES', ''),
    'ilce' => env('COMPANY_ILCE', ''),
    'il' => env('COMPANY_IL', ''),
    'posta_kodu' => env('COMPANY_POSTA_KODU', ''),
    'ulke' => env('COMPANY_ULKE', 'Türkiye'),
    'vergi_dairesi' => env('COMPANY_VERGI_DAIRESI', ''),
    'vergi_no' => env('COMPANY_VERGI_NO', ''),
    'mersis' => env('COMPANY_MERSIS', ''),
    'verbis' => env('COMPANY_VERBIS', ''),
    'email' => env('COMPANY_EMAIL', 'info@randevuajandam.com'),
    'telefon' => env('COMPANY_TELEFON', '+90 531 991 24 27'),
    'whatsapp' => env('COMPANY_WHATSAPP', '905319912427'),
    'web' => env('COMPANY_WEB', 'https://randevuajandam.com'),
];
