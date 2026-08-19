<?php

namespace App\Console\Commands;

use App\Models\Hasta;
use Illuminate\Console\Command;

class HastaImhaCommand extends Command
{
    protected $signature = 'hasta:imha';

    protected $description = 'Silme talebi 30 gunu gecen hasta kayitlarini imha eder (KVKK).';

    public function handle(): int
    {
        $hastalar = Hasta::whereNotNull('silme_talep_at')
            ->where('silme_talep_at', '<=', now()->subDays(30))
            ->get();

        foreach ($hastalar as $hasta) {
            // 1) Token'lari sil
            $hasta->apiTokens()->delete();

            // 2) Randevulari anonimlestir (finansal butunluk icin silme)
            $hasta->randevular()->update([
                'hasta_id' => null,
                'not' => null,
            ]);

            // 3) Kaydi kalici sil
            $hasta->forceDelete();

            $this->info("Hasta #{$hasta->id} imha edildi.");
        }

        return self::SUCCESS;
    }
}
