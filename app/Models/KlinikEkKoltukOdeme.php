<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KlinikEkKoltukOdeme extends Model
{
    protected $table = 'klinik_ek_koltuk_odemeleri';

    protected $fillable = [
        'klinik_id',
        'doktor_id',
        'adet',
        'periyot',
        'birim_fiyat',
        'tutar',
        'durum',
        'merchant_oid',
        'paytr_token',
        'callback_payload',
        'uyelik_bitis_hizasi',
        'onaylandi_at',
        'okudum_anladim_at',
    ];

    protected function casts(): array
    {
        return [
            'adet' => 'integer',
            'birim_fiyat' => 'decimal:2',
            'tutar' => 'decimal:2',
            'callback_payload' => 'array',
            'uyelik_bitis_hizasi' => 'datetime',
            'onaylandi_at' => 'datetime',
            'okudum_anladim_at' => 'datetime',
        ];
    }

    public function klinik(): BelongsTo
    {
        return $this->belongsTo(Klinik::class);
    }

    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class);
    }

    public function scopeOdenmis($query)
    {
        return $query->where('durum', 'odendi');
    }

    public function scopeBeklemede($query)
    {
        return $query->where('durum', 'beklemede');
    }
}
