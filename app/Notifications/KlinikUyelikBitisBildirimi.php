<?php

namespace App\Notifications;

use App\Models\Klinik;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KlinikUyelikBitisBildirimi extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Klinik $klinik,
        public int $kalanGun
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = 'Klinik Üyeliğiniz Hakkında - Randevu Ajandam';
        if ($this->kalanGun === 0) {
            $subject = 'Klinik Üyeliğiniz Bugün Sona Erdi! - Randevu Ajandam';
        }

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Sayın ' . ($this->klinik->sahipDoktor?->ad_soyad ?? 'Klinik Yetkilisi') . ',');

        if ($this->kalanGun === 0) {
            $mail->line('**' . $this->klinik->ad . '** isimli kliniğinizin üyelik süresi bugün itibarıyla sona ermiştir.')
                ->line('Klinik yönetim panelinize erişimin kesilmemesi için lütfen üyeliğinizi yenileyiniz.');
        } else {
            $mail->line('**' . $this->klinik->ad . '** isimli kliniğinizin üyelik süresi **' . $this->kalanGun . ' gün sonra** sona erecektir.')
                ->line('Hizmet kesintisi yaşamamak için lütfen en kısa sürede aboneliğinizi yenileyiniz veya paketinizi güncelleyiniz.');
        }

        $url = route('hekim.klinik.ayarlar');

        return $mail->action('Abonelik Ayarlarına Git', $url)
            ->line('Sağlıklı günler dileriz.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        if ($this->kalanGun === 0) {
            return [
                'baslik' => 'Klinik Üyeliğiniz Sona Erdi!',
                'mesaj' => $this->klinik->ad . ' isimli kliniğinizin üyelik süresi bugün sona erdi. Panel erişimi için lütfen yenileyin.',
                'link' => route('hekim.klinik.ayarlar'),
            ];
        }

        return [
            'baslik' => 'Klinik Üyeliği Bitiş Hatırlatması',
            'mesaj' => $this->klinik->ad . ' isimli kliniğinizin üyelik süresinin bitmesine ' . $this->kalanGun . ' gün kaldı.',
            'link' => route('hekim.klinik.ayarlar'),
        ];
    }
}
