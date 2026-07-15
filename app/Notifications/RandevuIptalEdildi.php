<?php

namespace App\Notifications;

use App\Models\Randevu;
use App\Notifications\Channels\SmsChannel;
use App\Support\BildirimSablonu;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RandevuIptalEdildi extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  string  $iptalEden  'doktor' or 'hasta'
     */
    public function __construct(
        public Randevu $randevu,
        public string $iptalEden
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];
        $ayarlar = $this->randevu->doktor->randevuAyari;

        if ($this->iptalEden === 'doktor') {
            $channels[] = 'mail';
            if ($ayarlar && $ayarlar->sms_bildirimleri && ! empty($notifiable->telefon)) {
                $channels[] = SmsChannel::class;
            }
        } else {
            if ($ayarlar && $ayarlar->email_bildirimleri) {
                $channels[] = 'mail';
            }
            if ($ayarlar && $ayarlar->sms_bildirimleri && ! empty($notifiable->telefon)) {
                $channels[] = SmsChannel::class;
            }
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $key = $this->iptalEden === 'doktor' ? 'randevu_iptal_hasta' : 'randevu_iptal_doktor';
        $s = BildirimSablonu::forRandevu($key, $this->randevu);
        $vars = BildirimSablonu::varsFromRandevu($this->randevu);
        $mail = new MailMessage;

        if ($this->iptalEden === 'doktor') {
            $mail->subject($s['mail_subject'])
                ->greeting('Sayın '.$notifiable->ad_soyad.',')
                ->line($s['mail_intro'])
                ->line('**İptal Edilen Randevu Detayları:**')
                ->line('📅 Tarih: '.$vars['tarih'])
                ->line('⏰ Saat: '.$vars['saat'])
                ->line('🏥 Hizmet: '.$vars['hizmet']);

            if (! empty($this->randevu->hekim_notu)) {
                $mail->line('💬 Hekim Notu: '.$this->randevu->hekim_notu);
            }
        } else {
            $mail->subject($s['mail_subject'])
                ->greeting('Sayın '.$vars['doktor'].',')
                ->line($s['mail_intro'])
                ->line('**İptal Edilen Randevu Detayları:**')
                ->line('📅 Tarih: '.$vars['tarih'])
                ->line('⏰ Saat: '.$vars['saat'])
                ->line('🏥 Hizmet: '.$vars['hizmet'])
                ->action('Paneli Görüntüle', route('hekim.panel'));
        }

        return $mail->line('Bilgilerinize sunar, sağlıklı günler dileriz.');
    }

    public function toSms(object $notifiable): string
    {
        $key = $this->iptalEden === 'doktor' ? 'randevu_iptal_hasta' : 'randevu_iptal_doktor';
        $s = BildirimSablonu::forRandevu($key, $this->randevu, [
            'hasta' => $this->iptalEden === 'doktor'
                ? (string) ($notifiable->ad_soyad ?? '')
                : null,
        ]);

        return $s['sms'];
    }
}
