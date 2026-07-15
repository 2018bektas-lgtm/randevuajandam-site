<?php

namespace App\Console\Commands;

use App\Models\KlinikDavetiye;
use Illuminate\Console\Command;

class DavetSuresiKontrolCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'klinik:davet-suresi-kontrol';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $signature_description = 'Geçerlilik süresi dolan klinik davetiyelerini iptal eder.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $affected = KlinikDavetiye::suresiDolmus()->update([
            'durum' => 'suresi_doldu',
        ]);

        $this->info("{$affected} adet süresi dolan klinik davetiyesi güncellendi.");
    }
}
