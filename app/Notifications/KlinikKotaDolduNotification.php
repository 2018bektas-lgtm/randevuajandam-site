<?php

namespace App\Notifications;

use App\Models\Klinik;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KlinikKotaDolduNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Klinik $klinik
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $dahil = $this->klinik->dahilDoktorLimiti();
        $efektif = $this->klinik->efektifDoktorLimiti();
        $mevcut = $this->klinik->doktorlar()->count();

        $ekKoltukUrl = route('hekim.klinik.ek-koltuk');

        return (new MailMessage)
            ->subject('Hekim Kotanız Doldu - ' . $this->klinik->ad)
            ->greeting('Merhaba ' . ($notifiable->ad_soyad ?? 'Klinik Sahibi') . ',')
            ->line("Kliniğinizdeki hekim sayısı limitinize ulaştı ({$mevcut}/{$efektif}).")
            ->line("Paket dahil kotanız: {$dahil} hekim, Ek koltuk: " . (int) $this->klinik->ek_doktor_koltuk_sayisi . " hekim.")
            ->line('Yeni hekim davet edebilmek için paketinizi yükseltebilir veya ek hekim koltuğu satın alabilirsiniz.')
            ->action('Ek Hekim Koltuğu Satın Al', $ekKoltukUrl)
            ->line('Teşekkür ederiz, Randevu Ajandam Ekibi');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tip' => 'klinik_kota_doldu',
            'klinik_id' => $this->klinik->id,
            'klinik_ad' => $this->klinik->ad,
            'efektif_limit' => $this->klinik->efektifDoktorLimiti(),
            'mevcut_hekim' => $this->klinik->doktorlar()->count(),
            'mesaj' => 'Hekim kotanız dolmuştur. Ek koltuk satın alarak kapasitenizi artırabilirsiniz.',
            'action_url' => route('hekim.klinik.ek-koltuk'),
        ];
    }
}
