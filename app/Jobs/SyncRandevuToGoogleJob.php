<?php

namespace App\Jobs;

use App\Models\Randevu;
use App\Services\GoogleCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Bir randevunun Google Takvim yansimasini uygular.
 * Aksiyon randevunun mevcut durumuna gore secilir:
 *   - onaylandi / tamamlandi -> push (insert veya update)
 *   - iptal                  -> Google'dan sil
 *   - beklemede              -> no-op (Google'a beklemedeki yazmiyoruz)
 */
class SyncRandevuToGoogleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = [30, 180, 600];

    public $timeout = 45;

    public function __construct(public int $randevuId) {}

    public function handle(GoogleCalendarService $service): void
    {
        if (! $service->enabled()) {
            return;
        }

        $randevu = Randevu::with(['doktor', 'hizmet'])->find($this->randevuId);
        if (! $randevu) {
            return;
        }
        if (! $randevu->doktor || ! $randevu->doktor->isGoogleTakvimBagli()) {
            return;
        }

        try {
            if ($randevu->durum === 'iptal') {
                $service->deleteRandevu($randevu);
                return;
            }

            if (in_array($randevu->durum, ['onaylandi', 'tamamlandi'], true)) {
                $service->pushRandevu($randevu);
                return;
            }

            // beklemede -> no-op
        } catch (Throwable $e) {
            Log::warning('SyncRandevuToGoogleJob hata', [
                'randevu_id' => $this->randevuId,
                'msg' => $e->getMessage(),
            ]);
            throw $e; // retry devreye girsin
        }
    }
}
