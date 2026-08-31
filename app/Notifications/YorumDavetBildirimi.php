<?php

namespace App\Notifications;

use App\Models\Randevu;
use App\Support\PaketYetki;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Randevu sonrası hasta yorum daveti (yorum_davet paketi).
 *
 * Bağlantı, giriş gerektirmeyen tek kullanımlık kısa adrestir:
 *   {site}/y/{token}
 *
 * Önceden buton hekimin herkese açık profiline (#yorumlar) gidiyordu;
 * oradan yorum bırakmanın yolu yoktu (form auth:hasta ile korumalıydı ve
 * misafir randevu ile açılan hesabın şifresini hasta bilmiyordu). Sonuç:
 * davet gidiyor ama kimse yorum yapamıyordu.
 */
class YorumDavetBildirimi extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Randevu $randevu,
        public string $token,
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

        $url = route('yorum.davet', ['token' => $this->token]);

        return (new MailMessage)
            ->subject('Deneyiminizi paylaşır mısınız?')
            ->greeting('Sayın '.($hastaAd ?: 'Değerli danışan').',')
            ->line("{$ad} ile randevunuz tamamlandı. Kısa bir değerlendirme bırakarak diğer danışanlara yardımcı olabilirsiniz.")
            ->line('Üyelik veya giriş gerekmiyor — aşağıdaki bağlantı doğrudan değerlendirme formunu açar.')
            ->action('Değerlendirme Yap', $url)
            ->line('Bağlantı size özeldir, bir kez kullanılabilir ve 30 gün geçerlidir.')
            ->line('Teşekkür ederiz.');
    }
}
