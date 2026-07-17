<?php

namespace App\Notifications;

use App\Models\Randevu;
use App\Notifications\Channels\ExpoPushChannel;
use App\Notifications\Channels\SmsChannel;
use App\Support\BildirimSablonu;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class YeniRandevuTalebi extends Notification implements ShouldQueue
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
        $channels = ['database', ExpoPushChannel::class];
        $ayarlar = $this->randevu->doktor->randevuAyari;

        if ($ayarlar) {
            if ($ayarlar->email_bildirimleri) {
                $channels[] = 'mail';
            }
            if ($ayarlar->sms_bildirimleri && ! empty($notifiable->telefon)) {
                $channels[] = SmsChannel::class;
            }
        } else {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $vars = BildirimSablonu::varsFromRandevu($this->randevu);

        return [
            'type' => 'yeni_randevu',
            'randevu_id' => $this->randevu->id,
            'title' => 'Yeni randevu talebi',
            'body' => ($vars['hasta'] ?? 'Hasta').' · '.($vars['tarih'] ?? '').' '.($vars['saat'] ?? ''),
            'hasta' => $vars['hasta'] ?? null,
            'tarih' => $vars['tarih'] ?? null,
            'saat' => $vars['saat'] ?? null,
        ];
    }

    /**
     * @return array{title: string, body: string, data: array<string, mixed>}
     */
    public function toExpoPush(object $notifiable): array
    {
        $arr = $this->toArray($notifiable);

        return [
            'title' => (string) $arr['title'],
            'body' => (string) $arr['body'],
            'data' => [
                'type' => 'yeni_randevu',
                'screen' => 'calendar',
                'randevu_id' => (string) $this->randevu->id,
                'appointment_id' => (string) $this->randevu->id,
                'channelId' => 'randevu',
                'deep_link' => 'randevuajandam-doktor://appointment/'.$this->randevu->id,
            ],
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ayarlar = $this->randevu->doktor->randevuAyari;
        $onayTipi = $ayarlar?->randevu_onay_tipi ?? 'manuel';
        $key = $onayTipi === 'otomatik' ? 'yeni_randevu_otomatik' : 'yeni_randevu_manuel';
        $s = BildirimSablonu::forRandevu($key, $this->randevu);
        $vars = BildirimSablonu::varsFromRandevu($this->randevu);

        $isOnline = ($this->randevu->gorusme_tipi ?? 'yuz_yuze') === 'online';

        $mail = (new MailMessage)
            ->subject($s['mail_subject'])
            ->greeting('Sayın '.$notifiable->unvan.' '.$notifiable->ad_soyad.',')
            ->line('Sisteminizde yeni bir randevu kaydı oluşturuldu.')
            ->line($s['mail_intro'])
            ->line('**Randevu Detayları:**')
            ->line('👤 Hasta: '.$vars['hasta'])
            ->line('📅 Tarih: '.$vars['tarih'])
            ->line('⏰ Saat: '.$vars['saat'])
            ->line('🏥 Hizmet: '.$vars['hizmet'])
            ->line('💻 Görüşme: '.($isOnline ? 'Online (platform)' : 'Yüz yüze'));

        if (! empty($this->randevu->not)) {
            $mail->line('💬 Hasta Notu: '.$this->randevu->not);
        }

        return $mail->action('Talepleri Görüntüle', route('hekim.randevu.talepler'))
            ->line('İyi çalışmalar dileriz.');
    }

    public function toSms(object $notifiable): string
    {
        $ayarlar = $this->randevu->doktor->randevuAyari;
        $onayTipi = $ayarlar?->randevu_onay_tipi ?? 'manuel';
        $key = $onayTipi === 'otomatik' ? 'yeni_randevu_otomatik' : 'yeni_randevu_manuel';
        $s = BildirimSablonu::forRandevu($key, $this->randevu, [
            'doktor' => (string) ($notifiable->ad_soyad ?? ''),
        ]);

        return $s['sms'];
    }
}
