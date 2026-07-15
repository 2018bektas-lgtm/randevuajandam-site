<?php

namespace App\Notifications;

use App\Models\EgitimBasvuru;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EgitimBasvuruAlindi extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public EgitimBasvuru $basvuru) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $b = $this->basvuru->loadMissing(['egitim', 'doktor']);
        $egitim = $b->egitim?->baslik ?? 'Eğitim';
        $doktor = trim(($b->doktor?->unvan ? $b->doktor->unvan.' ' : '').($b->doktor?->ad_soyad ?? ''));

        $mail = (new MailMessage)
            ->subject('Başvurunuz alındı: '.$egitim)
            ->greeting('Sayın '.$b->ad_soyad.',')
            ->line($egitim.' başvurusu alındı ve hekim onayını beklemektedir.')
            ->line('Hekim: '.$doktor);

        if ($b->egitim?->odeme_notu) {
            $mail->line('Ödeme notu: '.$b->egitim->odeme_notu);
        } elseif ($b->ucret_durumu !== 'yok' && $b->ucret_tutari) {
            $mail->line('Ücret bilgisi: '.number_format((float) $b->ucret_tutari, 2, ',', '.').' TL (ödeme hekim üzerinden).');
        }

        return $mail->line('Sizinle iletişime geçilecektir.');
    }
}
