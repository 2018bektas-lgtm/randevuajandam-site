<?php

namespace App\Notifications;

use App\Models\BeklemeListesi;
use App\Notifications\Concerns\NotifiesDoktorApp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BeklemeListesiKayitBildirimi extends Notification implements ShouldQueue
{
    use NotifiesDoktorApp;
    use Queueable;

    public function __construct(public BeklemeListesi $kayit) {}

    /**
     * @return array<int, string|class-string>
     */
    public function via(object $notifiable): array
    {
        return $this->doktorAppChannels();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $ad = trim(($this->kayit->ad ?? '').' '.($this->kayit->soyad ?? ''));
        if ($ad === '') {
            $ad = 'Hasta';
        }
        $tarih = $this->kayit->tercih_tarih
            ? (is_string($this->kayit->tercih_tarih)
                ? $this->kayit->tercih_tarih
                : $this->kayit->tercih_tarih->format('d.m.Y'))
            : 'esnek tarih';
        $saat = $this->kayit->tercih_saat ? substr((string) $this->kayit->tercih_saat, 0, 5) : '';

        return [
            'type' => 'bekleme_listesi',
            'kayit_id' => $this->kayit->id,
            'title' => 'Bekleme listesi kaydı',
            'body' => $ad.' · '.$tarih.($saat !== '' ? ' '.$saat : ''),
            'baslik' => 'Bekleme listesi kaydı',
            'mesaj' => $ad.' bekleme listesine kaydoldu.',
            'deep_link' => 'randevuajandam-doktor://waitlist',
        ];
    }
}
