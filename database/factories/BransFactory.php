<?php

namespace Database\Factories;

use App\Models\Brans;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Brans>
 */
class BransFactory extends Factory
{
    protected $model = Brans::class;

    public function definition(): array
    {
        return [
            'ad' => fake()->unique()->word().' Tıbbı',
        ];
    }
}
