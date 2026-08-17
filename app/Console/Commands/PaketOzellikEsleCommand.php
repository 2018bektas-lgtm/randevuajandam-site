<?php

namespace App\Console\Commands;

use App\Support\PaketOzellikKatalogu;
use Illuminate\Console\Command;

class PaketOzellikEsleCommand extends Command
{
    protected $signature = 'paket:ozellik-esle {kod=ai_asistan : Özellik kodu}';

    protected $description = 'Katalogdaki özelliği Profesyonel+ paketlere bağlar (mevcut atamaları silmez).';

    public function handle(): int
    {
        $kod = (string) $this->argument('kod');
        $rows = PaketOzellikKatalogu::attachToProfesyonelTier($kod);

        if ($rows === []) {
            $this->error("Özellik bulunamadı: {$kod}");

            return self::FAILURE;
        }

        foreach ($rows as $row) {
            $this->line(sprintf(
                '#%d %s %s',
                $row['id'],
                $row['ad'],
                $row['eklendi'] ? 'EKLENDI' : 'zaten var'
            ));
        }

        return self::SUCCESS;
    }
}
