<?php

namespace App\Notifications\Channels;

use App\Models\Doktor;
use App\Models\Randevu;
use App\Services\SmsKontorService;
use App\Services\SmsService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SmsChannel
{
    public function __construct(
        protected SmsService $smsService,
        protected SmsKontorService $smsKontor
    ) {}

    public function send(mixed $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        $phone = $notifiable->telefon ?? null;
        if (empty($phone)) {
            return;
        }

        $message = $notification->toSms($notifiable);
        if (empty($message)) {
            return;
        }

        // Kontör: randevu üzerinden doktor bul
        $doktor = $this->resolveDoktor($notification);
        if ($doktor) {
            if (! $this->smsKontor->doktorGonderebilir($doktor)) {
                Log::info('SMS atlandı: paket özelliği veya kontör yetersiz', [
                    'doktor_id' => $doktor->id,
                    'notification' => $notification::class,
                ]);

                return;
            }
        }

        $header = $doktor?->resolveSmsHeader();
        $ok = $this->smsService->send($phone, $message, $header);
        if ($ok && $doktor) {
            $this->smsKontor->tuket($doktor, 1);
        }
    }

    protected function resolveDoktor(Notification $notification): ?Doktor
    {
        if (property_exists($notification, 'randevu') && $notification->randevu instanceof Randevu) {
            $r = $notification->randevu;
            if ($r->relationLoaded('doktor')) {
                return $r->doktor;
            }

            return $r->doktor()->first();
        }

        return null;
    }
}
