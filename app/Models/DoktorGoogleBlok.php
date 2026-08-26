<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Google Takvim'den cekilen "mesgul" araligi. Randevu degildir; slot
 * cakismasi kontrolunde RandevuDogrulamaService bu tabloyu okur. Google'daki
 * event silininceye kadar burada durur ve bir hastanin o slotu secmesini engeller.
 */
class DoktorGoogleBlok extends Model
{
    protected $table = 'doktor_google_bloklari';

    protected $fillable = [
        'doktor_id',
        'google_event_id',
        'baslangic_at',
        'bitis_at',
        'baslik',
        'hepsi_gun_mu',
    ];

    protected function casts(): array
    {
        return [
            'baslangic_at' => 'datetime',
            'bitis_at' => 'datetime',
            'hepsi_gun_mu' => 'boolean',
        ];
    }

    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }
}
