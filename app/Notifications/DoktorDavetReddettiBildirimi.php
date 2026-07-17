<?php

namespace App\Notifications;

use App\Notifications\Concerns\NotifiesDoktorApp;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DoktorDavetReddettiBildirimi extends Notification
{
    use NotifiesDoktorApp;
    use Queueable;

    public function __construct(
        protected mixed $doktor,
        protected mixed $eposta
    ) {}

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
        $name = $this->doktor
            ? (($this->doktor->unvan ? $this->doktor->unvan.' ' : '').$this->doktor->ad_soyad)
            : (string) $this->eposta;

        return [
            'type' => 'davet_red',
            'doktor_id' => $this->doktor->id ?? null,
            'title' => 'Davet reddedildi',
            'body' => $name.' klinik davetini reddetti',
            'baslik' => 'Davet reddedildi',
            'mesaj' => $name.' gönderdiğiniz klinik davetini reddetti.',
            'link' => route('hekim.klinik.doktorlar'),
            'deep_link' => 'randevuajandam-doktor://clinic',
        ];
    }
}
