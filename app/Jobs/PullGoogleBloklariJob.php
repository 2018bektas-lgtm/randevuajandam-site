<?php

namespace App\Jobs;

use App\Models\Doktor;
use App\Services\GoogleCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Bir hekim icin Google'dan bloklari cek. Webhook tetikli veya elle (renew/resync).
 * Incremental: syncToken varsa oradan devam eder.
 */
class PullGoogleBloklariJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = [15, 60, 300];

    public $timeout = 120;

    public function __construct(public int $doktorId) {}

    public function handle(GoogleCalendarService $service): void
    {
        if (! $service->enabled()) {
            return;
        }

        $doktor = Doktor::with('klinik')->find($this->doktorId);
        if (! $doktor || ! $doktor->isGoogleTakvimBagli()) {
            return;
        }

        try {
            $sayi = $service->pullBloklar($doktor);
            Log::info('Google bloklar pull', ['doktor_id' => $doktor->id, 'guncellenen' => $sayi]);
        } catch (Throwable $e) {
            Log::warning('PullGoogleBloklariJob hata', [
                'doktor_id' => $this->doktorId,
                'msg' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function uniqueId(): string
    {
        return 'google-pull-'.$this->doktorId;
    }
}
