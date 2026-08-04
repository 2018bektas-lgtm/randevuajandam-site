<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnamImza extends Model
{
    protected $table = 'onam_imzalar';

    protected $fillable = [
        'onam_form_id',
        'doktor_id',
        'hasta_id',
        'hasta_ad_soyad',
        'ip',
        'imzalandi_at',
        'not',
    ];

    protected function casts(): array
    {
        return [
            'imzalandi_at' => 'datetime',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(OnamFormu::class, 'onam_form_id');
    }

    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }

    public function hasta(): BelongsTo
    {
        return $this->belongsTo(Hasta::class, 'hasta_id');
    }
}
