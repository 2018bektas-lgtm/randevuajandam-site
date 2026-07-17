<?php

namespace App\Notifications;

use App\Models\Yorum;
use App\Notifications\Concerns\NotifiesDoktorApp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class YeniYorumBildirimi extends Notification implements ShouldQueue
{
    use NotifiesDoktorApp;
    use Queueable;

    public function __construct(public Yorum $yorum) {}

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
        $this->yorum->loadMissing(['hasta:id,ad,soyad']);
        $hastaAd = trim(($this->yorum->hasta->ad ?? '').' '.($this->yorum->hasta->soyad ?? ''));
        if ($hastaAd === '') {
            $hastaAd = 'Hasta';
        }
        $puan = (int) ($this->yorum->puan ?? 0);
        $ozet = mb_substr((string) ($this->yorum->yorum ?? ''), 0, 80);

        return [
            'type' => 'yeni_yorum',
            'yorum_id' => $this->yorum->id,
            'randevu_id' => $this->yorum->randevu_id,
            'title' => 'Yeni hasta yorumu',
            'body' => $hastaAd.($puan > 0 ? " · {$puan}/5" : '').($ozet !== '' ? ' · '.$ozet : ''),
            'baslik' => 'Yeni hasta yorumu',
            'mesaj' => $hastaAd.' yorum bıraktı. Onay bekliyor.',
            'deep_link' => 'randevuajandam-doktor://reviews',
        ];
    }
}
