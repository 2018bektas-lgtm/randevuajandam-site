<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KlinikGider extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'klinik_giderleri';

    protected $fillable = [
        'klinik_id',
        'kategori',
        'baslik',
        'tutar',
        'tarih',
        'aciklama',
        'belge_yolu',
        'tekrarli_mi',
        'tekrar_periyodu',
    ];

    protected function casts(): array
    {
        return [
            'tutar' => 'decimal:2',
            'tarih' => 'date',
            'tekrarli_mi' => 'boolean',
        ];
    }

    public function klinik(): BelongsTo
    {
        return $this->belongsTo(Klinik::class, 'klinik_id');
    }

    public function scopeKategori($query, string $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    public function scopeTekrarli($query)
    {
        return $query->where('tekrarli_mi', true);
    }

    public function scopeDonemIci($query, $baslangic, $bitis)
    {
        return $query->whereBetween('tarih', [$baslangic, $bitis]);
    }
}
