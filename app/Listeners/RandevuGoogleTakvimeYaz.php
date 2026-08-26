<?php

namespace App\Listeners;

use App\Events\RandevuDurumuDegisti;
use App\Events\RandevuOlusturuldu;
use App\Jobs\SyncRandevuToGoogleJob;
use App\Models\Randevu;

/**
 * Randevu lifecycle event'lerini dinler ve gerekiyorsa SyncRandevuToGoogleJob
 * kuyruklar. Dogrudan API cagirmiyoruz — request response yolunu bloklama.
 * Job kendi icinde durum bazli aksiyon secer (push / delete / no-op).
 */
class RandevuGoogleTakvimeYaz
{
    public function olusturuldu(RandevuOlusturuldu $event): void
    {
        $this->kuyrukla($event->randevu);
    }

    public function durumDegisti(RandevuDurumuDegisti $event): void
    {
        $this->kuyrukla($event->randevu);
    }

    protected function kuyrukla(Randevu $randevu): void
    {
        if (! config('google_calendar.enabled', false)) {
            return;
        }

        $doktor = $randevu->doktor;
        if (! $doktor || ! $doktor->isGoogleTakvimBagli()) {
            return;
        }

        // Beklemede'yi Google'a hic yazmiyoruz; onay/iptal/tamamlandi'da is var.
        if (! in_array($randevu->durum, ['onaylandi', 'tamamlandi', 'iptal'], true)) {
            return;
        }

        SyncRandevuToGoogleJob::dispatch($randevu->id)->afterCommit();
    }
}
