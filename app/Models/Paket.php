<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paket extends Model
{
    protected $table = 'paketler';

    protected $fillable = [
        'ad',
        'tur',
        'aciklama',
        'aylik_fiyat',
        'aylik_indirimli_fiyat',
        'yillik_fiyat',
        'yillik_indirimli_fiyat',
        'ozellikler',
        'aktif_mi',
        'iyzico_plan_aylik',
        'iyzico_plan_yillik',
        'max_doktor_sayisi',
        'max_personel_sayisi',
        'max_hasta_sayisi',
        'max_randevu_sayisi',
        'merkezi_finans_mi',
        'toplu_randevu_mi',
        'raporlama_mi',
        'hasta_havuzu_mi',
        'sira',
        'domain_dahil_mi',
        'domain_dahil_yil',
        'domain_dahil_tlds',
        'deneme_gun',
        'one_cikan_mi',
        'etiket',
        'etiket_stil',
    ];

    protected function casts(): array
    {
        return [
            'ozellikler' => 'array',
            'aktif_mi' => 'boolean',
            'merkezi_finans_mi' => 'boolean',
            'toplu_randevu_mi' => 'boolean',
            'raporlama_mi' => 'boolean',
            'hasta_havuzu_mi' => 'boolean',
            'domain_dahil_mi' => 'boolean',
            'domain_dahil_yil' => 'integer',
            'domain_dahil_tlds' => 'array',
            'deneme_gun' => 'integer',
            'one_cikan_mi' => 'boolean',
        ];
    }

    /**
     * Vitrin şeridi: yönetim panelinden ayarlanır (Popüler, Web sitesi, …).
     *
     * @return array{label: string, stil: string}|null
     */
    public function vitrinEtiketi(): ?array
    {
        $label = trim((string) ($this->etiket ?? ''));
        $stil = trim((string) ($this->etiket_stil ?? ''));

        if ($label === '' && ! (bool) ($this->one_cikan_mi ?? false)) {
            return null;
        }

        if ($label === '' && (bool) ($this->one_cikan_mi ?? false)) {
            $label = $this->tur === 'klinik' ? 'Önerilen' : 'Popüler';
            $stil = $stil !== '' ? $stil : 'popular';
        }

        if ($stil === '') {
            $lower = mb_strtolower($label);
            $stil = match (true) {
                str_contains($lower, 'popüler') || str_contains($lower, 'önerilen') => 'popular',
                str_contains($lower, 'web') => 'web',
                str_contains($lower, 'ücretsiz') || str_contains($lower, 'ucretsiz') => 'free',
                str_contains($lower, 'deneme') => 'trial',
                default => 'custom',
            };
        }

        return ['label' => $label, 'stil' => $stil];
    }

    /** Domain pakete dahil mi (ayrı ücret yok). */
    public function domainDahilMi(): bool
    {
        return (bool) ($this->domain_dahil_mi ?? false);
    }

    /** Ücretsiz deneme günü (örn. Başlangıç = 14). */
    public function denemeGun(): int
    {
        return max(0, (int) ($this->deneme_gun ?? 0));
    }

    public function denemeVarMi(): bool
    {
        return $this->denemeGun() > 0;
    }

    /**
     * Check if this is a clinic package.
     */
    public function klinikPaketiMi(): bool
    {
        return $this->tur === 'klinik';
    }

    /**
     * Check if this is an individual package.
     */
    public function bireyselPaketMi(): bool
    {
        return $this->tur === 'bireysel';
    }

    /**
     * Scope: Clinic packages only.
     */
    public function scopeKlinik($query)
    {
        return $query->where('tur', 'klinik');
    }

    /**
     * Scope: Individual packages only.
     */
    public function scopeBireysel($query)
    {
        return $query->where('tur', 'bireysel');
    }

    /**
     * Get system features linked to this package.
     */
    public function sistemOzellikleri()
    {
        return $this->belongsToMany(PaketOzelligi::class, 'paket_ozellik_pivot', 'paket_id', 'ozellik_id');
    }

    /**
     * Check if the package has a specific feature.
     */
    public function hasFeature(string $featureCode): bool
    {
        return $this->sistemOzellikleri()->where('kod', $featureCode)->exists();
    }
}
