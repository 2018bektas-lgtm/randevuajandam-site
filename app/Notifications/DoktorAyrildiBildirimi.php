<?php

namespace App\Notifications;

use App\Models\Doktor;
use App\Notifications\Concerns\NotifiesDoktorApp;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DoktorAyrildiBildirimi extends Notification
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
            'type' => 'doktor_ayrildi',
            'doktor_id' => $this->doktor->id,
            'doktor_ad_soyad' => $this->doktor->ad_soyad,
            'title' => 'Hekim ayrıldı',
            'body' => $ad.' kliniğinizden ayrıldı',
            'baslik' => 'Hekim ayrıldı',
            'mesaj' => $ad.' kliniğinizden ayrıldı.',
            'link' => route('hekim.klinik.doktorlar'),
            'deep_link' => 'randevuajandam-doktor://clinic',
        ];
    }
}
