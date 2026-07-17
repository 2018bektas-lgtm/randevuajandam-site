<?php

namespace App\Notifications;

use App\Models\EgitimBasvuru;
use App\Notifications\Concerns\NotifiesDoktorApp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EgitimYeniBasvuru extends Notification implements ShouldQueue
{
    use NotifiesDoktorApp;
    use Queueable;

    public function __construct(public EgitimBasvuru $basvuru) {}

    /**
     * @return array<int, string|class-string>
     */
    public function via(object $notifiable): array
    {
        return $this->doktorAppChannels(['mail']);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->basvuru->loadMissing('egitim');
        $egitim = $this->basvuru->egitim?->baslik ?? 'Eğitim';

        return [
            'type' => 'egitim_basvuru',
            'basvuru_id' => $this->basvuru->id,
            'egitim_id' => $this->basvuru->egitim_id,
            'title' => 'Yeni eğitim başvurusu',
            'body' => $egitim.' · '.$this->basvuru->ad_soyad,
            'baslik' => 'Yeni eğitim başvurusu',
            'mesaj' => $this->basvuru->ad_soyad.' başvurdu: '.$egitim,
            'deep_link' => 'randevuajandam-doktor://education-apps',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $b = $this->basvuru->loadMissing('egitim');
        $egitim = $b->egitim?->baslik ?? 'Eğitim';

        return (new MailMessage)
            ->subject('Yeni eğitim başvurusu: '.$egitim)
            ->greeting('Merhaba,')
            ->line($egitim.' için yeni bir başvuru var.')
            ->line('Katılımcı: '.$b->ad_soyad)
            ->line('Telefon: '.$b->telefon)
            ->line('Durum: beklemede (ödeme platform üzerinden alınmaz).')
            ->action('Başvuruları Gör', route('hekim.egitimler.basvurular', $b->egitim_id))
            ->line('Ücreti kendi kanalınızdan tahsil edip panelden “Ödeme alındı” işaretleyebilirsiniz.');
    }
}
