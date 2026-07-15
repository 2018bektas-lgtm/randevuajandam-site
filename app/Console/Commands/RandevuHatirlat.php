<?php

namespace App\Console\Commands;

use App\Models\Randevu;
use App\Notifications\RandevuHatirlatma;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RandevuHatirlat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'randevu:hatirlat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Onaylı randevular için hastalara 1 gün ve 2 saat öncesinden hatırlatma e-postası ve SMS gönderir';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Randevu hatırlatma işlemi başlatıldı...');

        $this->hatirlatBirGunOnce();
        $this->hatirlatIkiSaatOnce();

        $this->info('Randevu hatırlatma işlemi tamamlandı.');
    }

    /**
     * Send reminders for appointments tomorrow (1 day before)
     */
    protected function hatirlatBirGunOnce(): void
    {
        $yarinkiTarih = Carbon::tomorrow()->toDateString();

        $randevular = Randevu::where('tarih', $yarinkiTarih)
            ->where('durum', 'onaylandi')
            ->where('hatirlatma_1gun_gonderildi', false)
            ->with(['hasta', 'doktor'])
            ->get();

        if ($randevular->isEmpty()) {
            $this->comment('Yarın için hatırlatma gönderilecek randevu bulunamadı.');

            return;
        }

        $count = 0;
        foreach ($randevular as $randevu) {
            if ($randevu->hasta) {
                try {
                    $randevu->hasta->notify(new RandevuHatirlatma($randevu, '1 gün'));
                    $randevu->update(['hatirlatma_1gun_gonderildi' => true]);
                    $count++;
                } catch (\Exception $e) {
                    Log::error('1 Günlük randevu hatırlatma hatası (Randevu ID: '.$randevu->id.'): '.$e->getMessage());
                }
            }
        }

        $this->info("{$count} adet randevu için 1 günlük hatırlatma gönderildi.");
    }

    /**
     * Send reminders for appointments starting in 2 hours
     */
    protected function hatirlatIkiSaatOnce(): void
    {
        $bugunkuTarih = Carbon::today()->toDateString();

        $randevular = Randevu::where('tarih', $bugunkuTarih)
            ->where('durum', 'onaylandi')
            ->where('hatirlatma_2saat_gonderildi', false)
            ->with(['hasta', 'doktor'])
            ->get();

        if ($randevular->isEmpty()) {
            $this->comment('Bugün için 2 saatlik hatırlatma gönderilecek randevu bulunamadı.');

            return;
        }

        $count = 0;
        foreach ($randevular as $randevu) {
            $randevuZamani = Carbon::parse($randevu->tarih->toDateString().' '.$randevu->saat);
            $diffInMinutes = now()->diffInMinutes($randevuZamani, false); // false to keep it positive if future, negative if past

            // If appointment is starting within 120 minutes (2 hours) and has not passed
            if ($diffInMinutes > 0 && $diffInMinutes <= 120) {
                if ($randevu->hasta) {
                    try {
                        $randevu->hasta->notify(new RandevuHatirlatma($randevu, '2 saat'));
                        $randevu->update(['hatirlatma_2saat_gonderildi' => true]);
                        $count++;
                    } catch (\Exception $e) {
                        Log::error('2 Saatlik randevu hatırlatma hatası (Randevu ID: '.$randevu->id.'): '.$e->getMessage());
                    }
                }
            }
        }

        $this->info("{$count} adet randevu için 2 saatlik hatırlatma gönderildi.");
    }
}
