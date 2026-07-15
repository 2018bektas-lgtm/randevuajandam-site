<?php

namespace App\Listeners;

use App\Events\RandevuDurumuDegisti;
use App\Models\Odeme;

class RandevuFinansKaydet
{
    /**
     * Handle the event.
     */
    public function durumDegisti(RandevuDurumuDegisti $event): void
    {
        $randevu = $event->randevu;
        $yeniDurum = $event->yeniDurum;

        if ($yeniDurum === 'tamamlandi') {
            // Check if a payment record already exists
            $odeme = Odeme::where('randevu_id', $randevu->id)->first();

            if (! $odeme) {
                // Get service price
                $tutar = $randevu->hizmet?->fiyat ?? 0.00;

                Odeme::create([
                    'doktor_id' => $randevu->doktor_id,
                    'randevu_id' => $randevu->id,
                    'hasta_id' => $randevu->hasta_id,
                    'hizmet_id' => $randevu->hizmet_id,
                    'tutar' => $tutar,
                    'odenen_tutar' => 0.00,
                    'odeme_yontemi' => 'nakit',
                    'durum' => 'beklemede',
                    'odeme_tarihi' => $randevu->tarih,
                    'aciklama' => 'Randevu tamamlandığında otomatik oluşturulan ödeme kaydı.',
                ]);
            }
        } elseif ($yeniDurum === 'iptal') {
            // If there's an existing payment, mark it as cancelled (iptal)
            $odeme = Odeme::where('randevu_id', $randevu->id)->first();
            if ($odeme) {
                $odeme->update([
                    'durum' => 'iptal',
                ]);
            }
        }
    }
}
