<?php

namespace App\Support;

/**
 * Profesyonel SEO başlık / açıklama / anahtar kelime üretici.
 */
class SeoMeta
{
    public static function brand(): string
    {
        return 'Randevu Ajandam';
    }

    public static function siteUrl(): string
    {
        return rtrim((string) config('app.url', 'https://randevuajandam.com'), '/');
    }

    /**
     * Title: anahtar kelime önde, marka sonda (≈50–60 karakter ideal).
     */
    public static function title(string $primary, ?string $secondary = null): string
    {
        $parts = array_filter([$primary, $secondary, self::brand()]);
        $title = implode(' | ', $parts);

        return self::limit($title, 65);
    }

    public static function description(string $text, int $max = 158): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags($text)) ?? '');

        return self::limit($text, $max);
    }

    public static function keywords(array $parts): string
    {
        $out = [];
        foreach ($parts as $p) {
            $p = trim(mb_strtolower((string) $p));
            if ($p === '' || in_array($p, $out, true)) {
                continue;
            }
            $out[] = $p;
        }

        return implode(', ', array_slice($out, 0, 18));
    }

    public static function homeTitle(): string
    {
        return self::title('Online Doktor Randevusu', 'Hekim ve Klinik Bul');
    }

    public static function homeDescription(): string
    {
        return self::description(
            'Türkiye genelinde uzman doktor ve kliniklerden online randevu alın. '
            .'Randevu Ajandam ile hekim arayın, müsait saat seçin, hasta randevunuzu anında oluşturun. '
            .'Diyetisyen, psikolog, diş hekimi ve tüm branşlar.'
        );
    }

    public static function doctorsIndexTitle(?string $il = null, ?string $ilce = null, ?string $brans = null, ?string $arama = null): string
    {
        if ($brans && $il && $ilce) {
            return self::title("{$ilce} {$brans} Doktorları", "{$il} Online Randevu");
        }
        if ($brans && $il) {
            return self::title("{$il} {$brans} Doktorları", 'Online Randevu');
        }
        if ($il && $ilce) {
            return self::title("{$ilce} Doktorları", "{$il} Online Randevu");
        }
        if ($il) {
            return self::title("{$il} Doktor ve Klinik Randevusu", 'Uzman Hekim Bul');
        }
        if ($brans) {
            return self::title("{$brans} Doktorları", 'Online Randevu Al');
        }
        if ($arama) {
            return self::title('"'.$arama.'" Doktor Arama', 'Online Randevu');
        }

        return self::title('Doktor ve Klinik Bul', 'Online Randevu Al');
    }

    public static function doctorsIndexDescription(?string $il = null, ?string $ilce = null, ?string $brans = null): string
    {
        $where = collect([$ilce, $il])->filter()->implode(', ');
        $who = $brans ?: 'uzman doktor ve klinik';

        if ($where !== '') {
            return self::description(
                "{$where} bölgesinde {$who} listesi. Müsait saatleri görün, online randevu alın. "
                ."Randevu Ajandam ile hızlı ve güvenli hasta randevusu."
            );
        }

        return self::description(
            "Türkiye geneli {$who} arayın. Branş, il ve ilçeye göre filtreleyin; "
            .'online randevu oluşturun. Randevu Ajandam — hasta ve hekim buluşma platformu.'
        );
    }

    public static function doctorProfileTitle(string $unvanAd, string $brans, ?string $il = null, ?string $ilce = null): string
    {
        $loc = collect([$ilce, $il])->filter()->implode(' ');
        $primary = trim($unvanAd.' — '.$brans.($loc ? ' '.$loc : '').' Randevu');

        return self::title($primary);
    }

    public static function doctorProfileDescription(string $unvanAd, string $brans, ?string $il = null, ?string $bio = null): string
    {
        $loc = $il ? " {$il} bölgesinde" : '';
        $bio = $bio ? ' '.self::limit(strip_tags($bio), 80) : '';

        return self::description(
            "{$unvanAd}{$loc} {$brans} uzmanı. Online randevu alın, müsait saatleri görün.{$bio} Randevu Ajandam."
        );
    }

    public static function blogIndexTitle(): string
    {
        return self::title('Sağlık Blogu', 'Hekim Yazıları ve Öneriler');
    }

    public static function blogIndexDescription(): string
    {
        return self::description(
            'Uzman hekimlerden sağlık yazıları, hastalık bilgilendirmeleri ve yaşam önerileri. '
            .'Randevu Ajandam blog — güvenilir tıbbi içerik.'
        );
    }

    public static function packagesTitle(): string
    {
        return self::title('Hekim ve Klinik Paket Fiyatları', 'Online Randevu Yazılımı');
    }

    public static function packagesDescription(): string
    {
        return self::description(
            'Hekim ve klinik randevu yazılımı paketleri. Online randevu, hasta yönetimi, web sitesi. '
            .'Randevu Ajandam abonelik fiyatları — deneme ile başlayın.'
        );
    }

    public static function contactTitle(): string
    {
        return self::title('İletişim', 'Destek ve Kurumsal');
    }

    public static function aboutTitle(): string
    {
        return self::title('Hakkımızda', 'Online Randevu Platformu');
    }

    protected static function limit(string $text, int $max): string
    {
        if (mb_strlen($text) <= $max) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, $max - 1)).'…';
    }
}
