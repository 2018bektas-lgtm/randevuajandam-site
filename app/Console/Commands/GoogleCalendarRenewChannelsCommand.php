<?php

namespace App\Console\Commands;

use App\Models\Doktor;
use App\Models\Klinik;
use App\Services\GoogleCalendarService;
use Illuminate\Console\Command;
use Throwable;

/**
 * Google Calendar push notification kanallari 7 gunde bir dolar.
 * Bu komut suresi 24 saatten az kalan (veya sureli olmayan) kanallari
 * kapatip yeniden acar. console.php'de gunluk schedule edilmelidir.
 */
class GoogleCalendarRenewChannelsCommand extends Command
{
    protected $signature = 'google-calendar:renew-channels';

    protected $description = 'Google Takvim push notification kanallarini yeniler (7 gun sinirini gecmemek icin).';

    public function handle(GoogleCalendarService $service): int
    {
        if (! $service->enabled()) {
            $this->warn('Google Calendar entegrasyonu kapali (config google_calendar.enabled=false).');
            return self::SUCCESS;
        }

        $threshold = now()->addDay()->getTimestamp();
        $sayi = 0;

        // Bireysel hekimler
        Doktor::query()
            ->whereNotNull('google_calendar_config')
            ->whereNull('klinik_id')
            ->chunkById(50, function ($doktorlar) use ($service, $threshold, &$sayi) {
                foreach ($doktorlar as $doktor) {
                    $cfg = $doktor->google_calendar_config ?? [];
                    $expires = (int) ($cfg['channel_expires_at'] ?? 0);
                    if ($expires !== 0 && $expires > $threshold) {
                        continue; // hala geniş süre var
                    }
                    try {
                        $service->stopWatch($doktor);
                        $service->watchChannel($doktor);
                        $sayi++;
                    } catch (Throwable $e) {
                        $this->error('Hekim '.$doktor->id.' watch yenileme hatasi: '.$e->getMessage());
                    }
                }
            });

        // Klinikler — sahip hekim uzerinden pull tetiklemek icin klinik channel'ini
        // tutan model klinik'in kendisi. Ama servisimiz Doktor|Klinik ile calisir; klinik
        // icin watch acmak istersek altında bir hekim uzerinden yapmak gerekir. Klinik
        // channel'i sahip hekim kullanilarak acilir; sahip yoksa atla.
        Klinik::query()
            ->whereNotNull('google_calendar_config')
            ->chunkById(50, function ($klinikler) use ($service, $threshold, &$sayi) {
                foreach ($klinikler as $klinik) {
                    $cfg = $klinik->google_calendar_config ?? [];
                    $expires = (int) ($cfg['channel_expires_at'] ?? 0);
                    if ($expires !== 0 && $expires > $threshold) {
                        continue;
                    }
                    $sahip = Doktor::query()
                        ->where('klinik_id', $klinik->id)
                        ->where('klinik_rolu', 'sahip')
                        ->first();
                    if (! $sahip) {
                        continue;
                    }
                    try {
                        $service->stopWatch($sahip);
                        $service->watchChannel($sahip);
                        $sayi++;
                    } catch (Throwable $e) {
                        $this->error('Klinik '.$klinik->id.' watch yenileme hatasi: '.$e->getMessage());
                    }
                }
            });

        $this->info("Yenilenen kanal: {$sayi}");
        return self::SUCCESS;
    }
}
