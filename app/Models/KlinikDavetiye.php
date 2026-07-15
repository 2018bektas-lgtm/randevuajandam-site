<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class KlinikDavetiye extends Model
{
    use HasFactory;

    protected $table = 'klinik_davetiyeleri';

    protected $fillable = [
        'klinik_id',
        'davet_eden_id',
        'davet_edilen_eposta',
        'davet_edilen_doktor_id',
        'token',
        'durum',
        'son_kullanma_tarihi',
    ];

    protected function casts(): array
    {
        return [
            'son_kullanma_tarihi' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (KlinikDavetiye $davetiye) {
            if (empty($davetiye->token)) {
                $davetiye->token = Str::uuid()->toString();
            }

            if (empty($davetiye->son_kullanma_tarihi)) {
                $davetiye->son_kullanma_tarihi = now()->addDays(7);
            }
        });
    }

    public function klinik(): BelongsTo
    {
        return $this->belongsTo(Klinik::class, 'klinik_id');
    }

    public function davetEden(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'davet_eden_id');
    }

    public function davetEdilen(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'davet_edilen_doktor_id');
    }

    public function scopeBeklemede($query)
    {
        return $query->where('durum', 'beklemede')
            ->where('son_kullanma_tarihi', '>', now());
    }

    public function scopeSuresiDolmus($query)
    {
        return $query->where('durum', 'beklemede')
            ->where('son_kullanma_tarihi', '<=', now());
    }

    public function suresiDolduMu(): bool
    {
        return $this->son_kullanma_tarihi->isPast();
    }
}
