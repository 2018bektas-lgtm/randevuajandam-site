<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnamFormu extends Model
{
    protected $table = 'onam_formlari';

    protected $fillable = [
        'doktor_id',
        'baslik',
        'icerik',
        'aktif_mi',
        'sira',
    ];

    protected function casts(): array
    {
        return [
            'aktif_mi' => 'boolean',
        ];
    }

    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }

    public function imzalar(): HasMany
    {
        return $this->hasMany(OnamImza::class, 'onam_form_id');
    }
}
