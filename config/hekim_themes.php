<?php

/**
 * Hekim (bireysel doktor) web sitesi temaları — platform kaydı / paket metni / API.
 *
 * Gerçek blade paketleri randevuajandam-hekim uygulamasındadır.
 * Klinik temaları buraya eklenmez (klinik ayrı uygulama / ayrı katalog).
 *
 * Geçerli hekim tema id'leri: tema-1 (Hipno), delogis
 */
return [
    'default' => 'tema-1',

    'catalog' => [
        'tema-1' => [
            'id' => 'tema-1',
            'ad' => 'Hipno',
            'name' => 'Hipno',
            'description' => 'Psikoloji ve danışmanlık için premium tasarım. Koyu zemin, altın vurgu, serif başlıklar.',
            'aciklama' => 'Psikoloji ve danışmanlık için premium tasarım. Koyu zemin, altın vurgu, serif başlıklar.',
            'renk' => '#9B9A84',
            'preview' => ['#262626', '#9B9A84', '#F9F9F9'],
            'premium' => false,
            'preview_image' => null,
        ],
        'delogis' => [
            'id' => 'delogis',
            'ad' => 'Delogis',
            'name' => 'Delogis',
            'description' => 'Delogis Home 3: orijinal palet #976147, modern klinik vitrin, blog/hizmet kartları.',
            'aciklama' => 'Delogis Home 3: orijinal palet #976147, modern klinik vitrin, blog/hizmet kartları.',
            'renk' => '#976147',
            'preview' => ['#976147', '#f2edea', '#1a1414'],
            'premium' => true,
            'preview_image' => null,
        ],
    ],
];
