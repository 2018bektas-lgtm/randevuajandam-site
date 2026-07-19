<?php

namespace Database\Seeders;

use App\Models\Unvan;
use Illuminate\Database\Seeder;

/**
 * Türkiye'de hekim ve sağlık meslekleri için yaygın unvanlar.
 *
 *   php artisan db:seed --class=UnvanSeeder
 */
class UnvanSeeder extends Seeder
{
    public function run(): void
    {
        $unvanlar = [
            // Akademik / hekim
            'Prof. Dr.',
            'Doç. Dr.',
            'Dr. Öğr. Üyesi',
            'Yrd. Doç. Dr.',
            'Uzm. Dr.',
            'Op. Dr.',
            'Dr.',
            'Asistan Dr.',
            'Pratisyen Dr.',
            'Tabip',

            // Diş
            'Dt.',
            'Uzm. Dt.',
            'Prof. Dr. Dt.',
            'Doç. Dr. Dt.',

            // Psikoloji / danışmanlık
            'Klinik Psikolog',
            'Psikolog',
            'Uzm. Psikolog',
            'Psikolojik Danışman',
            'Aile Danışmanı',
            'Psikoterapist',

            // Beslenme / fizyo / terapi
            'Dyt.',
            'Diyetisyen',
            'Uzm. Dyt.',
            'Fzt.',
            'Fizyoterapist',
            'Uzm. Fzt.',
            'Dil ve Konuşma Terapisti',
            'Ergoterapist',
            'Odyolog',
            'Podolog',

            // Ebe / hemşire / teknik
            'Ebe',
            'Hemşire',
            'Uzm. Hemşire',
            'Sağlık Teknikeri',
            'ATT',
            'Paramedik',

            // Diğer
            'Öğr. Gör.',
            'Uzm.',
            'Danışman',
            'Diğer',
        ];

        $created = 0;
        $skipped = 0;

        foreach ($unvanlar as $ad) {
            $ad = trim($ad);
            if ($ad === '') {
                continue;
            }

            $model = Unvan::firstOrCreate(['ad' => $ad]);
            if ($model->wasRecentlyCreated) {
                $created++;
            } else {
                $skipped++;
            }
        }

        $this->command?->info("Unvan seeder: {$created} eklendi, {$skipped} zaten vardı. Toplam: ".Unvan::count());
    }
}
