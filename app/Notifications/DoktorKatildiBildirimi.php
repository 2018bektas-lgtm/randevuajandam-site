<?php

namespace App\Notifications;

use App\Models\Doktor;
use App\Notifications\Concerns\NotifiesDoktorApp;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DoktorKatildiBildirimi extends Notification
{
    use NotifiesDoktorApp;
    use Queueable;

    public function __construct(public Doktor $doktor) {}

    /**
     * @return array<int, string|class-string>
     */
    public function via(object $notifiable): array
    {
        return $this->doktorAppChannels();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $ad = ($this->doktor->unvan ? $this->doktor->unvan.' ' : '').$this->doktor->ad_soyad;

        return [
            'type' => 'doktor_katildi',
            'doktor_id' => $this->doktor->id,
            'doktor_ad_soyad' => $this->doktor->ad_soyad,
            'title' => 'Yeni hekim katıldı',
            'body' => $ad.' kliniğinize katıldı',
            'baslik' => 'Yeni hekim katıldı',
            'mesaj' => $ad.' kliniğinize katıldı!',
            'link' => route('hekim.klinik.doktorlar'),
            'deep_link' => 'randevuajandam-doktor://clinic',
        ];
    }
}
