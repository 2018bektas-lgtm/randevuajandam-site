<?php

/**
 * Ticari kimlik bilgileri — şirket kurulunca doldurun.
 * Boş bırakılan alanlar sitede "—" veya gizli kalır.
 * env ile de verilebilir (COMPANY_UNVAN vb.).
 */
return [
    'unvan' => env('COMPANY_UNVAN', ''),
    'adres' => env('COMPANY_ADRES', ''),
    'il' => env('COMPANY_IL', ''),
    'vergi_dairesi' => env('COMPANY_VERGI_DAIRESI', ''),
    'vergi_no' => env('COMPANY_VERGI_NO', ''),
    'mersis' => env('COMPANY_MERSIS', ''),
    'verbis' => env('COMPANY_VERBIS', ''),
    'email' => env('COMPANY_EMAIL', 'info@randevuajandam.com'),
    'telefon' => env('COMPANY_TELEFON', '+90 531 991 24 27'),
    'whatsapp' => env('COMPANY_WHATSAPP', '905319912427'),
    'web' => env('COMPANY_WEB', 'https://randevuajandam.com'),
];
