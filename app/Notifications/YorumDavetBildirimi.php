<?php

namespace App\Notifications;

use App\Models\Randevu;
use App\Support\PaketYetki;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Randevu tamamlandıktan sonra hasta yorum daveti (yorum_davet paketi).
 */
class YorumDavetBildirimi extends Notification implements ShouldQueue
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
        $doktor = $this->randevu->doktor;
        if (! $doktor || ! PaketYetki::has($doktor, 'yorum_davet')) {
            return [];
        }

        $channels = [];
        if (! empty($notifiable->e_posta) || method_exists($notifiable, 'routeNotificationForMail')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $doktor = $this->randevu->doktor;
        $ad = trim(($doktor?->unvan ? $doktor->unvan.' ' : '').($doktor?->ad_soyad ?? 'Hekiminiz'));
        $hastaAd = $notifiable->ad_soyad ?? trim(($notifiable->ad ?? '').' '.($notifiable->soyad ?? ''));

        $url = url('/');
        if ($doktor && $doktor->il && $doktor->ilce && $doktor->slug) {
            try {
                $brans = $doktor->branslar()->first();
                $url = route('frontend.hekim.detay', [
                    'il_slug' => $doktor->il->slug ?? 'il',
                    'ilce_slug' => $doktor->ilce->slug ?? 'ilce',
                    'brans_slug' => $brans?->slug ?? 'hekim',
                    'doctor_slug' => $doktor->slug,
                ]).'#yorumlar';
            } catch (\Throwable) {
                // fallback homepage
            }
        }

        return (new MailMessage)
            ->subject('Deneyiminizi paylaşır mısınız?')
            ->greeting('Sayın '.($hastaAd ?: 'Değerli danışan').',')
            ->line("{$ad} ile randevunuz tamamlandı. Kısa bir yorum bırakarak diğer danışanlara yardımcı olabilirsiniz.")
            ->action('Yorum Yaz', $url)
            ->line('Teşekkür ederiz.');
    }
}
