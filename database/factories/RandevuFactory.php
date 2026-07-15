<?php

namespace Database\Factories;

use App\Models\Doktor;
use App\Models\Hasta;
use App\Models\Hizmet;
use App\Models\Randevu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Randevu>
 */
class RandevuFactory extends Factory
{
    protected $model = Randevu::class;

    public function definition(): array
    {
        $hasta = Hasta::factory()->create();

        return [
            'doktor_id' => Doktor::factory(),
            'hizmet_id' => Hizmet::factory(),
            'hasta_id' => $hasta->id,
            'ad' => $hasta->ad,
            'soyad' => $hasta->soyad,
            'telefon' => $hasta->telefon,
            'e_posta' => $hasta->e_posta,
            'tarih' => now()->addDays(fake()->numberBetween(1, 30))->toDateString(),
            'saat' => fake()->randomElement(['09:00', '09:30', '10:00', '10:30', '11:00', '14:00', '14:30', '15:00', '15:30', '16:00']),
            'durum' => 'beklemede',
        ];
    }

    /**
     * Indicate that the appointment is approved.
     */
    public function onaylandi(): static
    {
        return $this->state(fn (array $attributes) => [
            'durum' => 'onaylandi',
        ]);
    }

    /**
     * Indicate that the appointment is completed.
     */
    public function tamamlandi(): static
    {
        return $this->state(fn (array $attributes) => [
            'durum' => 'tamamlandi',
        ]);
    }

    /**
     * Indicate that the appointment is cancelled.
     */
    public function iptal(): static
    {
        return $this->state(fn (array $attributes) => [
            'durum' => 'iptal',
        ]);
    }
}
