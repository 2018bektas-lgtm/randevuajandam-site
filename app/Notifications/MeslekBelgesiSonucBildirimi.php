<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MeslekBelgesiSonucBildirimi extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public bool $onaylandi,
        public ?string $not = null
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ad = (string) ($notifiable->ad_soyad ?? 'Hekim');

        if ($this->onaylandi) {
            $url = method_exists($notifiable, 'checkoutUrlAfterMeslek')
                ? $notifiable->checkoutUrlAfterMeslek()
                : route('frontend.hekim.paket_sec');
            $hasIntent = method_exists($notifiable, 'hasKayitPaketNiyeti') && $notifiable->hasKayitPaketNiyeti();

            return (new MailMessage)
                ->subject('Meslek belgeniz onaylandı — Randevu Ajandam')
                ->greeting('Sayın '.$ad.',')
                ->line('Diploma / hekimlik belgeniz yönetici ekibimiz tarafından incelendi ve **onaylandı**.')
                ->line($hasIntent
                    ? 'Kayıt sırasında seçtiğiniz paket için ödemeye geçebilirsiniz. Tüm paket fiyatlarına KDV dahildir.'
                    : 'Paket seçimi ve ödeme adımına geçebilirsiniz. Tüm paket fiyatlarına KDV dahildir.')
                ->action($hasIntent ? 'Ödemeye devam et' : 'Paket seçimine git', $url)
                ->line('Sorularınız için destek ekibimizle iletişime geçebilirsiniz.');
        }

        $mail = (new MailMessage)
            ->subject('Meslek belgeniz onaylanmadı — Randevu Ajandam')
            ->greeting('Sayın '.$ad.',')
            ->line('Diploma / hekimlik belgenizin incelemesi tamamlandı; kayıt şu an **onaylanmadı**.')
            ->line('Lütfen net okunan belge ve doğru kimlik bilgileriyle yeniden gönderin.');

        if (filled($this->not)) {
            $mail->line('**Yönetici notu:** '.$this->not);
        }

        return $mail
            ->action('Belgeyi yeniden yükle', route('frontend.hekim.meslek.bekleme'))
            ->line('Sorularınız için destek ekibimizle iletişime geçebilirsiniz.');
    }
}
