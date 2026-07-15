<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Klinik extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'klinikler';

    protected $fillable = [
        'ad',
        'slug',
        'sahip_doktor_id',
        'paket_id',
        'logo',
        'telefon',
        'e_posta',
        'adres',
        'il_id',
        'ilce_id',
        'enlem',
        'boylam',
        'web_sitesi',
        'vergi_no',
        'vergi_dairesi',
        'aciklama',
        'calisma_saatleri',
        'sosyal_medya',
        'odeme_periyodu',
        'uyelik_baslangic',
        'uyelik_bitis',
        'max_doktor_sayisi',
        'aktif_mi',
        'meta_baslik',
        'meta_aciklama',
    ];

    protected function casts(): array
    {
        return [
            'calisma_saatleri' => 'array',
            'sosyal_medya' => 'array',
            'uyelik_baslangic' => 'datetime',
            'uyelik_bitis' => 'datetime',
            'aktif_mi' => 'boolean',
            'enlem' => 'float',
            'boylam' => 'float',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Klinik $klinik) {
            if (empty($klinik->slug)) {
                $klinik->slug = self::generateUniqueSlug($klinik->ad);
            }
        });

        static::updating(function (Klinik $klinik) {
            if ($klinik->isDirty('ad')) {
                $klinik->slug = self::generateUniqueSlug($klinik->ad, $klinik->id);
            }
        });
    }

    /**
     * Generate unique slug for clinic.
     */
    public static function generateUniqueSlug(string $ad, ?int $excludeId = null): string
    {
        $baseSlug = Str::slug($ad);
        $slug = $baseSlug;
        $counter = 1;

        while (self::where('slug', $slug)
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get the owner doctor of the clinic.
     */
    public function sahipDoktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'sahip_doktor_id');
    }

    /**
     * Get the subscription package of the clinic.
     */
    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class, 'paket_id');
    }

    /**
     * Klinik web sitesi kaydı (domain + durum).
     */
    public function webSite(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(KlinikWebSitesi::class, 'klinik_id');
    }

    /**
     * Klinik API anahtarı (site entegrasyonu).
     */
    public function apiKey(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ApiKey::class, 'klinik_id');
    }

    /**
     * Yalnızca en yüksek paket (Kurumsal) klinik_web_sitesi özelliğine sahiptir.
     */
    public function hasWebSitesiFeature(): bool
    {
        return $this->hasPaketFlag('klinik_web_sitesi');
    }

    /**
     * Klinik paket bayrakları / özellik kodları.
     *
     * @param  string  $flag  toplu_randevu|merkezi_finans|raporlama|hasta_havuzu|klinik_web_sitesi
     */
    public function hasPaketFlag(string $flag): bool
    {
        $paket = $this->paket;
        if (! $paket) {
            return false;
        }

        return match ($flag) {
            'toplu_randevu' => (bool) $paket->toplu_randevu_mi,
            'merkezi_finans' => (bool) $paket->merkezi_finans_mi,
            'raporlama' => (bool) $paket->raporlama_mi,
            'hasta_havuzu' => (bool) $paket->hasta_havuzu_mi,
            'klinik_web_sitesi' => $paket->hasFeature('klinik_web_sitesi'),
            default => false,
        };
    }

    /**
     * Get the city (il) of the clinic.
     */
    public function il(): BelongsTo
    {
        return $this->belongsTo(Il::class, 'il_id');
    }

    /**
     * Get the district (ilce) of the clinic.
     */
    public function ilce(): BelongsTo
    {
        return $this->belongsTo(Ilce::class, 'ilce_id');
    }

    /**
     * Get the doctors of the clinic.
     */
    public function doktorlar(): HasMany
    {
        return $this->hasMany(Doktor::class, 'klinik_id');
    }

    /**
     * Get the staff members of the clinic.
     */
    public function personeller(): HasMany
    {
        return $this->hasMany(KlinikPersonel::class, 'klinik_id');
    }

    /**
     * Get the invitations of the clinic.
     */
    public function davetiyeler(): HasMany
    {
        return $this->hasMany(KlinikDavetiye::class, 'klinik_id');
    }

    /**
     * Get the patients of the clinic (shared pool).
     */
    public function hastalar(): BelongsToMany
    {
        return $this->belongsToMany(Hasta::class, 'klinik_hastalari', 'klinik_id', 'hasta_id')
            ->withPivot('kayit_tarihi', 'notlar')
            ->withTimestamps();
    }

    /**
     * Get the expenses of the clinic.
     */
    public function giderler(): HasMany
    {
        return $this->hasMany(KlinikGider::class, 'klinik_id');
    }

    /**
     * Get the doctor earnings/commissions.
     */
    public function hakedisler(): HasMany
    {
        return $this->hasMany(KlinikHakedis::class, 'klinik_id');
    }

    /**
     * Get the announcements of the clinic.
     */
    public function duyurular(): HasMany
    {
        return $this->hasMany(KlinikDuyuru::class, 'klinik_id');
    }

    /**
     * Check if the doctor limit has been reached.
     */
    public function doktorLimitiDolduMu(): bool
    {
        return $this->doktorlar()->count() >= $this->max_doktor_sayisi;
    }

    /**
     * Check if the staff limit has been reached.
     */
    public function personelLimitiDolduMu(): bool
    {
        $maxPersonel = $this->paket?->max_personel_sayisi;

        if ($maxPersonel === null) {
            return false;
        }

        return $this->personeller()->count() >= $maxPersonel;
    }

    /**
     * Check if the clinic subscription is active.
     */
    public function uyelikAktifMi(): bool
    {
        if (! $this->uyelik_bitis) {
            return true;
        }

        return $this->uyelik_bitis->isFuture();
    }

    /**
     * Get the public URL for the clinic profile page.
     */
    public function getProfilUrlAttribute(): string
    {
        $ilSlug = $this->il?->slug ?? 'il';
        $ilceSlug = $this->ilce?->slug ?? 'ilce';

        return route('frontend.klinik.profil', [
            'il_slug' => $ilSlug,
            'ilce_slug' => $ilceSlug,
            'klinik_slug' => $this->slug,
        ]);
    }
}
