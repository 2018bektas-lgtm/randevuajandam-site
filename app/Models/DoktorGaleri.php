<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoktorGaleri extends Model
{
    use HasFactory;

    protected $table = 'doktor_galerileri';

    protected $fillable = [
        'doktor_id',
        'resim_yolu',
        'baslik',
        'sira',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'sira' => 'integer',
        ];
    }

    /**
     * Get the doctor that owns the gallery image.
     */
    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }
}
