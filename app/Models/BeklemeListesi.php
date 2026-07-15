<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BeklemeListesi extends Model
{
    protected $table = 'bekleme_listesi';

    protected $fillable = [
        'doktor_id',
        'hasta_id',
        'hizmet_id',
        'ad',
        'soyad',
        'telefon',
        'e_posta',
        'tercih_tarih',
        'tercih_saat',
        'not',
        'durum',
        'bildirildi_at',
    ];

    protected function casts(): array
    {
        return [
            'tercih_tarih' => 'date',
            'bildirildi_at' => 'datetime',
        ];
    }

    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }

    public function hasta(): BelongsTo
    {
        return $this->belongsTo(Hasta::class, 'hasta_id');
    }

    public function hizmet(): BelongsTo
    {
        return $this->belongsTo(Hizmet::class, 'hizmet_id');
    }

    public function getAdSoyadAttribute(): string
    {
        return trim($this->ad.' '.$this->soyad);
    }

    public function scopeAktif($query)
    {
        return $query->whereIn('durum', ['beklemede', 'bildirildi']);
    }

    public function scopeBeklemede($query)
    {
        return $query->where('durum', 'beklemede');
    }
}
