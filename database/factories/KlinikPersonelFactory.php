<?php

namespace Database\Factories;

use App\Models\Klinik;
use App\Models\KlinikPersonel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KlinikPersonel>
 */
class KlinikPersonelFactory extends Factory
{
    protected $model = KlinikPersonel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'klinik_id' => Klinik::factory(),
            'ad_soyad' => fake()->name(),
            'e_posta' => fake()->unique()->safeEmail(),
            'sifre' => 'password',
            'telefon' => fake()->phoneNumber(),
            'rol' => fake()->randomElement(['sekreter', 'resepsiyonist', 'muhasebeci']),
            'yetkiler' => [
                'randevu' => true,
                'hasta' => true,
                'odeme' => false,
                'finans' => false,
            ],
            'sifre_degistirildi_mi' => false,
            'aktif_mi' => true,
        ];
    }
}
