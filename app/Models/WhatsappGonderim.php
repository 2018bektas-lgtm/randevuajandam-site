<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappGonderim extends Model
{
    protected $table = 'whatsapp_gonderimler';

    protected $fillable = [
        'klinik_id',
        'doktor_id',
        'hasta_id',
        'telefon',
        'sablon',
        'kategori',
        'wamid',
        'durum',
        'hata',
    ];

    public const DURUM_KUYRUKTA = 'kuyrukta';
    public const DURUM_GONDERILDI = 'gonderildi';
    public const DURUM_ILETILDI = 'iletildi';
    public const DURUM_OKUNDU = 'okundu';
    public const DURUM_HATA = 'hata';
    public const DURUM_SMS_FALLBACK = 'sms_fallback';

    public function klinik(): BelongsTo
    {
        return $this->belongsTo(Klinik::class, 'klinik_id');
    }

    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }

    public function hasta(): BelongsTo
    {
        return $this->belongsTo(Hasta::class, 'hasta_id');
    }
}
