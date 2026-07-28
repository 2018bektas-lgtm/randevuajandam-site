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
        if (! $doktor) {
            return route('frontend.hekimler');
        }

        $ilSlug = $doktor->il?->slug ?? 'il';
        $ilceSlug = $doktor->ilce?->slug ?? 'ilce';
        $bransSlug = $doktor->branslar?->first()?->slug ?? 'hekim';
        $doctorSlug = $doktor->slug ?: 'hekim';

        return route('frontend.hekim.hizmet.detay', [
            'il_slug' => $ilSlug,
            'ilce_slug' => $ilceSlug,
            'brans_slug' => $bransSlug,
            'doctor_slug' => $doctorSlug,
            'hizmet_slug' => $this->slug,
        ]);
    }

    /**
     * Public absolute URL for service image.
     * Dosyalar genelde public/uploads veya storage/app/public (symlink /storage) altında.
     */
    public function getResimUrlAttribute(): ?string
    {
        $path = $this->resim;
        if (! $path) {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim(str_replace('\\', '/', (string) $path), '/');
        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        }

        // storage/app/public/uploads/... → storage/uploads/...
        if (str_starts_with($path, 'storage/app/public/')) {
            $path = 'storage/'.substr($path, strlen('storage/app/public/'));
        }

        // public/uploads altında varsa doğrudan /uploads/...
        $publicFile = public_path($path);
        if (is_file($publicFile)) {
            return asset($path);
        }

        // storage symlink: public/storage/uploads/...
        $storageRel = str_starts_with($path, 'storage/') ? $path : 'storage/'.$path;
        if (is_file(public_path($storageRel))) {
            return asset($storageRel);
        }

        // uploads/… çoğu canlı kurulumda public/uploads
        if (str_starts_with($path, 'uploads/')) {
            return asset($path);
        }

        return asset($storageRel);
    }
}
