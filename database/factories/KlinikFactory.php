<?php

namespace Database\Factories;

use App\Models\Doktor;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Klinik;
use App\Models\Paket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Klinik>
 */
class KlinikFactory extends Factory
{
    protected $model = Klinik::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ad' => fake()->company(),
            'sahip_doktor_id' => Doktor::factory(),
            'paket_id' => Paket::factory(),
            'telefon' => fake()->phoneNumber(),
            'e_posta' => fake()->companyEmail(),
            'adres' => fake()->address(),
            'il_id' => Il::inRandomOrder()->first()?->id ?? 1,
            'ilce_id' => Ilce::inRandomOrder()->first()?->id ?? 1,
            'aciklama' => fake()->paragraph(),
            'max_doktor_sayisi' => 10,
            'aktif_mi' => true,
            'uyelik_baslangic' => now(),
            'uyelik_bitis' => now()->addYear(),
        ];
    }
}
