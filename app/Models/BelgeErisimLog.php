<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BelgeErisimLog extends Model
{
    protected $table = 'belge_erisim_loglari';

    protected $fillable = [
        'doktor_id',
        'yonetici_id',
        'aktor',
        'belge_tipi',
        'ip',
        'user_agent',
    ];

    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class);
    }

    public static function kaydet(
        ?int $doktorId,
        string $aktor,
        string $belgeTipi = 'meslek_belgesi',
        ?int $yoneticiId = null
    ): void {
        try {
            static::create([
                'doktor_id' => $doktorId,
                'yonetici_id' => $yoneticiId,
                'aktor' => $aktor,
                'belge_tipi' => $belgeTipi,
                'ip' => request()?->ip(),
                'user_agent' => Str::limit((string) request()?->userAgent(), 255),
            ]);
        } catch (\Throwable) {
            // ignore
        }
    }
}
