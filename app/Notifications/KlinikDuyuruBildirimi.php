<?php

namespace App\Notifications;

use App\Models\KlinikDuyuru;
use App\Notifications\Concerns\NotifiesDoktorApp;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class KlinikDuyuruBildirimi extends Notification
{
    use NotifiesDoktorApp;
    use Queueable;

    public function __construct(public KlinikDuyuru $duyuru) {}

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
        return [
            'type' => 'klinik_duyuru',
            'duyuru_id' => $this->duyuru->id,
            'title' => 'Klinik duyurusu',
            'body' => (string) $this->duyuru->baslik,
            'baslik' => $this->duyuru->baslik,
            'mesaj' => 'Yeni klinik duyurusu: '.$this->duyuru->baslik,
            'onem_derecesi' => $this->duyuru->onem_derecesi,
            'link' => route('hekim.klinik.uye.duyurular'),
            'deep_link' => 'randevuajandam-doktor://clinic',
        ];
    }
}
