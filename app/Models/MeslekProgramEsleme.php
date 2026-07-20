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
        $s = Str::upper(Str::ascii(trim($s)));
        // Türkçe karakterler için ek normalize (ascii sonrası)
        $map = ['İ' => 'I', 'I' => 'I', 'Ş' => 'S', 'Ğ' => 'G', 'Ü' => 'U', 'Ö' => 'O', 'Ç' => 'C'];
        $s = strtr(mb_strtoupper(trim($s), 'UTF-8'), [
            'İ' => 'I', 'I' => 'I', 'ı' => 'I', 'i' => 'I',
            'Ş' => 'S', 'ş' => 'S', 'Ğ' => 'G', 'ğ' => 'G',
            'Ü' => 'U', 'ü' => 'U', 'Ö' => 'O', 'ö' => 'O',
            'Ç' => 'C', 'ç' => 'C',
        ]);
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;

        return $s;
    }
}
