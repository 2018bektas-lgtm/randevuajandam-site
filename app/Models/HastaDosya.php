<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HastaDosya extends Model
{
    protected $table = 'hasta_dosyalar';

    protected $fillable = [
        'doktor_id',
        'hasta_id',
        'baslik',
        'dosya_yolu',
        'orijinal_ad',
        'mime',
        'boyut',
        'not',
    ];

    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }

    public function hasta(): BelongsTo
    {
        return $this->belongsTo(Hasta::class, 'hasta_id');
    }
}
