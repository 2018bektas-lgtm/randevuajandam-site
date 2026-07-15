<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gider extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'giderler';

    protected $fillable = [
        'doktor_id',
        'finans_kategori_id',
        'kategori',
        'baslik',
        'tutar',
        'tarih',
        'aciklama',
        'belge_yolu',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'tutar' => 'decimal:2',
            'tarih' => 'date',
        ];
    }

    /**
     * Get the doctor.
     */
    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }

    /**
     * Get the expense category.
     */
    public function finansKategori(): BelongsTo
    {
        return $this->belongsTo(FinansKategori::class, 'finans_kategori_id');
    }

    /**
     * Scope: Filter by category.
     */
    public function scopeKategori($query, string $kategori)
    {
        return $query->where('kategori', $kategori);
    }
}
