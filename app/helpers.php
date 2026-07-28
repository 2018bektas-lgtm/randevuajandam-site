<?php

if (! function_exists('decode_text')) {
    /**
     * HTML entity decode (çift encode dahil).
     * Örn: &ccedil;ocuk → çocuk, &amp;ccedil; → ç
     */
    function decode_text(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_array($value)) {
            $value = implode("\n\n", array_map(
                static fn ($v) => is_scalar($v) ? (string) $v : '',
                $value
            ));
        }
        $decoded = (string) $value;
        if ($decoded === '') {
            return '';
        }
        for ($i = 0; $i < 4; $i++) {
            $next = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($next === $decoded) {
                break;
            }
            $decoded = $next;
        }

        return $decoded;
    }
}

if (! function_exists('plain_text')) {
    /**
     * Entity decode + HTML strip (liste özetleri).
     */
    function plain_text(mixed $value, ?int $limit = null): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags(decode_text($value))) ?? '');
        if ($limit !== null && $limit > 0) {
            return \Illuminate\Support\Str::limit($text, $limit);
        }

        return $text;
    }
}
