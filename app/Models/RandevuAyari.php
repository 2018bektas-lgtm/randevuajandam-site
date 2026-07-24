<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RandevuAyari extends Model
{
    protected $table = 'randevu_ayarlari';

    protected $fillable = [
        'doktor_id',
        'randevu_onay_tipi',
        'en_erken_randevu_saati',
        'en_gec_randevu_gunu',
        'randevu_periyodu',
        'randevu_iptal_aktif_mi',
        'iptal_saat_limiti',
        'gunluk_maksimum_randevu',
        'email_bildirimleri',
        'sms_bildirimleri',
        'aktif_mi',
        'online_randevu_aktif',
        'yuzyuze_randevu_aktif',
    ];

    protected $casts = [
        'aktif_mi' => 'boolean',
        'online_randevu_aktif' => 'boolean',
        'yuzyuze_randevu_aktif' => 'boolean',
        'randevu_iptal_aktif_mi' => 'boolean',
        'email_bildirimleri' => 'boolean',
        'sms_bildirimleri' => 'boolean',
        'en_erken_randevu_saati' => 'integer',
        'en_gec_randevu_gunu' => 'integer',
        'randevu_periyodu' => 'integer',
        'iptal_saat_limiti' => 'integer',
        'gunluk_maksimum_randevu' => 'integer',
    ];

    public function doktor()
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }
}
