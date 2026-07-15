<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException(
                'Production ortamında DatabaseSeeder çalıştırılamaz (sabit demo şifreler).'
            );
        }

        // User::factory(10)->create();

        \App\Models\User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        $this->call(YoneticiSeeder::class);
        $this->call(PaketSeeder::class);
        $this->call(KlinikSeeder::class);
        $this->call(BransSeeder::class);
        $this->call(UnvanSeeder::class);
        $this->call(IlIlceSeeder::class);
        $this->call(DoktorSeeder::class);
        $this->call(SiteAyariSeeder::class);
        $this->call(HastaVeRandevuSeeder::class);
    }
}
