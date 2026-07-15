<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Il extends Model
{
    use HasSlug;

    protected $table = 'iller';

    protected $fillable = [
        'ad',
        'plaka',
        'slug',
    ];

    /**
     * Get the districts for the city.
     */
    public function ilceler(): HasMany
    {
        return $this->hasMany(Ilce::class, 'il_id');
    }

    /**
     * Get the doctors in this city.
     */
    public function doktorlar(): HasMany
    {
        return $this->hasMany(Doktor::class, 'il_id');
    }
}
