<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Brans;

class BransSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branslar = [
            'Diş Hekimliği',
            'Ortodonti',
            'Pedodonti (Çocuk Diş Hekimliği)',
            'Göz Hastalıkları',
            'Kardiyoloji',
            'Kulak Burun Boğaz (KBB)',
            'Dahiliye (İç Hastalıkları)',
            'Genel Cerrahi',
            'Kadın Hastalıkları ve Doğum',
            'Nöroloji',
            'Psikiyatri',
            'Fizik Tedavi ve Rehabilitasyon',
            'Diyetisyen (Beslenme ve Diyetetik)',
            'Psikoloji',
            'Çocuk Sağlığı ve Hastalıkları',
            'Cildiye (Dermatoloji)',
            'Ortopedi ve Travmatoloji'
        ];

        foreach ($branslar as $brans) {
            Brans::firstOrCreate(['ad' => $brans]);
        }
    }
}
