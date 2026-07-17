<?php

namespace App\Notifications;

use App\Models\Klinik;
use App\Notifications\Concerns\NotifiesDoktorApp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KlinikUyelikBitisBildirimi extends Notification implements ShouldQueue
{
    use NotifiesDoktorApp;
    use Queueable;

    public function __construct(
        public Klinik $klinik,
        public int $kalanGun
    ) {}

    /**
     * @return array<int, string|class-string>
     */
    public function via(object $notifiable): array
    {
        return $this->doktorAppChannels(['mail']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = 'Klinik Üyeliğiniz Hakkında - Randevu Ajandam';
        if ($this->kalanGun === 0) {
            $subject = 'Klinik Üyeliğiniz Bugün Sona Erdi! - Randevu Ajandam';
        }

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Sayın '.($this->klinik->sahipDoktor?->ad_soyad ?? 'Klinik Yetkilisi').',');

        if ($this->kalanGun === 0) {
            $mail->line('**'.$this->klinik->ad.'** isimli kliniğinizin üyelik süresi bugün itibarıyla sona ermiştir.')
                ->line('Klinik yönetim panelinize erişimin kesilmemesi için lütfen üyeliğinizi yenileyiniz.');
        } else {
            $mail->line('**'.$this->klinik->ad.'** isimli kliniğinizin üyelik süresi **'.$this->kalanGun.' gün sonra** sona erecektir.')
                ->line('Hizmet kesintisi yaşamamak için lütfen en kısa sürede aboneliğinizi yenileyiniz veya paketinizi güncelleyiniz.');
        }

        $url = route('hekim.klinik.ayarlar');

        return $mail->action('Abonelik Ayarlarına Git', $url)
            ->line('Sağlıklı günler dileriz.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        if ($this->kalanGun === 0) {
            return [
                'type' => 'klinik_uyelik',
                'title' => 'Klinik üyeliği sona erdi',
                'body' => $this->klinik->ad.' üyeliği bugün sona erdi. Lütfen yenileyin.',
                'baslik' => 'Klinik Üyeliğiniz Sona Erdi!',
                'mesaj' => $this->klinik->ad.' isimli kliniğinizin üyelik süresi bugün sona erdi. Panel erişimi için lütfen yenileyin.',
                'link' => route('hekim.klinik.ayarlar'),
                'deep_link' => 'randevuajandam-doktor://packages',
            ];
        }

        return [
            'type' => 'klinik_uyelik',
            'title' => 'Üyelik bitiş hatırlatması',
            'body' => $this->klinik->ad.' · '.$this->kalanGun.' gün kaldı',
            'baslik' => 'Klinik Üyeliği Bitiş Hatırlatması',
            'mesaj' => $this->klinik->ad.' isimli kliniğinizin üyelik süresinin bitmesine '.$this->kalanGun.' gün kaldı.',
            'link' => route('hekim.klinik.ayarlar'),
            'deep_link' => 'randevuajandam-doktor://packages',
        ];
    }
}
