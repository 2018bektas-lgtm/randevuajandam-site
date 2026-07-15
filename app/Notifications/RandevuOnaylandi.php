<?php

namespace App\Notifications;

use App\Models\Randevu;
use App\Notifications\Channels\SmsChannel;
use App\Support\BildirimSablonu;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RandevuOnaylandi extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Randevu $randevu
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['mail'];

        $ayarlar = $this->randevu->doktor->randevuAyari;
        if ($ayarlar && $ayarlar->sms_bildirimleri && ! empty($notifiable->telefon)) {
            $channels[] = SmsChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $s = BildirimSablonu::forRandevu('randevu_onaylandi', $this->randevu);
        $doktor = $this->randevu->doktor;
        $vars = BildirimSablonu::varsFromRandevu($this->randevu);
        $isOnline = ($this->randevu->gorusme_tipi ?? 'yuz_yuze') === 'online';

        $mail = (new MailMessage)
            ->subject($s['mail_subject'])
            ->greeting('Sayın '.$notifiable->ad_soyad.',')
            ->line($s['mail_intro'])
            ->line('**Randevu Detayları:**')
            ->line('📅 Tarih: '.$vars['tarih'])
            ->line('⏰ Saat: '.$vars['saat'])
            ->line('🏥 Hizmet: '.$vars['hizmet'])
            ->line('💻 Görüşme: '.($isOnline ? 'Online (platform)' : 'Yüz yüze'));

        if ($isOnline) {
            $mail->line('Görüntülü görüşme sitemiz üzerinden yapılır (Zoom linki gerekmez).');
            if (! empty($vars['gorusme_linki'])) {
                $mail->action('Görüşmeye Katıl', $vars['gorusme_linki']);
            } else {
                $mail->action('Randevularımı Görüntüle', route('frontend.hasta.randevular'));
            }
        } else {
            $mail->line('📍 Adres: '.($doktor->adres ?? 'Hekim Muayenehanesi'))
                ->action('Randevularımı Görüntüle', route('frontend.hasta.randevular'));
        }

        return $mail->line('Sağlıklı günler dileriz.');
    }

    public function toSms(object $notifiable): string
    {
        $s = BildirimSablonu::forRandevu('randevu_onaylandi', $this->randevu, [
            'hasta' => (string) ($notifiable->ad_soyad ?? ''),
        ]);

        return $s['sms'];
    }
}
