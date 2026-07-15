<?php

namespace App\Notifications;

use App\Models\EgitimBasvuru;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EgitimYeniBasvuru extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public EgitimBasvuru $basvuru) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
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
