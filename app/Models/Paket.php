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
        'ek_doktor_aylik_fiyat',
        'ek_doktor_yillik_fiyat',
        'ek_personel_aylik_fiyat',
        'ek_personel_yillik_fiyat',
        'ozellikler',
        'aktif_mi',
        'iyzico_plan_aylik',
        'iyzico_plan_yillik',
        'max_doktor_sayisi',
        'max_ek_doktor',
        'max_personel_sayisi',
        'max_hasta_sayisi',
        'max_randevu_sayisi',
        'max_hizmet_sayisi',
        'max_biyografi_karakter',
        'max_profil_foto',
        'sms_aylik_kontor',
        'merkezi_finans_mi',
        'toplu_randevu_mi',
        'raporlama_mi',
        'hasta_havuzu_mi',
        'sira',
        'listeleme_oncelik',
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
            'ek_doktor_aylik_fiyat' => 'decimal:2',
            'ek_doktor_yillik_fiyat' => 'decimal:2',
            'ek_personel_aylik_fiyat' => 'decimal:2',
            'ek_personel_yillik_fiyat' => 'decimal:2',
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
            'sms_aylik_kontor' => 'integer',
            'max_hizmet_sayisi' => 'integer',
            'max_biyografi_karakter' => 'integer',
            'max_profil_foto' => 'integer',
            'max_ek_doktor' => 'integer',
            'listeleme_oncelik' => 'integer',
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
        if ($this->relationLoaded('sistemOzellikleri')) {
            return $this->sistemOzellikleri->contains(
                fn ($o) => (string) ($o->kod ?? '') === $featureCode
            );
        }

        return $this->sistemOzellikleri()->where('kod', $featureCode)->exists();
    }

    /**
     * Herhangi biri varsa true (OR).
     *
     * @param  list<string>  $featureCodes
     */
    public function hasAnyFeature(array $featureCodes): bool
    {
        foreach ($featureCodes as $code) {
            if ($code !== '' && $this->hasFeature($code)) {
                return true;
            }
        }

        return false;
    }

    /** Ücretsiz vitrin / 0 TL paket mi? */
    public function ucretsizMi(): bool
    {
        $aylik = (float) ($this->aylik_indirimli_fiyat ?? $this->aylik_fiyat ?? 0);

        return $aylik <= 0;
    }

    /**
     * Paket kartı için kısa özellik özeti (çok uzun listeleri UI'da kısaltır).
     *
     * @return array{items: list<string>, daha_fazla: int, toplam: int}
     */
    public function kartVitrinOzeti(int $limit = 7): array
    {
        $limit = max(3, min(12, $limit));
        $items = [];

        if (($this->max_doktor_sayisi ?? null) !== null && (int) $this->max_doktor_sayisi > 0) {
            $items[] = (int) $this->max_doktor_sayisi.' hekime kadar';
        }
        if (($this->max_personel_sayisi ?? null) !== null && (int) $this->max_personel_sayisi > 0) {
            $items[] = (int) $this->max_personel_sayisi.' sekreter / personel';
        }
        if (! empty($this->merkezi_finans_mi)) {
            $items[] = 'Merkezi finans + muhasebeci';
        }
        if (($this->sms_aylik_kontor ?? 0) > 0) {
            $items[] = 'SMS: aylık '.number_format((int) $this->sms_aylik_kontor, 0, ',', '.').' kontör';
        }
        if (($this->max_randevu_sayisi ?? null) !== null && (int) $this->max_randevu_sayisi > 0) {
            $items[] = 'En fazla '.(int) $this->max_randevu_sayisi.' randevu';
        }

        // Satış odaklı öncelik sırası (kartta önce bunlar)
        $priority = [
            'online_takvim', 'randevu_talepleri', 'bekleme_listesi', 'hasta_kartlari',
            'email_bildirim', 'sms_hatirlatma', 'finans', 'hasta_bakiyeleri',
            'web_sitesi', 'klinik_web_sitesi', 'online_gorusme', 'blog',
            'galeri', 'egitimler', 'finans_rapor', 'destek_oncelikli',
        ];

        $byKod = collect();
        if ($this->relationLoaded('sistemOzellikleri')) {
            $byKod = $this->sistemOzellikleri->keyBy('kod');
        } elseif (method_exists($this, 'sistemOzellikleri')) {
            $byKod = $this->sistemOzellikleri()->get()->keyBy('kod');
        }

        if ($byKod->isNotEmpty()) {
            $seen = [];
            foreach ($priority as $kod) {
                if (! isset($byKod[$kod])) {
                    continue;
                }
                $ad = (string) ($byKod[$kod]->ad ?? '');
                if ($ad === '' || isset($seen[$ad])) {
                    continue;
                }
                // Limit satırlarıyla çakışan metinleri atla
                $lower = mb_strtolower($ad);
                if (str_contains($lower, 'hekim') && str_contains($lower, 'kadar')) {
                    continue;
                }
                $items[] = $ad;
                $seen[$ad] = true;
            }
            foreach ($byKod->sortBy([['sira', 'asc'], ['ad', 'asc']]) as $oz) {
                $ad = (string) ($oz->ad ?? '');
                if ($ad === '' || isset($seen[$ad])) {
                    continue;
                }
                if (! ($oz->vitrin_mi ?? true)) {
                    continue;
                }
                $items[] = $ad;
                $seen[$ad] = true;
            }
        } elseif (is_array($this->ozellikler)) {
            foreach ($this->ozellikler as $oz) {
                $ad = is_string($oz) ? trim($oz) : '';
                if ($ad !== '') {
                    $items[] = $ad;
                }
            }
        }

        $items = array_values(array_unique($items));
        $toplam = count($items);
        $goster = array_slice($items, 0, $limit);

        return [
            'items' => $goster,
            'daha_fazla' => max(0, $toplam - count($goster)),
            'toplam' => $toplam,
        ];
    }
}
