<?php

namespace App\Notifications;

use App\Models\BeklemeListesi;
use App\Models\Randevu;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BeklemeListesiSlotAcildi extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public BeklemeListesi $kayit,
        public ?Randevu $slotRandevu = null
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
        $doktor = $this->kayit->doktor;
        $doktorIsim = trim(($doktor?->unvan ? $doktor->unvan.' ' : '').($doktor?->ad_soyad ?? 'Hekim'));
        $profil = $doktor?->profil_url ?? url('/');

        $tarihBilgi = '';
        if ($this->slotRandevu) {
            $t = $this->slotRandevu->tarih instanceof \DateTimeInterface
                ? $this->slotRandevu->tarih->format('d.m.Y')
                : (string) $this->slotRandevu->tarih;
            $tarihBilgi = $t.' · '.substr((string) $this->slotRandevu->saat, 0, 5);
        } elseif ($this->kayit->tercih_tarih) {
            $tarihBilgi = $this->kayit->tercih_tarih->format('d.m.Y');
            if ($this->kayit->tercih_saat) {
                $tarihBilgi .= ' · '.$this->kayit->tercih_saat;
            }
        }

        $ad = method_exists($notifiable, 'getAttribute')
            ? ($notifiable->ad_soyad ?? $this->kayit->ad_soyad)
            : $this->kayit->ad_soyad;

        $mail = (new MailMessage)
            ->subject('Boş randevu slotu — '.$doktorIsim)
            ->greeting('Sayın '.$ad.',')
            ->line($doktorIsim.' için bekleme listenizdeki bir randevu saati açılmış olabilir.')
            ->line('Erken davranırsanız randevu alabilirsiniz.');

        if ($tarihBilgi !== '') {
            $mail->line('İlgili tarih/saat: **'.$tarihBilgi.'**');
        }

        return $mail
            ->action('Hekim Profiline Git', $profil)
            ->line('Bu bilgilendirme bekleme listesi kaydınıza istinaden gönderilmiştir.');
    }
}
