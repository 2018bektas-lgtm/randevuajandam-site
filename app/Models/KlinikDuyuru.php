<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KlinikDuyuru extends Model
{
    use HasFactory;

    protected $table = 'klinik_duyurulari';

    protected $fillable = [
        'klinik_id',
        'baslik',
        'icerik',
        'onem_derecesi',
        'aktif_mi',
    ];

    protected function casts(): array
    {
        return [
            'aktif_mi' => 'boolean',
        ];
    }

    public function klinik(): BelongsTo
    {
        return $this->belongsTo(Klinik::class, 'klinik_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif_mi', true);
    }

    public function scopeAcil($query)
    {
        return $query->where('onem_derecesi', 'acil');
    }
}
