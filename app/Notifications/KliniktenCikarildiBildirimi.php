<?php

namespace App\Notifications;

use App\Models\Klinik;
use App\Notifications\Concerns\NotifiesDoktorApp;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class KliniktenCikarildiBildirimi extends Notification
{
    use NotifiesDoktorApp;
    use Queueable;

    public function __construct(public Klinik $klinik) {}

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
            'type' => 'klinikten_cikarildi',
            'klinik_id' => $this->klinik->id,
            'klinik_ad' => $this->klinik->ad,
            'title' => 'Klinikten çıkarıldınız',
            'body' => $this->klinik->ad.' kliniğinden çıkarıldınız',
            'baslik' => 'Klinikten çıkarıldınız',
            'mesaj' => $this->klinik->ad.' kliniğinden çıkarıldınız. Bireysel paket satın alarak devam edebilirsiniz.',
            'link' => route('frontend.paketler'),
            'deep_link' => 'randevuajandam-doktor://packages',
        ];
    }
}
