<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hizmet extends Model
{
    use HasFactory, HasSlug, SoftDeletes;

    protected $table = 'hizmetler';

    protected $fillable = [
        'doktor_id',
        'ad',
        'slug',
        'aciklama',
        'resim',
        'sure',
        'fiyat',
        'aktif_mi',
        'meta_baslik',
        'meta_aciklama',
        'meta_anahtar_kelimeler',
    ];

    protected static function booted(): void
    {
        static::created(function (Hizmet $hizmet) {
            \App\Jobs\SendWebhookJob::dispatch('service.created', $hizmet->toArray(), $hizmet->doktor_id);
        });

        static::updated(function (Hizmet $hizmet) {
            \App\Jobs\SendWebhookJob::dispatch('service.updated', $hizmet->toArray(), $hizmet->doktor_id);
        });

        static::deleted(function (Hizmet $hizmet) {
            \App\Jobs\SendWebhookJob::dispatch('service.deleted', $hizmet->toArray(), $hizmet->doktor_id);
        });
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'aktif_mi' => 'boolean',
            'fiyat' => 'decimal:2',
            'sure' => 'integer',
        ];
    }

    protected function slugKaynak(): string
    {
        return 'ad';
    }

    protected function slugKapsam(): array
    {
        return ['doktor_id'];
    }

    /**
     * Get the doctor that owns the service.
     */
    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }

    /**
     * Get the public URL for the service page.
     */
    public function getUrlAttribute(): string
    {
        $doktor = $this->doktor;
        $ilSlug = $doktor->il?->slug ?? 'il';
        $ilceSlug = $doktor->ilce?->slug ?? 'ilce';

        $bransSlug = 'hekim';
        $brans = $doktor->branslar->first();
        if ($brans) {
            $bransSlug = $brans->slug;
        }

        return route('frontend.hekim.hizmet.detay', [
            'il_slug' => $ilSlug,
            'ilce_slug' => $ilceSlug,
            'brans_slug' => $bransSlug,
            'doctor_slug' => $doktor->slug,
            'hizmet_slug' => $this->slug,
        ]);
    }
}
