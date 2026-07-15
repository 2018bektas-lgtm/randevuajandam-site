<?php

namespace Database\Factories;

use App\Models\Doktor;
use App\Models\Hizmet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Hizmet>
 */
class HizmetFactory extends Factory
{
    protected $model = Hizmet::class;

    public function definition(): array
    {
        return [
            'doktor_id' => Doktor::factory(),
            'ad' => fake()->words(3, true).' Hizmeti',
            'aciklama' => fake()->paragraph(),
            'sure' => fake()->randomElement([15, 20, 30, 45, 60]),
            'fiyat' => fake()->randomFloat(2, 100, 5000),
            'aktif_mi' => true,
        ];
    }

    /**
     * Indicate that the service is inactive.
     */
    public function pasif(): static
    {
        return $this->state(fn (array $attributes) => [
            'aktif_mi' => false,
        ]);
    }
}
