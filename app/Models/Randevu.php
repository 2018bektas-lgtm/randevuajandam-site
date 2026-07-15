<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Randevu extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'randevular';

    protected $fillable = [
        'doktor_id',
        'hizmet_id',
        'hasta_id',
        'ad',
        'soyad',
        'telefon',
        'e_posta',
        'tarih',
        'saat',
        'slot_token',
        'not',
        'durum',
        'gorusme_tipi',
        'meeting_provider',
        'meeting_room_id',
        'meeting_url',
        'meeting_join_token',
        'meeting_baslangic_at',
        'meeting_bitis_at',
        'hekim_notu',
        'yonetim_token',
        'hatirlatma_1gun_gonderildi',
        'hatirlatma_2saat_gonderildi',
    ];

    protected $hidden = [
        'yonetim_token',
        'meeting_join_token',
        'meeting_url',
        'meeting_room_id',
    ];

    protected static function booted(): void
    {
        static::saving(function (Randevu $randevu) {
            $aktif = in_array($randevu->durum, ['beklemede', 'onaylandi', 'tamamlandi'], true);
            if ($aktif && $randevu->doktor_id && $randevu->tarih && $randevu->saat) {
                $tarih = $randevu->tarih instanceof \DateTimeInterface
                    ? $randevu->tarih->format('Y-m-d')
                    : substr((string) $randevu->tarih, 0, 10);
                $saat = substr((string) $randevu->saat, 0, 5);
                $randevu->slot_token = $randevu->doktor_id.'|'.$tarih.'|'.$saat;
            } else {
                // Cancelled / soft states release the unique slot
                $randevu->slot_token = null;
            }
        });

        static::created(function (Randevu $randevu) {
            $doktor = $randevu->doktor;
            if ($doktor && $doktor->klinik_id && $randevu->hasta_id) {
                $doktor->klinik->hastalar()->syncWithoutDetaching([
                    $randevu->hasta_id => ['kayit_tarihi' => now()],
                ]);
            }

            // Eğer ilk oluşturulduğunda onaylı ise webhook gönder
            if ($randevu->durum === 'onaylandi') {
                if ($randevu->isOnline()) {
                    try {
                        app(\App\Services\MeetingRoomService::class)->ensureRoom($randevu);
                    } catch (\Throwable) {
                        // oda hatası randevuyu bozmasın
                    }
                }
                \App\Jobs\SendWebhookJob::dispatch(
                    'appointment.approved',
                    $randevu->toArray(),
                    $randevu->doktor_id,
                    $doktor ? $doktor->klinik_id : null
                );
            }
        });

        static::updated(function (Randevu $randevu) {
            $doktor = $randevu->doktor;
            if ($randevu->isDirty('durum')) {
                $event = null;
                if ($randevu->durum === 'onaylandi') {
                    $event = 'appointment.approved';
                    if ($randevu->isOnline()) {
                        try {
                            app(\App\Services\MeetingRoomService::class)->ensureRoom($randevu);
                        } catch (\Throwable) {
                            //
                        }
                    }
                } elseif ($randevu->durum === 'iptal') {
                    $event = 'appointment.cancelled';
                }

                if ($event) {
                    \App\Jobs\SendWebhookJob::dispatch(
                        $event,
                        $randevu->toArray(),
                        $randevu->doktor_id,
                        $doktor ? $doktor->klinik_id : null
                    );
                }
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'tarih' => 'date',
            'hatirlatma_1gun_gonderildi' => 'boolean',
            'hatirlatma_2saat_gonderildi' => 'boolean',
            'meeting_baslangic_at' => 'datetime',
            'meeting_bitis_at' => 'datetime',
        ];
    }

    public function isOnline(): bool
    {
        return ($this->gorusme_tipi ?? 'yuz_yuze') === 'online';
    }

    public function hasMeetingRoom(): bool
    {
        return filled($this->meeting_room_id) && filled($this->meeting_join_token);
    }

    public function canJoinMeeting(?\Carbon\Carbon $now = null): bool
    {
        return app(\App\Services\MeetingRoomService::class)->canJoin($this, $now);
    }

    public function platformJoinUrl(): ?string
    {
        return app(\App\Services\MeetingRoomService::class)->platformJoinUrl($this);
    }

    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }

    public function hizmet(): BelongsTo
    {
        return $this->belongsTo(Hizmet::class, 'hizmet_id');
    }

    public function hasta(): BelongsTo
    {
        return $this->belongsTo(Hasta::class, 'hasta_id');
    }

    /**
     * Get the review for this appointment.
     */
    public function yorum(): HasOne
    {
        return $this->hasOne(Yorum::class, 'randevu_id');
    }
}
