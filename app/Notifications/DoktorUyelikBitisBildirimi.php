<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DoktorUyelikBitisBildirimi extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $kalanGun
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ad = (string) ($notifiable->ad_soyad ?? 'Hekim');
        $bitis = $notifiable->uyelik_bitis?->format('d.m.Y') ?? '—';

        if ($this->kalanGun <= 0) {
            return (new MailMessage)
                ->subject('Üyeliğiniz sona erdi — Randevu Ajandam')
                ->greeting('Sayın '.$ad.',')
                ->line('Paket üyeliğinizin süresi **'.$bitis.'** itibarıyla dolmuştur.')
                ->line('Kesintisiz kullanım için lütfen paket seçip ödemeyi tamamlayın. Fiyatlara KDV dahildir.')
                ->action('Paket seç / öde', route('frontend.hekim.paket_sec'));
        }

        return (new MailMessage)
            ->subject('Üyeliğiniz '.$this->kalanGun.' gün sonra bitiyor — Randevu Ajandam')
            ->greeting('Sayın '.$ad.',')
            ->line('Paket üyeliğiniz **'.$bitis.'** tarihinde sona erecek (yaklaşık **'.$this->kalanGun.' gün**).')
            ->line('Hizmet kesintisi yaşamamak için paketinizi yenileyebilirsiniz. Fiyatlara KDV dahildir.')
            ->action('Paket / üyelik', route('hekim.uyelik'));
    }
}
