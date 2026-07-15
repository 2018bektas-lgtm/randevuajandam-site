<?php

namespace App\Notifications;

use App\Models\Klinik;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class KliniktenCikarildiBildirimi extends Notification
{
    use Queueable;

    protected $klinik;

    /**
     * Create a new notification instance.
     */
    public function __construct(Klinik $klinik)
    {
        $this->klinik = $klinik;
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
            'klinik_id' => $this->klinik->id,
            'klinik_ad' => $this->klinik->ad,
            'mesaj' => $this->klinik->ad . ' kliniğinden çıkarıldınız. Bireysel paket satın alarak devam edebilirsiniz.',
            'link' => route('frontend.paketler'),
        ];
    }
}
