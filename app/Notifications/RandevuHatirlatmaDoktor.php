<?php

namespace App\Notifications;

use App\Models\Randevu;
use App\Notifications\Concerns\NotifiesDoktorApp;
use App\Support\BildirimSablonu;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RandevuHatirlatmaDoktor extends Notification implements ShouldQueue
{
    use NotifiesDoktorApp;
    use Queueable;

    /**
     * @param  string  $sure  örn. "2 saat"
     */
    public function __construct(
        public Randevu $randevu,
        public string $sure = '2 saat'
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
        $vars = BildirimSablonu::varsFromRandevu($this->randevu);

        return [
            'type' => 'randevu_hatirlatma',
            'randevu_id' => $this->randevu->id,
            'title' => 'Yaklaşan randevu',
            'body' => ($vars['hasta'] ?? 'Hasta').' · '.($vars['tarih'] ?? '').' '.($vars['saat'] ?? '').' ('.$this->sure.' kaldı)',
            'baslik' => 'Yaklaşan randevu',
            'mesaj' => $this->sure.' içinde randevunuz var.',
            'deep_link' => 'randevuajandam-doktor://appointment/'.$this->randevu->id,
        ];
    }
}
