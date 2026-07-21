<?php

/**
 * Hekim → hekim referans (yüzde modeli, nakit yok).
 */
return [
    'aktif' => filter_var(env('REFERANS_AKTIF', true), FILTER_VALIDATE_BOOLEAN),

    /** Gelen hekim ilk ücretli ödemede indirim % */
    'yuzde_davet_edilen' => (int) env('REFERANS_YUZDE_DAVET_EDILEN', 15),

    /** Davet eden: abonelik süresinin %’si kadar ücretsiz gün */
    'yuzde_davet_eden' => (int) env('REFERANS_YUZDE_DAVET_EDEN', 20),

    /** Aynı davet eden için ayda max başarılı ödül */
    'aylik_limit_davet_eden' => (int) env('REFERANS_AYLIK_LIMIT', 5),

    /** Bu tutarın altı (₺) ödül vermez */
    'min_odeme_tl' => (float) env('REFERANS_MIN_ODEME_TL', 1),

    /** ref cookie gün */
    'cookie_gun' => (int) env('REFERANS_COOKIE_GUN', 30),

    'cookie_name' => 'ra_ref',
];
