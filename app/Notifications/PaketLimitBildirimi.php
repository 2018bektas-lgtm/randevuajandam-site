<?php

namespace App\Notifications;

use App\Notifications\Concerns\NotifiesDoktorApp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PaketLimitBildirimi extends Notification implements ShouldQueue
{
    use NotifiesDoktorApp;
    use Queueable;

    public function __construct(
        public int $limit,
        public int $mevcut
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
        return [
            'type' => 'paket_limit',
            'title' => 'Randevu limiti doldu',
            'body' => "Paket limitiniz ({$this->limit}) dolu. Yeni randevu alınamıyor.",
            'baslik' => 'Randevu limiti doldu',
            'mesaj' => "Mevcut randevu sayınız {$this->mevcut}/{$this->limit}. Paket yükseltmeniz gerekebilir.",
            'deep_link' => 'randevuajandam-doktor://packages',
        ];
    }
}
