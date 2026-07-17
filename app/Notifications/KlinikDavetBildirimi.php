<?php

namespace App\Notifications;

use App\Models\Doktor;
use App\Models\KlinikDavetiye;
use App\Notifications\Concerns\NotifiesDoktorApp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KlinikDavetBildirimi extends Notification implements ShouldQueue
{
    use NotifiesDoktorApp;
    use Queueable;

    public function __construct(
        public KlinikDavetiye $davetiye
    ) {}

    /**
     * @return array<int, string|class-string>
     */
    public function via(object $notifiable): array
    {
        // Anonymous mail route: mail only. Registered doctor: app + mail.
        if ($notifiable instanceof Doktor) {
            return $this->doktorAppChannels(['mail']);
        }

        return ['mail'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->davetiye->loadMissing(['klinik', 'davetEden']);
        $klinikAd = $this->davetiye->klinik->ad ?? 'Klinik';

        return [
            'type' => 'klinik_davet',
            'davetiye_id' => $this->davetiye->id,
            'title' => 'Klinik daveti',
            'body' => $klinikAd.' sizi ekibe davet etti',
            'baslik' => 'Klinik daveti',
            'mesaj' => $klinikAd.' kliniğinden davetiniz var.',
            'deep_link' => 'randevuajandam-doktor://overview',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->davetiye->loadMissing(['klinik', 'davetEden']);
        $klinikAd = $this->davetiye->klinik->ad ?? 'Klinik';
        $davetEdenAd = $this->davetiye->davetEden->ad_soyad ?? 'Hekim';
        $davetEdenUnvan = $this->davetiye->davetEden?->unvan ? $this->davetiye->davetEden->unvan.' ' : '';

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
