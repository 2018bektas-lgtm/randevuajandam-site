<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HekimWebSitesi extends Model
{
    use HasFactory;

    protected $table = 'hekim_web_siteleri';

    protected $fillable = [
        'doktor_id',
        'domain',
        'tema',
        'durum',
        'hostinger_domain_id',
        'hata_mesaji',
    ];

    /**
     * Get the doctor that owns this website.
     */
    public function doktor()
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }

    /** @return list<string> */
    public static function hekimTemaIds(): array
    {
        return array_keys((array) config('hekim_themes.catalog', []));
    }

    public static function defaultHekimTema(): string
    {
        return (string) config('hekim_themes.default', 'tema-1');
    }

    /**
     * Bilinen hekim tema id'sine normalize et (eski modern/custom → tema-1).
     */
    public static function normalizeHekimTema(?string $tema): string
    {
        $tema = is_string($tema) ? trim($tema) : '';
        $ids = self::hekimTemaIds();
        if ($tema !== '' && in_array($tema, $ids, true)) {
            return $tema;
        }
        $aliases = [
            'hipno' => 'tema-1',
            'klasik' => 'tema-1',
            'modern' => 'tema-1',
            'minimalist' => 'tema-1',
            'minimal' => 'tema-1',
            'pediatrik' => 'tema-1',
            'custom' => 'tema-1',
            'sicak' => 'tema-1',
            'ocean' => 'tema-1',
        ];
        if ($tema !== '' && isset($aliases[$tema])) {
            return $aliases[$tema];
        }

        return self::defaultHekimTema();
    }
}
