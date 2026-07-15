<?php

namespace App\Console\Commands;

use App\Models\Klinik;
use App\Notifications\KlinikUyelikBitisBildirimi;
use Illuminate\Console\Command;
use Carbon\Carbon;

class KlinikUyelikHatirlatCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'klinik:uyelik-hatirlat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Üyelik bitiş tarihi yaklaşan klinik sahiplerine bildirim gönderir.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $count = 0;

        // Klinikler ve sahip hekimlerini çek
        $klinikler = Klinik::where('aktif_mi', true)
            ->whereNotNull('uyelik_bitis')
            ->with('sahipDoktor')
            ->get();

        foreach ($klinikler as $klinik) {
            if (!$klinik->sahipDoktor) {
                continue;
            }

            $bitisTarihi = Carbon::parse($klinik->uyelik_bitis)->startOfDay();
            $diffInDays = $today->diffInDays($bitisTarihi, false);

            // Bitişe tam 7 gün, 1 gün kalmışsa veya bugün bitmişse (0 gün)
            if ($diffInDays === 7 || $diffInDays === 1 || $diffInDays === 0) {
                $klinik->sahipDoktor->notify(new KlinikUyelikBitisBildirimi($klinik, $diffInDays));
                $count++;
            }
        }

        $this->info("{$count} adet klinik sahibine üyelik bitiş bildirimi gönderildi.");
    }
}
