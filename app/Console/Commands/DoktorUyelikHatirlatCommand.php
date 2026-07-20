<?php

namespace App\Console\Commands;

use App\Models\Doktor;
use App\Notifications\DoktorUyelikBitisBildirimi;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DoktorUyelikHatirlatCommand extends Command
{
    protected $signature = 'doktor:uyelik-hatirlat';

    protected $description = 'Bireysel hekim üyelik bitişine 7/3/1 gün kala e-posta hatırlatır.';

    public function handle(): int
    {
        $today = Carbon::today();
        $count = 0;

        $doktorlar = Doktor::query()
            ->where('aktif_mi', true)
            ->whereNotNull('uyelik_bitis')
            ->whereNotNull('paket_id')
            ->where(function ($q) {
                $q->whereNull('odeme_periyodu')->orWhere('odeme_periyodu', '!=', 'deneme');
            })
            ->get();

        foreach ($doktorlar as $doktor) {
            $bitis = Carbon::parse($doktor->uyelik_bitis)->startOfDay();
            $diff = (int) $today->diffInDays($bitis, false);

            $map = [
                7 => 'uyelik_hatirlat_7_at',
                3 => 'uyelik_hatirlat_3_at',
                1 => 'uyelik_hatirlat_1_at',
            ];
            if (! isset($map[$diff])) {
                continue;
            }
            $col = $map[$diff];
            if ($doktor->{$col}) {
                continue; // bu dönem için gönderilmiş
            }

            try {
                $doktor->notify(new DoktorUyelikBitisBildirimi($diff));
                $doktor->forceFill([$col => now()])->save();
                $count++;
            } catch (\Throwable $e) {
                $this->warn('Mail hata #'.$doktor->id.': '.$e->getMessage());
            }
        }

        $this->info("{$count} hekime üyelik bitiş hatırlatması gönderildi.");

        return self::SUCCESS;
    }
}
