<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Yorum extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'yorumlar';

    protected $fillable = [
        'hasta_id',
        'doktor_id',
        'randevu_id',
        'puan',
        'yorum',
        'doktor_yaniti',
        'onay_durumu',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'puan' => 'integer',
        ];
    }

    /**
     * Get the patient that wrote the review.
     */
    public function hasta(): BelongsTo
    {
        return $this->belongsTo(Hasta::class, 'hasta_id');
    }

    /**
     * Get the doctor that the review is for.
     */
    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }

    /**
     * Get the appointment associated with the review.
     */
    public function randevu(): BelongsTo
    {
        return $this->belongsTo(Randevu::class, 'randevu_id');
    }

    /**
     * Scope: only approved reviews.
     */
    public function scopeOnaylandi($query)
    {
        return $query->where('onay_durumu', 'onaylandi');
    }

    /**
     * Scope: pending reviews.
     */
    public function scopeBeklemede($query)
    {
        return $query->where('onay_durumu', 'beklemede');
    }
}
