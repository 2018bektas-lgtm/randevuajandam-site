<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteAyari extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'site_ayarlari';

    /**
     * Request-scoped + cache-backed site settings (avoids N queries per page).
     */
    public static function cached(): ?self
    {
        return once(function () {
            return Cache::remember('site_ayari:v1', now()->addMinutes(30), function () {
                return static::query()->first();
            });
        });
    }

    /**
     * Clear settings cache after admin updates.
     */
    public static function forgetCache(): void
    {
        Cache::forget('site_ayari:v1');
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::forgetCache());
        static::deleted(fn () => static::forgetCache());
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'meta_baslik',
        'meta_aciklama',
        'meta_anahtar_kelimeler',
        'meta_yazar',
        'gtm_container_id',
        'ga4_measurement_id',
        'meta_pixel_id',
        'google_ads_id',
        'recaptcha_site_key',
        'recaptcha_secret_key',
        'recaptcha_enabled',
        'iyzico_api_key',
        'iyzico_secret_key',
        'iyzico_base_url',
        'banka_adi',
        'banka_hesap_sahibi',
        'banka_iban',
        'banka_aciklama',
    ];

    protected function casts(): array
    {
        return [
            'recaptcha_enabled' => 'boolean',
            'recaptcha_secret_key' => 'encrypted',
            'iyzico_api_key' => 'encrypted',
            'iyzico_secret_key' => 'encrypted',
        ];
    }
}
