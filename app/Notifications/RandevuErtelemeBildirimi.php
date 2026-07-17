<?php

namespace App\Notifications;

use App\Models\Randevu;
use App\Notifications\Concerns\NotifiesDoktorApp;
use App\Support\BildirimSablonu;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notify doctor when appointment time is changed by clinic staff (not the doctor themselves)
 * or when a patient-initiated reschedule exists in future flows.
 * Also used to notify patient when doctor reschedules (mail only via via()).
 */
class RandevuErtelemeBildirimi extends Notification implements ShouldQueue
{
    use NotifiesDoktorApp;
    use Queueable;

    /**
     * @param  string  $hedef  'doktor' | 'hasta'
     */
    public function __construct(
        public Randevu $randevu,
        public string $eskiTarih,
        public string $eskiSaat,
        public string $hedef = 'hasta'
    ) {}

    /**
     * @return array<int, string|class-string>
     */
    public function via(object $notifiable): array
    {
        if ($this->hedef === 'doktor') {
            return $this->doktorAppChannels();
        }

        return ['mail'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $vars = BildirimSablonu::varsFromRandevu($this->randevu);
        $yeni = ($vars['tarih'] ?? '').' '.($vars['saat'] ?? '');
        $eski = $this->formatDate($this->eskiTarih).' '.substr($this->eskiSaat, 0, 5);

        return [
            'type' => 'randevu_erteleme',
            'randevu_id' => $this->randevu->id,
            'title' => 'Randevu saati değişti',
            'body' => ($vars['hasta'] ?? 'Hasta').' · '.$eski.' → '.$yeni,
            'baslik' => 'Randevu saati değişti',
            'mesaj' => 'Randevu '.$eski.' tarihinden '.$yeni.' tarihine alındı.',
            'deep_link' => 'randevuajandam-doktor://appointment/'.$this->randevu->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $vars = BildirimSablonu::varsFromRandevu($this->randevu);
        $yeni = ($vars['tarih'] ?? '').' '.($vars['saat'] ?? '');
        $eski = $this->formatDate($this->eskiTarih).' '.substr($this->eskiSaat, 0, 5);

        return (new MailMessage)
            ->subject('Randevu saatiniz güncellendi')
            ->greeting('Sayın '.($notifiable->ad_soyad ?? $notifiable->ad ?? 'Hasta').',')
            ->line('Randevu saatiniz hekiminiz tarafından güncellendi.')
            ->line('Eski: '.$eski)
            ->line('Yeni: '.$yeni)
            ->line('Hekim: '.($vars['doktor'] ?? ''))
            ->line('Sağlıklı günler dileriz.');
    }

    private function formatDate(string $tarih): string
    {
        try {
            return \Carbon\Carbon::parse($tarih)->format('d.m.Y');
        } catch (\Throwable) {
            return $tarih;
        }
    }
}
