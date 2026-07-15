<?php

namespace App\Notifications;

use App\Models\KlinikDuyuru;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class KlinikDuyuruBildirimi extends Notification
{
    use Queueable;

    protected $duyuru;

    /**
     * Create a new notification instance.
     */
    public function __construct(KlinikDuyuru $duyuru)
    {
        $this->duyuru = $duyuru;
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
            'duyuru_id' => $this->duyuru->id,
            'baslik' => $this->duyuru->baslik,
            'onem_derecesi' => $this->duyuru->onem_derecesi,
            'mesaj' => 'Yeni acil klinik duyurusu: ' . $this->duyuru->baslik,
            'link' => route('hekim.klinik.uye.duyurular'),
        ];
    }
}
