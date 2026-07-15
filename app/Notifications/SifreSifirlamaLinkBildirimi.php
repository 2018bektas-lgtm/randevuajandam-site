<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SifreSifirlamaLinkBildirimi extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $token,
        public string $type
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
        $url = route('password.reset', ['token' => $this->token, 'type' => $this->type]);
        $isim = property_exists($notifiable, 'ad_soyad') ? $notifiable->ad_soyad : ($notifiable->ad ?? 'Kullanıcı');

        return (new MailMessage)
            ->subject('Şifre Sıfırlama Talebi - Randevu Ajandam')
            ->greeting('Merhaba ' . $isim . ',')
            ->line('Hesabınız için bir şifre sıfırlama talebi aldık.')
            ->line('Şifrenizi sıfırlamak için aşağıdaki butona tıklayabilirsiniz:')
            ->action('Şifremi Sıfırla', $url)
            ->line('Bu şifre sıfırlama linki 60 dakika boyunca geçerlidir.')
            ->line('Eğer bu talebi siz yapmadıysanız, bu e-postayı dikkate almayabilirsiniz.')
            ->line('Sağlıklı günler dileriz.');
    }
}
