<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Egitim extends Model
{
    use HasSlug, SoftDeletes;

    protected $table = 'egitimler';

    protected $fillable = [
        'doktor_id',
        'baslik',
        'slug',
        'ozet',
        'icerik',
        'kapak',
        'tip',
        'baslangic_at',
        'bitis_at',
        'mekan',
        'online_url',
        'fiyat',
        'odeme_notu',
        'kontenjan',
        'basvuru_acik_mi',
        'basvuru_bitis_at',
        'durum',
        'meta_baslik',
        'meta_aciklama',
        'meta_anahtar_kelimeler',
        'sira',
    ];

    protected function casts(): array
    {
        return [
            'baslangic_at' => 'datetime',
            'bitis_at' => 'datetime',
            'basvuru_bitis_at' => 'datetime',
            'basvuru_acik_mi' => 'boolean',
            'fiyat' => 'decimal:2',
            'kontenjan' => 'integer',
            'sira' => 'integer',
        ];
    }

    protected function slugKaynak(): string
    {
        return 'baslik';
    }

    protected function slugKapsam(): array
    {
        return ['doktor_id'];
    }

    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }

    public function formAlanlari(): HasMany
    {
        return $this->hasMany(EgitimFormAlani::class, 'egitim_id')->orderBy('sira');
    }

    public function basvurular(): HasMany
    {
        return $this->hasMany(EgitimBasvuru::class, 'egitim_id');
    }

    public function scopeYayinda($query)
    {
        return $query->where('durum', 'yayinda');
    }

    public function basvuruAlinabilirMi(): bool
    {
        if ($this->durum !== 'yayinda' || ! $this->basvuru_acik_mi) {
            return false;
        }
        if ($this->basvuru_bitis_at && $this->basvuru_bitis_at->isPast()) {
            return false;
        }
        if ($this->kontenjan !== null) {
            $dolu = $this->basvurular()
                ->whereIn('durum', ['beklemede', 'onaylandi'])
                ->count();
            if ($dolu >= (int) $this->kontenjan) {
                return false;
            }
        }

        return true;
    }

    public function getUrlAttribute(): string
    {
        $doktor = $this->doktor;
        $ilSlug = $doktor?->il?->slug ?? 'il';
        $ilceSlug = $doktor?->ilce?->slug ?? 'ilce';
        $bransSlug = $doktor?->branslar?->first()?->slug ?? 'hekim';

        return route('frontend.hekim.egitim.detay', [
            'il_slug' => $ilSlug,
            'ilce_slug' => $ilceSlug,
            'brans_slug' => $bransSlug,
            'doctor_slug' => $doktor?->slug ?? 'hekim',
            'egitim_slug' => $this->slug,
        ]);
    }

    /**
     * Kapak görseli public URL (DB: uploads/... → /uploads/...).
     */
    public function getKapakUrlAttribute(): ?string
    {
        $path = $this->kapak;
        if (! $path) {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        $path = ltrim(str_replace('\\', '/', $path), '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        return asset($path);
    }

    public function getListeUrlAttribute(): string
    {
        $doktor = $this->doktor;
        $ilSlug = $doktor?->il?->slug ?? 'il';
        $ilceSlug = $doktor?->ilce?->slug ?? 'ilce';
        $bransSlug = $doktor?->branslar?->first()?->slug ?? 'hekim';

        return route('frontend.hekim.egitimler', [
            'il_slug' => $ilSlug,
            'ilce_slug' => $ilceSlug,
            'brans_slug' => $bransSlug,
            'doctor_slug' => $doktor?->slug ?? 'hekim',
        ]);
    }
}
