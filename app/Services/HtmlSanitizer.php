<?php

namespace App\Services;

/**
 * Lightweight HTML allowlist sanitizer (no external package).
 * Strips script/style, event handlers, javascript: URLs; keeps basic rich-text tags.
 */
class HtmlSanitizer
{
    /** Tags allowed in CKEditor-style doctor content */
    protected static string $allowedTags = '<p><br><br/><b><strong><i><em><u><s><ul><ol><li><a><h1><h2><h3><h4><h5><h6><blockquote><pre><code><table><thead><tbody><tr><th><td><img><span><div><hr><sub><sup><figure><figcaption>';

    public static function clean(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        // Remove null bytes
        $html = str_replace("\0", '', $html);

        // Strip script / style / iframe / object / embed completely
        $html = preg_replace('#<\s*(script|style|iframe|object|embed|form|input|button|textarea|select|meta|link|base|svg|math)\b[^>]*>.*?<\s*/\s*\1\s*>#is', '', $html) ?? $html;
        $html = preg_replace('#<\s*(script|style|iframe|object|embed|form|input|button|textarea|select|meta|link|base|svg|math)\b[^>]*/?\s*>#is', '', $html) ?? $html;

        // HTML comments (can hide IE conditionals)
        $html = preg_replace('/<!--.*?-->/s', '', $html) ?? $html;

        // Allowlisted tags only
        $html = strip_tags($html, self::$allowedTags);

        // Attribute / URL cleanup on remaining tags
        $html = preg_replace_callback(
            '/<([a-z0-9]+)(\s[^>]*)?>/i',
            static function (array $m): string {
                $tag = strtolower($m[1]);
                $attrs = $m[2] ?? '';

                if ($attrs === '') {
                    return '<'.$tag.'>';
                }

                $safe = self::filterAttributes($tag, $attrs);

                return $safe === '' ? '<'.$tag.'>' : '<'.$tag.$safe.'>';
            },
            $html
        ) ?? $html;

        return trim($html);
    }

    /**
     * @return string attributes including leading space, or empty
     */
    protected static function filterAttributes(string $tag, string $attrString): string
    {
        $allowed = match ($tag) {
            'a' => ['href', 'title', 'target', 'rel'],
            'img' => ['src', 'alt', 'title', 'width', 'height'],
            'td', 'th' => ['colspan', 'rowspan'],
            'table' => ['border'],
            default => ['class'],
        };

        // Also allow style only for color/align-ish safe subset? Skip style entirely (safer).
        $out = [];
        if (preg_match_all('/([a-zA-Z_:][-a-zA-Z0-9_:.]*)\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s>]+))/', $attrString, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $name = strtolower($m[1]);
                $value = $m[3] !== '' ? $m[3] : ($m[4] !== '' ? $m[4] : ($m[5] ?? ''));

                // Block event handlers and style/on*
                if (str_starts_with($name, 'on') || in_array($name, ['style', 'srcset', 'srcdoc', 'formaction', 'xlink:href'], true)) {
                    continue;
                }
                if (! in_array($name, $allowed, true)) {
                    continue;
                }

                $value = self::sanitizeAttrValue($name, $value);
                if ($value === null) {
                    continue;
                }

                $out[] = $name.'="'.e($value, false).'"';
            }
        }

        // Force safe rel on target=_blank links
        if ($tag === 'a' && in_array('target="_blank"', $out, true)) {
            $hasRel = false;
            foreach ($out as $a) {
                if (str_starts_with($a, 'rel=')) {
                    $hasRel = true;
                    break;
                }
            }
            if (! $hasRel) {
                $out[] = 'rel="noopener noreferrer"';
            }
        }

        return $out === [] ? '' : ' '.implode(' ', $out);
    }

    protected static function sanitizeAttrValue(string $name, string $value): ?string
    {
        $value = trim($value);
        $value = str_replace(["\0", "\r", "\n"], '', $value);

        if (in_array($name, ['href', 'src'], true)) {
            $lower = strtolower($value);
            // Block javascript:/data:/vbscript: (data:image allowed for img src only carefully — block data: entirely)
            if (preg_match('#^\s*(javascript|vbscript|data)\s*:#i', $lower)) {
                return null;
            }
            // Allow relative, http(s), mailto, tel
            if ($value !== '' && ! preg_match('#^(https?:|mailto:|tel:|/|\./|\.\./|#)#i', $value)) {
                // bare paths without scheme ok if start with letter
                if (! preg_match('#^[a-zA-Z0-9_./%-]+$#', $value)) {
                    return null;
                }
            }
        }

        if ($name === 'target' && ! in_array($value, ['_blank', '_self'], true)) {
            return null;
        }

        return mb_substr($value, 0, 2000);
    }
}
