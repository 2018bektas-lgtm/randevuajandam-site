<?php

namespace App\Listeners;

use App\Events\RandevuDurumuDegisti;
use App\Events\RandevuOlusturuldu;
use Illuminate\Support\Facades\Log;

class RandevuLogKaydet
{
    /**
     * Handle RandevuOlusturuldu event.
     */
    public function olusturuldu(RandevuOlusturuldu $event): void
    {
        Log::channel('stack')->info('Yeni randevu oluşturuldu', [
            'randevu_id' => $event->randevu->id,
            'doktor_id' => $event->randevu->doktor_id,
            'hasta_id' => $event->randevu->hasta_id,
            'tarih' => $event->randevu->tarih?->toDateString(),
            'saat' => $event->randevu->saat,
            'durum' => $event->randevu->durum,
        ]);
    }

    /**
     * Handle RandevuDurumuDegisti event.
     */
    public function durumDegisti(RandevuDurumuDegisti $event): void
    {
        Log::channel('stack')->info('Randevu durumu değişti', [
            'randevu_id' => $event->randevu->id,
            'doktor_id' => $event->randevu->doktor_id,
            'hasta_id' => $event->randevu->hasta_id,
            'eski_durum' => $event->eskiDurum,
            'yeni_durum' => $event->yeniDurum,
        ]);
    }
}
