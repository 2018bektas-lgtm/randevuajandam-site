<?php

namespace App\Notifications;

use App\Models\KlinikDavetiye;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KlinikDavetBildirimi extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public KlinikDavetiye $davetiye
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $klinikAd = $this->davetiye->klinik->ad;
        $davetEdenAd = $this->davetiye->davetEden->ad_soyad;
        $davetEdenUnvan = $this->davetiye->davetEden->unvan ? $this->davetiye->davetEden->unvan.' ' : '';

        $url = route('frontend.hekim.klinik.davet.kabul', ['token' => $this->davetiye->token]);

        return (new MailMessage)
            ->subject($klinikAd.' Klinik Daveti - Randevu Ajandam')
            ->greeting('Sayın Meslektaşımız,')
            ->line($davetEdenUnvan.$davetEdenAd.', sizi **'.$klinikAd.'** bünyesinde hekim olarak çalışmaya davet ediyor.')
            ->line('Daveti kabul ederek klinik randevu planlama, hasta havuzu ve yönetim sistemine dahil olabilirsiniz.')
            ->action('Daveti Kabul Et', $url)
            ->line('Bu davet 7 gün boyunca geçerlidir.')
            ->line('Sağlıklı günler dileriz.');
    }
}
