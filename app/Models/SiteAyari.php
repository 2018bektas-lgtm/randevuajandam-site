<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteAyari extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'site_ayarlari';

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
