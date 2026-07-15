<?php

namespace App\Notifications;

use App\Models\Doktor;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DoktorAyrildiBildirimi extends Notification
{
    use Queueable;

    protected $doktor;

    /**
     * Create a new notification instance.
     */
    public function __construct(Doktor $doktor)
    {
        $this->doktor = $doktor;
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
        return [
            'doktor_id' => $this->doktor->id,
            'doktor_ad_soyad' => $this->doktor->ad_soyad,
            'mesaj' => ($this->doktor->unvan ? $this->doktor->unvan . ' ' : '') . $this->doktor->ad_soyad . ' kliniğinizden ayrıldı.',
            'link' => route('hekim.klinik.doktorlar'),
        ];
    }
}
