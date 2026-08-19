<?php

namespace App\Support;

use App\Models\Randevu;

/**
 * Config tabanlı bildirim şablonları ({placeholder} değiştirme).
 */
class BildirimSablonu
{
    /**
     * @param  array<string, string|null>  $extra
     * @return array{mail_subject: string, mail_intro: string, sms: string}
     */
    public static function forRandevu(string $key, Randevu $randevu, array $extra = []): array
    {
        $tpl = (array) config('bildirim_sablonlari.'.$key, []);
        $vars = array_merge(self::varsFromRandevu($randevu), $extra);

        return [
            'mail_subject' => self::render((string) ($tpl['mail_subject'] ?? ''), $vars),
            'mail_intro' => self::render((string) ($tpl['mail_intro'] ?? ''), $vars),
            'sms' => self::clearTurkish(self::render((string) ($tpl['sms'] ?? ''), $vars)),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function varsFromRandevu(Randevu $randevu): array
    {
        $doktor = $randevu->doktor;
        $doktorIsim = trim(($doktor?->unvan ? $doktor->unvan.' ' : '').($doktor?->ad_soyad ?? 'Hekim'));
        $hastaIsim = trim(($randevu->ad ?? '').' '.($randevu->soyad ?? ''));
        if ($hastaIsim === '' && $randevu->hasta) {
            $hastaIsim = (string) ($randevu->hasta->ad_soyad ?? '');
        }
        $tarih = $randevu->tarih?->translatedFormat('d F Y')
            ?? ($randevu->tarih instanceof \DateTimeInterface
                ? $randevu->tarih->format('d.m.Y')
                : (string) $randevu->tarih);

        $isOnline = ($randevu->gorusme_tipi ?? 'yuz_yuze') === 'online';
        $gorusmeLinki = $isOnline ? (string) ($randevu->meeting_url ?? '') : '';

        $gorusmeNotu = '';
        if ($isOnline) {
            $gorusmeNotu = $gorusmeLinki !== ''
                ? 'Online gorusme linki: '.$gorusmeLinki
                : 'Online gorusme; hekiminiz gorusme linkini kisa sure once paylasacaktir.';
        }

        return [
            'hasta' => $hastaIsim !== '' ? $hastaIsim : 'Hasta',
            'doktor' => $doktorIsim !== '' ? $doktorIsim : 'Hekim',
            'tarih' => (string) $tarih,
            'saat' => substr((string) $randevu->saat, 0, 5),
            'hizmet' => (string) ($randevu->hizmet?->ad ?? 'Genel Muayene'),
            'vakit' => '',
            'gorusme_tipi' => $isOnline ? 'Online' : 'Yuz yuze',
            'gorusme_linki' => $gorusmeLinki,
            'gorusme_notu' => $gorusmeNotu,
        ];
    }

    /**
     * @param  array<string, string|null>  $vars
     */
    public static function render(string $template, array $vars): string
    {
        $replace = [];
        foreach ($vars as $k => $v) {
            $replace['{'.$k.'}'] = (string) ($v ?? '');
        }

        return strtr($template, $replace);
    }

    public static function clearTurkish(string $text): string
    {
        $search = ['ç', 'Ç', 'ğ', 'Ğ', 'ı', 'İ', 'ö', 'Ö', 'ş', 'Ş', 'ü', 'Ü'];
        $replace = ['c', 'C', 'g', 'G', 'i', 'I', 'o', 'O', 's', 'S', 'u', 'U'];

        return str_replace($search, $replace, $text);
    }
}
