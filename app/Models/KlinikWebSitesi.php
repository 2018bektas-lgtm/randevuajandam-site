<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KlinikWebSitesi extends Model
{
    protected $table = 'klinik_web_siteleri';

    protected $fillable = [
        'klinik_id',
        'domain',
        'tema',
        'durum',
        'hostinger_domain_id',
        'hata_mesaji',
    ];

    public function klinik(): BelongsTo
    {
        return $this->belongsTo(Klinik::class, 'klinik_id');
    }

    public function siteUrl(): string
    {
        $d = $this->domain;
        if (str_starts_with($d, 'http://') || str_starts_with($d, 'https://')) {
            return rtrim($d, '/');
        }

        $scheme = app()->environment('production') ? 'https' : 'http';

        return $scheme.'://'.ltrim($d, '/');
    }
}
