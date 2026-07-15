<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EgitimBasvuru extends Model
{
    protected $table = 'egitim_basvurulari';

    protected $fillable = [
        'egitim_id',
        'doktor_id',
        'hasta_id',
        'ad',
        'soyad',
        'telefon',
        'e_posta',
        'cevaplar',
        'durum',
        'ucret_durumu',
        'ucret_tutari',
        'odenen_tutar',
        'odeme_yontemi',
        'odeme_id',
        'hekim_notu',
        'kvkk_onay',
        'ip',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'cevaplar' => 'array',
            'ucret_tutari' => 'decimal:2',
            'odenen_tutar' => 'decimal:2',
            'kvkk_onay' => 'boolean',
        ];
    }

    public function egitim(): BelongsTo
    {
        return $this->belongsTo(Egitim::class, 'egitim_id');
    }

    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }

    public function hasta(): BelongsTo
    {
        return $this->belongsTo(Hasta::class, 'hasta_id');
    }

    public function odeme(): BelongsTo
    {
        return $this->belongsTo(Odeme::class, 'odeme_id');
    }

    public function getAdSoyadAttribute(): string
    {
        return trim($this->ad.' '.$this->soyad);
    }
}
