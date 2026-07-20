<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UyelikOdeme extends Model
{
    protected $table = 'uyelik_odemeleri';

    protected $fillable = [
        'doktor_id',
        'paket_id',
        'odeme_yontemi',
        'provider',
        'odeme_periyodu',
        'tutar',
        'durum',
        'fatura_durumu',
        'havale_referans',
        'merchant_oid',
        'kurulum_verisi',
        'callback_payload',
        'onaylandi_at',
        'onaylayan_yonetici_id',
    ];

    protected function casts(): array
    {
        return [
            'tutar' => 'decimal:2',
            'kurulum_verisi' => 'array',
            'callback_payload' => 'array',
            'onaylandi_at' => 'datetime',
        ];
    }

    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class);
    }

    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class);
    }

    public function onaylayanYonetici(): BelongsTo
    {
        return $this->belongsTo(Yonetici::class, 'onaylayan_yonetici_id');
    }
}
