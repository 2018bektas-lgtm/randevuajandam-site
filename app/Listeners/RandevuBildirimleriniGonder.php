<?php

namespace App\Listeners;

use App\Events\RandevuDurumuDegisti;
use App\Events\RandevuOlusturuldu;
use App\Notifications\RandevuIptalEdildi;
use App\Notifications\RandevuOnaylandi;
use App\Notifications\YeniRandevuTalebi;
use App\Services\BeklemeListesiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RandevuBildirimleriniGonder
{
    /**
     * Handle the RandevuOlusturuldu event.
     */
    public function olusturuldu(RandevuOlusturuldu $event): void
    {
        $randevu = $event->randevu;
        $doktor = $randevu->doktor;
        $hasta = $randevu->hasta;

        // 1. Notify doctor about the new appointment request
        if ($doktor) {
            $doktor->notify(new YeniRandevuTalebi($randevu));
        }

        // 2. If it was automatically approved, also notify patient immediately
        if ($randevu->durum === 'onaylandi' && $hasta) {
            $hasta->notify(new RandevuOnaylandi($randevu));
        }
    }

    /**
     * Handle the RandevuDurumuDegisti event.
     */
    public function durumDegisti(RandevuDurumuDegisti $event): void
    {
        $randevu = $event->randevu;
        $hasta = $randevu->hasta;
        $doktor = $randevu->doktor;

        // 1. If status changed to approved (onaylandi), notify patient
        if ($event->yeniDurum === 'onaylandi' && $hasta) {
            $hasta->notify(new RandevuOnaylandi($randevu));
        }

        // 2. If status changed to canceled (iptal), notify the other party
        if ($event->yeniDurum === 'iptal') {
            $iptalEden = $this->resolveIptalEden();

            if ($iptalEden === 'hasta') {
                if ($doktor) {
                    $doktor->notify(new RandevuIptalEdildi($randevu, 'hasta'));
                }
            } else {
                // Doctor, staff or admin canceled → notify patient
                if ($hasta) {
                    $hasta->notify(new RandevuIptalEdildi($randevu, 'doktor'));
                }
            }

            // 3. Notify waitlist candidates about freed slot
            try {
                app(BeklemeListesiService::class)->notifyOnSlotOpened($randevu);
            } catch (\Throwable $e) {
                Log::warning('Bekleme listesi bildirim hatası: '.$e->getMessage(), [
                    'randevu_id' => $randevu->id,
                ]);
            }
        }
    }

    /**
     * Who cancelled: web session, mobile token middleware, or default doctor-side.
     */
    private function resolveIptalEden(): string
    {
        if (Auth::guard('hasta')->check() || request()->attributes->get('auth_hasta')) {
            return 'hasta';
        }

        if (
            Auth::guard('doktor')->check()
            || request()->attributes->get('auth_doktor')
            || Auth::guard('yonetici')->check()
            || request()->attributes->get('auth_personel')
        ) {
            return 'doktor';
        }

        // Unknown context (queued job without request auth): prefer notifying doctor
        // only when we cannot tell — safer default for web guest cancels is rare.
        return 'doktor';
    }
}
