<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Unvan;

class UnvanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $unvanlar = [
            'Prof. Dr.',
            'Doç. Dr.',
            'Yrd. Doç. Dr.',
            'Uzm. Dr.',
            'Dr.',
            'Op. Dr.',
            'Klinik Psikolog',
            'Psikolog',
            'Diyetisyen (Dyt.)',
            'Fizyoterapist (Fzt.)',
            'Aile Danışmanı',
            'Diğer'
        ];

        foreach ($unvanlar as $unvan) {
            Unvan::firstOrCreate(['ad' => $unvan]);
        }
    }
}
