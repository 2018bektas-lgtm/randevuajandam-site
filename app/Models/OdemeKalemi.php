<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OdemeKalemi extends Model
{
    protected $table = 'odeme_kalemleri';

    protected $fillable = [
        'odeme_id',
        'tutar',
        'tarih',
        'odeme_yontemi',
        'not',
    ];

    protected function casts(): array
    {
        return [
            'tutar' => 'decimal:2',
            'tarih' => 'date',
        ];
    }

    public function odeme(): BelongsTo
    {
        return $this->belongsTo(Odeme::class, 'odeme_id');
    }
}
