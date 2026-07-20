<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EdevletDogrulamaLog extends Model
{
    protected $table = 'edevlet_dogrulama_loglari';

    protected $fillable = [
        'barkod',
        'tc_maskeli',
        'durum',
        'sure_ms',
        'hata',
        'ip',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'sure_ms' => 'integer',
        ];
    }

    public static function maskTc(?string $tc): ?string
    {
        $tc = preg_replace('/\D/', '', (string) $tc) ?? '';
        if (strlen($tc) !== 11) {
            return $tc !== '' ? '***' : null;
        }

        return substr($tc, 0, 3).'*****'.substr($tc, -3);
    }
}
