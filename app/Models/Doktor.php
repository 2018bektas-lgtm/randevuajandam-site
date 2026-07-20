<?php

namespace App\Models;

use App\Support\HasTwoFactorAuth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Doktor extends Authenticatable
{
    use HasFactory, HasTwoFactorAuth, Notifiable, SoftDeletes;

    protected $table = 'doktorlar';

    protected $fillable = [
        'ad_soyad',
        'slug',
        'e_posta',
        'sifre',
        'telefon',
        'tc_kimlik_no',
        'il_id',
        'ilce_id',
        'tur',
        'klinik_adi',
        'paket_id',
        'odeme_periyodu',
        'uyelik_baslangic',
        'uyelik_bitis',
        'deneme_kullanildi',
        'aktif_mi',
        'platformda_gorunur',
        'unvan',
        'uzmanlik_alani',
        'mezuniyet',
        'biyografi',
        'adres',
        'enlem',
        'boylam',
        'profil_resmi',
        'instagram',
        'facebook',
        'twitter',
        'linkedin',
        'youtube',
        'web_sitesi',
        'iyzico_subscription_reference_code',
        'iyzico_subscription_status',
        'abonelik_yenileme_kapali',
        'abonelik_iptal_at',
        'abonelik_iptal_nedeni',
        'klinik_id',
        'klinik_rolu',
        'klinik_katilma_tarihi',
        'klinik_aktif_mi',
        'komisyon_orani',
        'klinik_yetkileri',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($doktor) {
            if (empty($doktor->slug)) {
                $doktor->slug = self::generateUniqueSlug($doktor);
            }
        });

        static::updating(function ($doktor) {
            if ($doktor->isDirty('ad_soyad') || $doktor->isDirty('unvan') || $doktor->isDirty('il_id') || $doktor->isDirty('ilce_id') || $doktor->isDirty('uzmanlik_alani')) {
                $doktor->slug = self::generateUniqueSlug($doktor);
            }
        });

        static::updated(function ($doktor) {
            \App\Jobs\SendWebhookJob::dispatch('profile.updated', $doktor->toArray(), $doktor->id, $doktor->klinik_id);
        });
    }

    /**
     * Generate unique slug for doctor based on unvan, name, branch, city, district.
     */
    public static function generateUniqueSlug(Doktor $doktor): string
    {
        $baseSlug = Str::slug(($doktor->unvan ? $doktor->unvan.' ' : '').$doktor->ad_soyad);
        $slug = $baseSlug;
        $counter = 1;

        while (self::where('il_id', $doktor->il_id)
            ->where('ilce_id', $doktor->ilce_id)
            ->where('slug', $slug)
            ->where('id', '!=', $doktor->id)
            ->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    protected $hidden = [
        'sifre',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    public function routeNotificationForMail(): string
    {
        return $this->e_posta;
    }

    /**
     * Get the password for the doctor.
     */
    public function getAuthPassword(): string
    {
        return $this->sifre;
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'sifre' => 'hashed',
            'uyelik_baslangic' => 'datetime',
            'uyelik_bitis' => 'datetime',
            'deneme_kullanildi' => 'boolean',
            'abonelik_yenileme_kapali' => 'boolean',
            'abonelik_iptal_at' => 'datetime',
            'aktif_mi' => 'boolean',
            'platformda_gorunur' => 'boolean',
            'mezuniyet' => 'array',
            'enlem' => 'float',
            'boylam' => 'float',
            'klinik_katilma_tarihi' => 'datetime',
            'klinik_aktif_mi' => 'boolean',
            'komisyon_orani' => 'decimal:2',
            'klinik_yetkileri' => 'array',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Get the branches of the doctor.
     */
    public function branslar()
    {
        return $this->belongsToMany(Brans::class, 'doktor_brans', 'doktor_id', 'brans_id');
    }

    /**
     * Get the subscription package of the doctor.
     */
    public function paket()
    {
        return $this->belongsTo(Paket::class, 'paket_id');
    }

    /**
     * Get the city (il) of the doctor.
     */
    public function il(): BelongsTo
    {
        return $this->belongsTo(Il::class, 'il_id');
    }

    /**
     * Get the district (ilce) of the doctor.
     */
    public function ilce(): BelongsTo
    {
        return $this->belongsTo(Ilce::class, 'ilce_id');
    }

    /**
     * Get the blog posts of the doctor.
     */
    public function bloglar(): HasMany
    {
        return $this->hasMany(Blog::class, 'doktor_id');
    }

    /**
     * Get the services of the doctor.
     */
    public function hizmetler(): HasMany
    {
        return $this->hasMany(Hizmet::class, 'doktor_id');
    }

    /**
     * Get the appointment settings of the doctor.
     */
    public function randevuAyari()
    {
        return $this->hasOne(RandevuAyari::class, 'doktor_id');
    }

    /**
     * Get the working hours of the doctor.
     */
    public function calismaSaatleri(): HasMany
    {
        return $this->hasMany(DoktorCalismaSaati::class, 'doktor_id');
    }

    /**
     * Get the leaves/blocks of the doctor.
     */
    public function izinler(): HasMany
    {
        return $this->hasMany(DoktorIzin::class, 'doktor_id');
    }

    /**
     * Get the FAQs of the doctor.
     */
    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class, 'doktor_id');
    }

    /**
     * Get the appointments of the doctor.
     */
    public function randevular(): HasMany
    {
        return $this->hasMany(Randevu::class, 'doktor_id');
    }

    /**
     * Appointment waitlist entries.
     */
    public function beklemeListesi(): HasMany
    {
        return $this->hasMany(BeklemeListesi::class, 'doktor_id');
    }

    public function egitimler(): HasMany
    {
        return $this->hasMany(Egitim::class, 'doktor_id')->orderBy('sira')->orderByDesc('id');
    }

    /**
     * Get the reviews of the doctor.
     */
    public function yorumlar(): HasMany
    {
        return $this->hasMany(Yorum::class, 'doktor_id');
    }

    /**
     * Get the payments of the doctor.
     */
    public function odemeler(): HasMany
    {
        return $this->hasMany(Odeme::class, 'doktor_id');
    }

    /**
     * Get the expenses of the doctor.
     */
    public function giderler(): HasMany
    {
        return $this->hasMany(Gider::class, 'doktor_id');
    }

    /**
     * Get the financial categories of the doctor.
     */
    public function finansKategoriler(): HasMany
    {
        return $this->hasMany(FinansKategori::class, 'doktor_id');
    }

    /**
     * Get the gallery images of the doctor.
     */
    public function galeriler(): HasMany
    {
        return $this->hasMany(DoktorGaleri::class, 'doktor_id')->orderBy('sira');
    }

    /**
     * Get the website installation details of the doctor.
     */
    public function webSite()
    {
        return $this->hasOne(HekimWebSitesi::class, 'doktor_id');
    }

    /**
     * Get the clinic the doctor belongs to.
     */
    public function klinik(): BelongsTo
    {
        return $this->belongsTo(Klinik::class, 'klinik_id');
    }

    /**
     * Check if the doctor belongs to a clinic.
     */
    public function klinikteMi(): bool
    {
        return $this->klinik_id !== null;
    }

    /**
     * Check if the doctor is the clinic owner.
     */
    public function klinikSahibiMi(): bool
    {
        return $this->klinik_rolu === 'sahip';
    }

    /**
     * Check if the doctor is an individual (not in a clinic).
     */
    public function bireyselMi(): bool
    {
        return $this->klinik_id === null;
    }

    /**
     * Get the active subscription package (either individual or clinic's package).
     */
    public function aktifPaket()
    {
        if ($this->klinikteMi()) {
            return $this->klinik ? $this->klinik->paket : null;
        }

        return $this->paket;
    }

    /**
     * Web sitesi paketi ile ana vitrinden gizlenebilir.
     */
    public function canHideFromPlatform(): bool
    {
        $paket = $this->aktifPaket();

        return $paket && $paket->hasFeature('web_sitesi');
    }

    /** Bu pakette ücretsiz deneme hakkı var mı? (bir kez) */
    public function canStartTrial(?Paket $paket = null): bool
    {
        $paket = $paket ?? $this->paket;
        if (! $paket || ! $paket->denemeVarMi()) {
            return false;
        }
        if ($this->klinikteMi()) {
            return false;
        }
        if ((bool) ($this->deneme_kullanildi ?? false)) {
            return false;
        }

        // Aktif ücretli üyelik varsa deneme yok
        if ($this->uyelik_bitis && $this->uyelik_bitis->isFuture() && $this->odeme_periyodu !== 'deneme') {
            return false;
        }

        return true;
    }

    public function isOnTrial(): bool
    {
        return $this->odeme_periyodu === 'deneme'
            && $this->uyelik_bitis
            && $this->uyelik_bitis->isFuture();
    }

    public function isMembershipExpired(): bool
    {
        if ($this->klinikteMi()) {
            $klinik = $this->klinik;

            return $klinik && $klinik->uyelik_bitis && $klinik->uyelik_bitis->isPast();
        }

        return $this->uyelik_bitis && $this->uyelik_bitis->isPast();
    }

    /** Deneme veya üyelik süresi (gün) kalan — yoksa null. */
    public function membershipDaysLeft(): ?int
    {
        if (! $this->uyelik_bitis || $this->uyelik_bitis->isPast()) {
            return null;
        }

        $days = (int) floor(now()->diffInSeconds($this->uyelik_bitis, false) / 86400);

        return max(0, $days);
    }

    /** Aktif üyelik var ve henüz bitmemiş mi? */
    public function hasActiveMembership(): bool
    {
        if ($this->klinikteMi()) {
            return true;
        }

        return $this->paket_id
            && $this->uyelik_bitis
            && $this->uyelik_bitis->isFuture();
    }

    /**
     * Grok tarzı: iptal istendi, dönem sonuna kadar erişim açık, yenileme yok.
     */
    public function isSubscriptionCancelPending(): bool
    {
        return (bool) ($this->abonelik_yenileme_kapali ?? false)
            && $this->hasActiveMembership();
    }

    public function canCancelSubscription(): bool
    {
        if ($this->klinikteMi() && ! $this->klinikSahibiMi()) {
            return false;
        }

        if (! $this->hasActiveMembership()) {
            return false;
        }

        // Zaten iptal / yenileme kapalı
        if ($this->abonelik_yenileme_kapali) {
            return false;
        }

        return true;
    }

    /**
     * Bireysel hekim: web_sitesi paketi var ama site/domain kurulmamış.
     */
    public function needsDoctorWebsiteOnboarding(): bool
    {
        $paket = $this->aktifPaket();
        if (! $paket || ! $paket->hasFeature('web_sitesi')) {
            return false;
        }

        return ! $this->webSite;
    }

    /**
     * Klinik sahibi: klinik_web_sitesi var ama klinik sitesi kurulmamış.
     */
    public function needsClinicWebsiteOnboarding(): bool
    {
        if (! $this->klinikSahibiMi() || ! $this->klinik) {
            return false;
        }
        $paket = $this->klinik->paket ?? $this->aktifPaket();
        if (! $paket || ! $paket->hasFeature('klinik_web_sitesi')) {
            return false;
        }

        return ! $this->klinik->webSite;
    }

    /**
     * Kayıt/ödeme sonrası domain adımına yönlendirilmeli mi?
     */
    public function needsWebsiteDomainOnboarding(): bool
    {
        return $this->needsDoctorWebsiteOnboarding() || $this->needsClinicWebsiteOnboarding();
    }

    /**
     * Onboarding hedefi: hekim veya klinik domain adımı.
     *
     * @return 'doctor'|'clinic'|null
     */
    public function websiteOnboardingTarget(): ?string
    {
        if ($this->needsDoctorWebsiteOnboarding()) {
            return 'doctor';
        }
        if ($this->needsClinicWebsiteOnboarding()) {
            return 'clinic';
        }

        return null;
    }

    /**
     * Ana site arama / profil / sitemap görünürlüğü.
     * Web paketi yoksa bayrak yok sayılır (her zaman listelenir).
     */
    public function isListedOnPlatform(): bool
    {
        if (! $this->aktif_mi) {
            return false;
        }

        if (! $this->canHideFromPlatform()) {
            return true;
        }

        return (bool) $this->platformda_gorunur;
    }

    /**
     * Ana site vitrini: aktif ve (platformda_gorunur VEYA web_sitesi paketi yok).
     * Web paketi yokken gizli bayrak yok sayılır.
     */
    public function scopePlatformdaListelenen($query)
    {
        return $query->where('aktif_mi', true)->where(function ($q) {
            $q->where(function ($inner) {
                $inner->where('platformda_gorunur', true)
                    ->orWhereNull('platformda_gorunur');
            })->orWhereDoesntHave('paket.sistemOzellikleri', function ($sq) {
                $sq->where('kod', 'web_sitesi');
            });
        });
    }

    /**
     * Check if the doctor has a specific permission in their clinic.
     */
    public function hasClinicPermission(string $permission): bool
    {
        // Individual doctors don't have clinic permissions
        if (!$this->klinikteMi()) {
            return false;
        }

        // Clinic Owners and Partners always have full permission
        if ($this->klinik_rolu === 'sahip' || $this->klinik_rolu === 'ortak') {
            return true;
        }

        // Check if permission is set to true in JSON data
        $yetkiler = $this->klinik_yetkileri;
        return is_array($yetkiler) && isset($yetkiler[$permission]) && (bool)$yetkiler[$permission];
    }

    /**
     * Get the average rating of the doctor (approved reviews only).
     */
    public function getOrtalamaPuanAttribute(): ?float
    {
        $ortalama = $this->yorumlar()->onaylandi()->avg('puan');

        return $ortalama ? round($ortalama, 1) : null;
    }

    /**
     * Get the approved review count.
     */
    public function getYorumSayisiAttribute(): int
    {
        return $this->yorumlar()->onaylandi()->count();
    }

    /**
     * Check if the doctor's online booking is open.
     */
    public function getRandevuyaAcikMiAttribute(): bool
    {
        if (! $this->aktif_mi) {
            return false;
        }

        // If no settings exist yet, default to true
        $ayarlar = $this->randevuAyari;
        if ($ayarlar) {
            return (bool) $ayarlar->aktif_mi;
        }

        return true;
    }

    /**
     * Get the public URL for the doctor's profile page.
     */
    public function getProfilUrlAttribute(): string
    {
        $ilSlug = $this->il?->slug ?? 'il';
        $ilceSlug = $this->ilce?->slug ?? 'ilce';

        $bransSlug = 'hekim';
        $brans = $this->branslar->first();
        if ($brans) {
            $bransSlug = $brans->slug;
        }

        return route('frontend.hekim.detay', [
            'il_slug' => $ilSlug,
            'ilce_slug' => $ilceSlug,
            'brans_slug' => $bransSlug,
            'doctor_slug' => $this->slug,
        ]);
    }
}
