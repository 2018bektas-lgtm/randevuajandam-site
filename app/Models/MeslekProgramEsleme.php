<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MeslekProgramEsleme extends Model
{
    protected $table = 'meslek_program_eslemeleri';

    protected $fillable = [
        'program_anahtar',
        'unvan_ad',
        'brans_ad',
        'oncelik',
        'auto_onay',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'oncelik' => 'integer',
            'auto_onay' => 'boolean',
            'aktif' => 'boolean',
        ];
    }

    /**
     * YÖK program metnine en iyi eşleşen kural (uzun anahtar öncelikli).
     */
    public static function matchProgram(?string $program): ?self
    {
        $program = self::normalize((string) $program);
        if ($program === '') {
            return null;
        }

        $rules = static::query()
            ->where('aktif', true)
            ->orderByDesc('oncelik')
            ->orderByRaw('CHAR_LENGTH(program_anahtar) DESC')
            ->get();

        $best = null;
        $bestLen = 0;
        foreach ($rules as $rule) {
            $key = self::normalize((string) $rule->program_anahtar);
            if ($key === '') {
                continue;
            }
            if (str_contains($program, $key) && mb_strlen($key) >= $bestLen) {
                // Aynı uzunlukta daha yüksek oncelik zaten orderBy ile önde
                if (mb_strlen($key) > $bestLen || $best === null) {
                    $best = $rule;
                    $bestLen = mb_strlen($key);
                }
            }
        }

        return $best;
    }

    public static function normalize(string $s): string
    {
        $s = trim($s);
        // Mojibake / Windows-1254 kalıntıları
        $s = strtr($s, [
            'Ý' => 'İ', 'ý' => 'ı', 'Þ' => 'Ş', 'þ' => 'ş', 'Ð' => 'Ğ', 'ð' => 'ğ',
        ]);
        $s = strtr(mb_strtoupper($s, 'UTF-8'), [
            'İ' => 'I', 'I' => 'I', 'ı' => 'I', 'i' => 'I',
            'Ş' => 'S', 'ş' => 'S', 'Ğ' => 'G', 'ğ' => 'G',
            'Ü' => 'U', 'ü' => 'U', 'Ö' => 'O', 'ö' => 'O',
            'Ç' => 'C', 'ç' => 'C',
            'Â' => 'A', 'Ê' => 'E',
        ]);
        // ascii düşür (kalan aksanlar)
        if (class_exists(Str::class)) {
            $s = Str::upper(Str::ascii($s));
        }
        $s = preg_replace('/[^A-Z0-9\s\/\-\.]/u', '', $s) ?? $s;
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;

        return trim($s);
    }
}
