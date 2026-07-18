<?php

namespace App\Services;

/**
 * Lightweight Turkish/English profanity detector for public-facing text (reviews, notes).
 */
class ProfanityFilter
{
    /** @var list<string> */
    protected array $words;

    public function __construct(?array $words = null)
    {
        $configured = $words ?? config('profanity.words', []);
        $this->words = array_values(array_filter(array_map(
            fn ($w) => $this->normalize((string) $w),
            is_array($configured) ? $configured : []
        )));
    }

    public function contains(string $text): bool
    {
        $normalized = $this->normalize($text);
        if ($normalized === '') {
            return false;
        }

        // Collapse common leetspeak / separators used to bypass filters
        $collapsed = preg_replace('/[*\.\-_\s]+/u', '', $normalized) ?? $normalized;

        foreach ($this->words as $word) {
            if ($word === '') {
                continue;
            }

            // Multi-word phrases (spaces in source)
            if (str_contains($word, ' ')) {
                if (str_contains($normalized, $word)) {
                    return true;
                }

                continue;
            }

            // Short codes (amk, aq) — whole token match only
            if (mb_strlen($word) <= 3) {
                if (preg_match('/(?<![\p{L}\p{N}])'.preg_quote($word, '/').'(?![\p{L}\p{N}])/u', $normalized)) {
                    return true;
                }

                continue;
            }

            if (preg_match('/(?<![\p{L}\p{N}])'.preg_quote($word, '/').'(?![\p{L}\p{N}])/u', $normalized)) {
                return true;
            }

            // Bypass with separators: s.i.k.t.i.r
            $wordCollapsed = str_replace(' ', '', $word);
            if (mb_strlen($wordCollapsed) >= 4 && str_contains($collapsed, $wordCollapsed)) {
                return true;
            }
        }

        return false;
    }

    public function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text), 'UTF-8');
        // Turkish İ/I handling after strtolower
        $map = [
            'ı' => 'i',
            'İ' => 'i',
            'I' => 'i',
            'ş' => 's',
            'Ş' => 's',
            'ğ' => 'g',
            'Ğ' => 'g',
            'ü' => 'u',
            'Ü' => 'u',
            'ö' => 'o',
            'Ö' => 'o',
            'ç' => 'c',
            'Ç' => 'c',
        ];
        $text = strtr($text, $map);
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return $text;
    }
}
