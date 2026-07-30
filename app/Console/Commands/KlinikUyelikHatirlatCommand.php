<?php

namespace App\Console\Commands;

use App\Models\Klinik;
use App\Notifications\KlinikUyelikBitisBildirimi;
use Carbon\Carbon;
use Illuminate\Console\Command;

class KlinikUyelikHatirlatCommand extends Command
{
    protected $signature = 'klinik:uyelik-hatirlat';

    protected $description = 'Klinik üyelik bitişine 7/3/1 gün kala sahip hekime e-posta + bildirim (otomatik yenileme metni dahil).';

    public function handle(): int
    {
        $today = Carbon::today();
        $count = 0;

        $klinikler = Klinik::query()
            ->where('aktif_mi', true)
            ->whereNotNull('uyelik_bitis')
            ->with('sahipDoktor')
            ->get();

        foreach ($klinikler as $klinik) {
            $sahip = $klinik->sahipDoktor;
            if (! $sahip) {
                continue;
            }

            $bitisTarihi = Carbon::parse($klinik->uyelik_bitis)->startOfDay();
            $diffInDays = (int) $today->diffInDays($bitisTarihi, false);

            // Sahip hekimin hatırlatma kolonlarını klinik için de kullan (tek sefer / dönem)
            $map = [
                7 => 'uyelik_hatirlat_7_at',
                3 => 'uyelik_hatirlat_3_at',
                1 => 'uyelik_hatirlat_1_at',
            ];
            if (! isset($map[$diffInDays])) {
                continue;
            }
            $col = $map[$diffInDays];
            if ($sahip->{$col}) {
                continue;
            }

            $auto = $klinik->willAutoRenew();
            $periyotLabel = ($klinik->odeme_periyodu ?? '') === 'yillik' ? 'yıllık' : 'aylık';

            try {
                $sahip->notify(new KlinikUyelikBitisBildirimi(
                    $klinik,
                    $diffInDays,
                    $auto,
                    $klinik->estimatedRenewalAmount(),
                    $periyotLabel
                ));
                $sahip->forceFill([$col => now()])->save();
                $count++;
            } catch (\Throwable $e) {
                $this->warn('Klinik mail hata #'.$klinik->id.': '.$e->getMessage());
            }
        }

        $this->info("{$count} klinik sahibine üyelik / otomatik yenileme bildirimi gönderildi.");

        return self::SUCCESS;
    }
}
