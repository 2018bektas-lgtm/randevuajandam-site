<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Yonetici;

class YoneticiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Yonetici::updateOrCreate(
            ['e_posta' => 'admin@test.com'],
            [
                'ad_soyad' => 'Sistem Yöneticisi',
                'sifre' => 'sifre123', // Hashing happens automatically via model cast
                'telefon' => '05555555555',
                'aktif_mi' => true,
            ]
        );
    }
}
