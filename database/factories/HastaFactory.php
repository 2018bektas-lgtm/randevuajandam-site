<?php

namespace Database\Factories;

use App\Models\Hasta;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Hasta>
 */
class HastaFactory extends Factory
{
    protected $model = Hasta::class;

    public function definition(): array
    {
        return [
            'ad' => fake()->firstName(),
            'soyad' => fake()->lastName(),
            'e_posta' => fake()->unique()->safeEmail(),
            'sifre' => Hash::make('sifre123'),
            'telefon' => '0 (5'.fake()->numerify('##').') '.fake()->numerify('###').' '.fake()->numerify('##').' '.fake()->numerify('##'),
            'aktif_mi' => true,
        ];
    }

    /**
     * Indicate that the patient is inactive.
     */
    public function pasif(): static
    {
        return $this->state(fn (array $attributes) => [
            'aktif_mi' => false,
        ]);
    }
}
