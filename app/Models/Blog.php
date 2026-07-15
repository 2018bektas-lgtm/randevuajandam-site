<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends Model
{
    use HasSlug, SoftDeletes;

    protected $table = 'bloglar';

    protected $fillable = [
        'doktor_id',
        'baslik',
        'slug',
        'icerik',
        'resim',
        'meta_baslik',
        'meta_aciklama',
        'meta_anahtar_kelimeler',
        'aktif_mi',
        'okunma_sayisi',
    ];

    protected static function booted(): void
    {
        static::created(function (Blog $blog) {
            \App\Jobs\SendWebhookJob::dispatch('blog.created', $blog->toArray(), $blog->doktor_id);
        });

        static::updated(function (Blog $blog) {
            \App\Jobs\SendWebhookJob::dispatch('blog.updated', $blog->toArray(), $blog->doktor_id);
        });

        static::deleted(function (Blog $blog) {
            \App\Jobs\SendWebhookJob::dispatch('blog.deleted', $blog->toArray(), $blog->doktor_id);
        });
    }

    protected function slugKaynak(): string
    {
        return 'baslik';
    }

    protected function slugKapsam(): array
    {
        return ['doktor_id'];
    }

    /**
     * Get the doctor that owns the blog post.
     */
    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }

    /**
     * Get the public URL for the blog post.
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

        return route('frontend.hekim.blog.detay', [
            'il_slug' => $ilSlug,
            'ilce_slug' => $ilceSlug,
            'brans_slug' => $bransSlug,
            'doctor_slug' => $doktor->slug,
            'blog_slug' => $this->slug,
        ]);
    }
}
