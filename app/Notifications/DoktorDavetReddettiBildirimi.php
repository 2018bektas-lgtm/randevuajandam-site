<?php

namespace App\Notifications;

use App\Models\Doktor;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DoktorDavetReddettiBildirimi extends Notification
{
    use Queueable;

    protected $doktor;
    protected $eposta;

    /**
     * Create a new notification instance.
     */
    public function __construct($doktor, $eposta)
    {
        $this->doktor = $doktor;
        $this->eposta = $eposta;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $name = $this->doktor ? (($this->doktor->unvan ? $this->doktor->unvan . ' ' : '') . $this->doktor->ad_soyad) : $this->eposta;
        return [
            'doktor_id' => $this->doktor ? $this->doktor->id : null,
            'mesaj' => $name . ' gönderdiğiniz klinik davetini reddetti.',
            'link' => route('hekim.klinik.doktorlar'),
        ];
    }
}
