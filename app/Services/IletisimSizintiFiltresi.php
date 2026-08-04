<?php

namespace App\Services;

/**
 * Ücretsiz Vitrin paketinde biyografiye telefon / Instagram vb. sızıntısını engeller (Excel).
 */
class IletisimSizintiFiltresi
{
    /**
     * @return array{temiz: string, engellendi: bool, nedenler: list<string>}
     */
    public static function filtrele(?string $metin, bool $uygula = true): array
    {
        $raw = (string) ($metin ?? '');
        if (! $uygula || $raw === '') {
            return ['temiz' => $raw, 'engellendi' => false, 'nedenler' => []];
        }

        $nedenler = [];
        $temiz = $raw;

        // Telefon: 05xx, +90, 90 5xx, boşluklu
        $phonePattern = '/(?:\+?90[\s\-]?)?0?5\d{2}[\s\-]?\d{3}[\s\-]?\d{2}[\s\-]?\d{2}|\b0\d{10}\b|\b5\d{9}\b/u';
        if (preg_match($phonePattern, $temiz)) {
            $nedenler[] = 'telefon numarası';
            $temiz = preg_replace($phonePattern, '[iletişim gizli]', $temiz) ?? $temiz;
        }

        // Instagram / IG / @handle (kısa handle)
        $socialPatterns = [
            '/instagram\.com\/[A-Za-z0-9._]+/iu',
            '/\b(?:ig|insta|instagram)\s*[:@]?\s*[A-Za-z0-9._]{2,}/iu',
            '/(?<![A-Za-z0-9._])@[A-Za-z0-9._]{3,30}\b/u',
            '/wa\.me\/\d+/iu',
            '/whatsapp/iu',
            '/t\.me\/[A-Za-z0-9_]+/iu',
        ];
        foreach ($socialPatterns as $p) {
            if (preg_match($p, $temiz)) {
                $nedenler[] = 'sosyal medya / iletişim linki';
                $temiz = preg_replace($p, '[iletişim gizli]', $temiz) ?? $temiz;
            }
        }

        // E-posta
        if (preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/iu', $temiz)) {
            $nedenler[] = 'e-posta';
            $temiz = preg_replace('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/iu', '[iletişim gizli]', $temiz) ?? $temiz;
        }

        $nedenler = array_values(array_unique($nedenler));

        return [
            'temiz' => $temiz,
            'engellendi' => $nedenler !== [],
            'nedenler' => $nedenler,
        ];
    }
}
