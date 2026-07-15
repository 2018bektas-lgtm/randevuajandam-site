<?php

namespace Database\Factories;

use App\Models\Yonetici;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Yonetici>
 */
class YoneticiFactory extends Factory
{
    protected $model = Yonetici::class;

    public function definition(): array
    {
        return [
            'ad_soyad' => fake()->name(),
            'e_posta' => fake()->unique()->safeEmail(),
            'sifre' => Hash::make('sifre123'),
            'telefon' => '0 (5'.fake()->numerify('##').') '.fake()->numerify('###').' '.fake()->numerify('##').' '.fake()->numerify('##'),
            'aktif_mi' => true,
        ];
    }

    /**
     * Indicate that the administrator is inactive.
     */
    public function pasif(): static
    {
        return $this->state(fn (array $attributes) => [
            'aktif_mi' => false,
        ]);
    }
}
