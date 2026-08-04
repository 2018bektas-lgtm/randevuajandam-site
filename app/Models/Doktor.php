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
        'sms_gonderici_baslik',
        'tc_kimlik_no',
        'diploma_no',
        'edevlet_barkod',
        'meslek_belge_yolu',
        'meslek_dogrulama_durumu',
        'meslek_dogrulama_notu',
        'meslek_dogrulandi_at',
        'meslek_dogrulayan_yonetici_id',
        'il_id',
        'ilce_id',
        'tur',
        'klinik_adi',
        'paket_id',
        'kayit_paket_id',
        'kayit_periyot',
        'odeme_periyodu',
        'uyelik_baslangic',
        'uyelik_bitis',
        'deneme_kullanildi',
        'aktif_mi',
        'platformda_gorunur',
        'son_giris_at',
        'sms_kullanim_donem',
        'sms_kullanim_adet',
        'sms_ek_kontor',
        'garanti_aylik_fiyat',
        'garanti_yillik_fiyat',
        'garanti_bitis',
        'referans_kodu',
        'davet_eden_id',
        'referans_kodu_kullanilan',
        'unvan',
        'uzmanlik_alani',
        'mezuniyet',
        'biyografi',
        'adres',
        'fatura_tipi',
        'fatura_unvan',
        'fatura_tc_vkn',
        'fatura_vergi_dairesi',
        'fatura_adres',
        'fatura_il',
        'fatura_ilce',
        'fatura_posta_kodu',
        'fatura_email',
        'fatura_telefon',
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
        'paytr_recurring_id',
        'paytr_utoken',
        'paytr_ctoken',
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
            'meslek_dogrulandi_at' => 'datetime',
            'aktif_mi' => 'boolean',
            'platformda_gorunur' => 'boolean',
            'son_giris_at' => 'datetime',
            'sms_kullanim_adet' => 'integer',
            'sms_ek_kontor' => 'integer',
            'garanti_aylik_fiyat' => 'decimal:2',
            'garanti_yillik_fiyat' => 'decimal:2',
            'garanti_bitis' => 'datetime',
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
            'paytr_utoken' => 'encrypted',
            'paytr_ctoken' => 'encrypted',
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

    public function onamFormlari(): HasMany
    {
        return $this->hasMany(OnamFormu::class, 'doktor_id');
    }

    public function hastaDosyalar(): HasMany
    {
        return $this->hasMany(HastaDosya::class, 'doktor_id');
    }

    /** Destek SLA / satış vaadi (operasyonel bayrak). */
    public function hasDestekEmail(): bool
    {
        return $this->hasPaketFeature('destek_email');
    }

    public function hasDestekOncelikli(): bool
    {
        return $this->hasPaketFeature('destek_oncelikli');
    }

    public function hasVeriTasima(): bool
    {
        return $this->hasPaketFeature('veri_tasima');
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

    public function davetEden(): BelongsTo
    {
        return $this->belongsTo(self::class, 'davet_eden_id');
    }

    public function referansDavetleri(): HasMany
    {
        return $this->hasMany(ReferansDavet::class, 'davet_eden_id');
    }

    public function davetIleGeldigiKayit()
    {
        return $this->hasOne(ReferansDavet::class, 'davet_edilen_id');
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
     * Aktif üyelik paketi.
     * Kliniğe bağlı hekimlerde klinik paketi (hekim paneli yetkileri de bu paketten okunur).
     */
    public function aktifPaket()
    {
        if ($this->klinikteMi()) {
            return $this->klinik ? $this->klinik->paket : null;
        }

        return $this->paket;
    }

    /**
     * Paket özellik kodu (hakkimda, finans, …). Klinik hekiminde klinik paketine bakılır.
     */
    public function hasPaketFeature(string $featureCode): bool
    {
        $paket = $this->aktifPaket();

        return $paket ? $paket->hasFeature($featureCode) : false;
    }

    /**
     * Web sitesi paketi ile ana vitrinden gizlenebilir (kişisel veya klinik web).
     */
    public function canHideFromPlatform(): bool
    {
        $paket = $this->aktifPaket();
        if (! $paket) {
            return false;
        }

        return $paket->hasFeature('web_sitesi') || $paket->hasFeature('klinik_web_sitesi');
    }

    /**
     * Bireysel hekim web sitesi paketi (web_sitesi özelliği) aktif mi?
     */
    public function hasWebSitesiPaketi(): bool
    {
        return $this->hasPaketFeature('web_sitesi');
    }

    /**
     * Ödeme formundan gelen fatura alanlarını doğrula ve normalize et.
     *
     * @return array{tip: string, unvan: string, tc_vkn: string, vergi_dairesi: ?string, adres: string, il: string, ilce: string, posta_kodu: ?string, email: string, telefon: string}
     */
    public static function normalizeFaturaFromRequest(\Illuminate\Http\Request $request): array
    {
        $tip = $request->input('fatura_tipi', 'bireysel') === 'kurumsal' ? 'kurumsal' : 'bireysel';
        $tcVkn = preg_replace('/\D/', '', (string) $request->input('fatura_tc_vkn', '')) ?? '';

        return [
            'tip' => $tip,
            'unvan' => trim((string) $request->input('fatura_unvan', '')),
            'tc_vkn' => $tcVkn,
            'vergi_dairesi' => trim((string) $request->input('fatura_vergi_dairesi', '')) ?: null,
            'adres' => trim((string) $request->input('fatura_adres', '')),
            'il' => trim((string) $request->input('fatura_il', '')),
            'ilce' => trim((string) $request->input('fatura_ilce', '')),
            'posta_kodu' => trim((string) $request->input('fatura_posta_kodu', '')) ?: null,
            'email' => trim((string) $request->input('fatura_email', '')),
            'telefon' => trim((string) $request->input('fatura_telefon', '')),
        ];
    }

    /**
     * Fatura bilgilerini hekim kaydına yaz (sonraki ödemelerde prefiller).
     *
     * @param  array<string, mixed>  $fatura
     */
    public function saveFaturaBilgileri(array $fatura): void
    {
        $this->forceFill([
            'fatura_tipi' => $fatura['tip'] ?? null,
            'fatura_unvan' => $fatura['unvan'] ?? null,
            'fatura_tc_vkn' => $fatura['tc_vkn'] ?? null,
            'fatura_vergi_dairesi' => $fatura['vergi_dairesi'] ?? null,
            'fatura_adres' => $fatura['adres'] ?? null,
            'fatura_il' => $fatura['il'] ?? null,
            'fatura_ilce' => $fatura['ilce'] ?? null,
            'fatura_posta_kodu' => $fatura['posta_kodu'] ?? null,
            'fatura_email' => $fatura['email'] ?? null,
            'fatura_telefon' => $fatura['telefon'] ?? null,
        ])->save();
    }

    /**
     * Kayıtlı fatura özeti (admin / fatura kesimi).
     *
     * @return array<string, mixed>|null
     */
    public function faturaBilgisiArray(): ?array
    {
        if (empty($this->fatura_tipi) && empty($this->fatura_unvan) && empty($this->fatura_tc_vkn)) {
            return null;
        }

        return [
            'tip' => $this->fatura_tipi,
            'unvan' => $this->fatura_unvan,
            'tc_vkn' => $this->fatura_tc_vkn,
            'vergi_dairesi' => $this->fatura_vergi_dairesi,
            'adres' => $this->fatura_adres,
            'il' => $this->fatura_il,
            'ilce' => $this->fatura_ilce,
            'posta_kodu' => $this->fatura_posta_kodu,
            'email' => $this->fatura_email,
            'telefon' => $this->fatura_telefon,
        ];
    }

    /**
     * Kurulmuş kişisel domain URL'si (varsa). Yoksa null.
     */
    public function publicWebsiteUrl(): ?string
    {
        $host = $this->publicWebsiteHost();
        if ($host === null || $host === '') {
            return null;
        }

        return 'https://'.$host;
    }

    /**
     * Gösterim için domain (örn. pskdanmeliskaratas.com). Yoksa null.
     */
    public function publicWebsiteHost(): ?string
    {
        $site = $this->relationLoaded('webSite') ? $this->webSite : $this->webSite()->first();
        $domain = is_object($site) ? trim((string) ($site->domain ?? '')) : '';
        if ($domain === '') {
            $legacy = trim((string) ($this->web_sitesi ?? ''));
            if ($legacy === '') {
                return null;
            }
            $domain = $legacy;
        }
        $domain = preg_replace('#^https?://#i', '', $domain) ?? $domain;
        $domain = preg_replace('#^//#', '', (string) $domain) ?? $domain;
        $domain = preg_replace('#/.*$#', '', (string) $domain) ?? $domain;
        $domain = preg_replace('#^www\.#i', '', trim((string) $domain)) ?? $domain;
        $domain = rtrim(strtolower((string) $domain), '.');

        return $domain !== '' && str_contains($domain, '.') ? $domain : null;
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
            $klinik = $this->klinik;
            if (! $klinik || ! $klinik->aktif_mi || ! $klinik->paket_id) {
                return false;
            }
            if ($klinik->uyelik_bitis && $klinik->uyelik_bitis->isPast()) {
                return false;
            }

            return true;
        }

        if (! $this->paket_id) {
            return false;
        }

        // Süresi dolmuş
        if ($this->uyelik_bitis && $this->uyelik_bitis->isPast()) {
            return false;
        }

        // uyelik_bitis null: eski kayıt / henüz set edilmemiş — paket varsa “aktif” say
        return true;
    }

    /**
     * Ana sitede (arama, profil, sitemap) listelenmeye uygun mu?
     * Paket seçilmeden / ödeme (veya deneme) başlamadan görünmez.
     */
    public function isEligibleForPublicListing(): bool
    {
        if (! $this->aktif_mi) {
            return false;
        }

        if (! $this->hasActiveMembership()) {
            return false;
        }

        // Bireysel: meslek onayı yoksa (beklemede/red) vitrinde yok
        if (! $this->klinikteMi()) {
            $durum = $this->meslek_dogrulama_durumu;
            if ($durum !== null && $durum !== '' && $durum !== 'onaylandi') {
                return false;
            }
        }

        return true;
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

    /** PayTR kayıtlı kart (utoken+ctoken) var mı? */
    public function hasPaytrSavedCard(): bool
    {
        return filled($this->paytr_utoken) && filled($this->paytr_ctoken);
    }

    /**
     * Dönem sonunda 3D'siz otomatik çekim yapılacak mı?
     * Cron: abonelik:yenile (PAYTR_RECURRING_ENABLED).
     */
    public function willAutoRenew(): bool
    {
        if ($this->isOnTrial() || ($this->odeme_periyodu ?? '') === 'deneme') {
            return false;
        }
        if ($this->abonelik_yenileme_kapali ?? false) {
            return false;
        }
        if (! $this->hasActiveMembership()) {
            return false;
        }
        if (! (bool) config('services.paytr.recurring_enabled', true)) {
            return false;
        }

        // Klinik sahibi: klinik token'ı
        if ($this->klinikSahibiMi() && $this->klinik) {
            return $this->klinik->willAutoRenew();
        }

        return $this->hasPaytrSavedCard();
    }

    /** Tahmini yenileme tutarı (KDV dahil paket fiyatı). */
    public function estimatedRenewalAmount(): ?float
    {
        $paket = $this->aktifPaket() ?? $this->paket;
        if (! $paket) {
            return null;
        }
        $periyot = $this->odeme_periyodu === 'yillik' ? 'yillik' : 'aylik';
        if ($periyot === 'yillik') {
            $t = (float) ($paket->yillik_indirimli_fiyat ?: $paket->yillik_fiyat);

            return $t > 0 ? $t : null;
        }
        $t = (float) ($paket->aylik_indirimli_fiyat ?: $paket->aylik_fiyat);

        return $t > 0 ? $t : null;
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
     * Önce aktif üyelik; web paketi varsa platformda_gorunur bayrağına uy.
     */
    public function isListedOnPlatform(): bool
    {
        if (! $this->isEligibleForPublicListing()) {
            return false;
        }

        // Explicit gizle
        if ($this->platformda_gorunur === false) {
            // Sadece web/klinik-web paketi olanlar “gizle” kullanabilir;
            // yine de false ise listelenmesin (kayıt sonrası default false).
            return false;
        }

        return true;
    }

    /**
     * Ana site vitrini: aktif üyelikli + vitrinde gizli değil.
     */
    public function scopePlatformdaListelenen($query)
    {
        $now = now();

        return $query
            ->where('aktif_mi', true)
            // Vitrinden gizlenmemiş
            ->where(function ($q) {
                $q->where('platformda_gorunur', true)
                    ->orWhereNull('platformda_gorunur');
            })
            ->where(function ($q) use ($now) {
                // Bireysel: paket var + profil_sayfasi + üyelik bitmemiş + meslek onaylı
                $q->where(function ($b) use ($now) {
                    $b->whereNull('klinik_id')
                        ->whereNotNull('paket_id')
                        ->whereHas('paket.sistemOzellikleri', fn ($sq) => $sq->where('kod', 'profil_sayfasi'))
                        ->where(function ($u) use ($now) {
                            $u->whereNull('uyelik_bitis')
                                ->orWhere('uyelik_bitis', '>', $now);
                        })
                        ->where(function ($m) {
                            $m->whereNull('meslek_dogrulama_durumu')
                                ->orWhere('meslek_dogrulama_durumu', '')
                                ->orWhere('meslek_dogrulama_durumu', 'onaylandi');
                        });
                })
                // Klinik üyesi: kliniğin paketi + profil_sayfasi + aktif üyelik
                ->orWhere(function ($c) use ($now) {
                    $c->whereNotNull('klinik_id')
                        ->whereHas('klinik', function ($kq) use ($now) {
                            $kq->where('aktif_mi', true)
                                ->whereNotNull('paket_id')
                                ->whereHas('paket.sistemOzellikleri', fn ($sq) => $sq->where('kod', 'profil_sayfasi'))
                                ->where(function ($u) use ($now) {
                                    $u->whereNull('uyelik_bitis')
                                        ->orWhere('uyelik_bitis', '>', $now);
                                });
                        });
                });
            })
            // Excel: listeleme önceliği yalnızca oncelikli_liste özelliği olan paketlerde
            ->orderByRaw('(
                SELECT CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM paket_ozellik_pivot pop
                        INNER JOIN paket_ozellikleri po ON po.id = pop.ozellik_id
                        WHERE pop.paket_id = p.id AND po.kod = \'oncelikli_liste\'
                    ) THEN COALESCE(p.listeleme_oncelik, 0)
                    ELSE 0
                END
                FROM paketler p
                WHERE p.id = COALESCE(
                    doktorlar.paket_id,
                    (SELECT k.paket_id FROM klinikler k WHERE k.id = doktorlar.klinik_id LIMIT 1)
                )
                LIMIT 1
            ) DESC')
            ->orderByDesc('doktorlar.id');
    }

    /**
     * Vitrinde iletişim (tel/e-posta) gösterilebilir mi?
     */
    public function canShowContactOnProfile(): bool
    {
        return $this->hasPaketFeature('iletisim_profilde');
    }

    /**
     * Vitrinde sosyal / dış bağlantılar gösterilebilir mi?
     */
    public function canShowSocialLinks(): bool
    {
        return $this->hasPaketFeature('dis_baglanti');
    }

    /**
     * Vitrinde onaylı yorumlar gösterilebilir mi?
     */
    public function canShowReviews(): bool
    {
        return $this->hasPaketFeature('yorum_gorunur');
    }

    /**
     * Doğrulanmış rozet (meslek onayı + paket özelliği).
     */
    public function canShowVerifiedBadge(): bool
    {
        return $this->hasPaketFeature('dogrulanmis_rozet') && $this->isMeslekOnayli();
    }

    /**
     * SMS özel gönderici başlığı (sms_baslik).
     */
    public function resolveSmsHeader(): ?string
    {
        if (! $this->hasPaketFeature('sms_baslik')) {
            return null;
        }
        $h = $this->sms_gonderici_baslik ?? null;

        return filled($h) ? (string) $h : null;
    }

    /**
     * Meslek belgesi / kimlik admin onayı tamam mı? (ödeme öncesi zorunlu)
     */
    public function isMeslekOnayli(): bool
    {
        return ($this->meslek_dogrulama_durumu ?? 'beklemede') === 'onaylandi';
    }

    public function isMeslekBeklemede(): bool
    {
        return ($this->meslek_dogrulama_durumu ?? 'beklemede') === 'beklemede';
    }

    public function isMeslekReddedildi(): bool
    {
        return ($this->meslek_dogrulama_durumu ?? '') === 'reddedildi';
    }

    /**
     * Ödeme / paket seçimine geçebilir mi? (admin meslek onayı şart)
     */
    public function canProceedToPayment(): bool
    {
        return $this->isMeslekOnayli();
    }

    /**
     * Kayıt sırasında seçilen paket (henüz ödenmemiş niyet).
     */
    public function kayitPaketi(): BelongsTo
    {
        return $this->belongsTo(Paket::class, 'kayit_paket_id');
    }

    public function mezuniyetBelgeleri()
    {
        return $this->hasMany(DoktorMezuniyetBelgesi::class);
    }

    public function hasKayitPaketNiyeti(): bool
    {
        return ! empty($this->kayit_paket_id);
    }

    /**
     * Meslek onayı / giriş sonrası: her zaman paket listesi.
     * Kayıt niyeti varsa öneri olarak query ile taşınır; hekim özgürce başka paket seçebilir.
     * (Eski davranış: doğrudan ödeme — starter’a kilitleniyordu.)
     */
    public function checkoutUrlAfterMeslek(): string
    {
        $params = [];
        if ($this->hasKayitPaketNiyeti()) {
            $params['oneri'] = (int) $this->kayit_paket_id;
            if (in_array($this->kayit_periyot, ['aylik', 'yillik'], true)) {
                $params['periyot'] = $this->kayit_periyot;
            }
        }

        return route('frontend.hekim.paket_sec', $params);
    }

    /**
     * Ödeme URL’si — yalnızca geçerli aktif paket id ile.
     */
    public function packageCheckoutUrl(int|string $paketId, string $periyot = 'aylik'): string
    {
        $periyot = in_array($periyot, ['aylik', 'yillik'], true) ? $periyot : 'aylik';
        $paket = Paket::query()->where('aktif_mi', true)->find($paketId);
        if (! $paket) {
            return route('frontend.hekim.paket_sec', ['degistir' => 1]);
        }

        $needsDomain = $paket->hasFeature('web_sitesi')
            || $paket->hasFeature('klinik_web_sitesi')
            || (bool) ($paket->domain_dahil_mi ?? false);

        if ($needsDomain) {
            return route('frontend.hekim.onboarding.domain', [
                'paket' => $paket->id,
                'periyot' => $periyot,
            ]);
        }

        return route('frontend.hekim.paket_ode', [
            'paket' => $paket->id,
            'periyot' => $periyot,
        ]);
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
        $bransSlug = $this->branslar?->first()?->slug ?? 'hekim';

        return route('frontend.hekim.detay', [
            'il_slug' => $ilSlug,
            'ilce_slug' => $ilceSlug,
            'brans_slug' => $bransSlug,
            'doctor_slug' => $this->slug ?: 'hekim',
        ]);
    }
}
