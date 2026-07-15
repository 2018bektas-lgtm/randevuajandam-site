<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinansKategori extends Model
{
    use HasFactory;

    protected $table = 'finans_kategoriler';

    protected $fillable = [
        'doktor_id',
        'ad',
        'tur',
        'renk',
        'aktif',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    /**
     * Accessor for aktif_mi.
     */
    public function getAktifMiAttribute(): bool
    {
        return (bool) $this->aktif;
    }

    /**
     * Get the doctor.
     */
    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }

    /**
     * Get the expenses in this category.
     */
    public function giderler(): HasMany
    {
        return $this->hasMany(Gider::class, 'finans_kategori_id');
    }

    /**
     * Get the payments in this category.
     */
    public function odemeler(): HasMany
    {
        return $this->hasMany(Odeme::class, 'finans_kategori_id');
    }

    /**
     * Scope: Only income categories.
     */
    public function scopeGelir($query)
    {
        return $query->where('tur', 'gelir');
    }

    /**
     * Scope: Only expense categories.
     */
    public function scopeGider($query)
    {
        return $query->where('tur', 'gider');
    }

    /**
     * Scope: Only active categories.
     */
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }
}
