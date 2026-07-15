<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KlinikHakedis extends Model
{
    use HasFactory;

    protected $table = 'klinik_hakedisler';

    protected $fillable = [
        'klinik_id',
        'doktor_id',
        'donem_baslangic',
        'donem_bitis',
        'toplam_gelir',
        'komisyon_orani',
        'komisyon_tutari',
        'net_hakedis',
        'durum',
    ];

    protected function casts(): array
    {
        return [
            'donem_baslangic' => 'date',
            'donem_bitis' => 'date',
            'toplam_gelir' => 'decimal:2',
            'komisyon_orani' => 'decimal:2',
            'komisyon_tutari' => 'decimal:2',
            'net_hakedis' => 'decimal:2',
        ];
    }

    public function klinik(): BelongsTo
    {
        return $this->belongsTo(Klinik::class, 'klinik_id');
    }

    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }

    public function scopeHesaplandi($query)
    {
        return $query->where('durum', 'hesaplandi');
    }

    public function scopeOnaylandi($query)
    {
        return $query->where('durum', 'onaylandi');
    }

    public function scopeOdendi($query)
    {
        return $query->where('durum', 'odendi');
    }
}
