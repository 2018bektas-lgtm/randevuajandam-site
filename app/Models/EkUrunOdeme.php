<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EkUrunOdeme extends Model
{
    protected $table = 'ek_urun_odemeleri';

    protected $fillable = [
        'tip',
        'doktor_id',
        'klinik_id',
        'adet',
        'birim_fiyat',
        'tutar',
        'periyot',
        'kist_orani',
        'durum',
        'merchant_oid',
        'paytr_token',
        'meta',
        'callback_payload',
        'onaylandi_at',
    ];

    protected function casts(): array
    {
        return [
            'adet' => 'integer',
            'birim_fiyat' => 'decimal:2',
            'tutar' => 'decimal:2',
            'kist_orani' => 'decimal:4',
            'meta' => 'array',
            'callback_payload' => 'array',
            'onaylandi_at' => 'datetime',
        ];
    }

    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class);
    }

    public function klinik(): BelongsTo
    {
        return $this->belongsTo(Klinik::class);
    }
}
