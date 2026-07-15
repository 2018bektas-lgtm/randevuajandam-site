<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ilce extends Model
{
    use HasSlug;

    protected $table = 'ilceler';

    protected $fillable = [
        'il_id',
        'ad',
        'slug',
    ];

    protected function slugKapsam(): array
    {
        return ['il_id'];
    }

    /**
     * Get the city that owns the district.
     */
    public function il(): BelongsTo
    {
        return $this->belongsTo(Il::class, 'il_id');
    }

    /**
     * Get the doctors in this district.
     */
    public function doktorlar(): HasMany
    {
        return $this->hasMany(Doktor::class, 'ilce_id');
    }
}
