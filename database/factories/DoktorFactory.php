<?php

namespace Database\Factories;

use App\Models\Doktor;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Paket;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Doktor>
 */
class DoktorFactory extends Factory
{
    protected $model = Doktor::class;

    public function definition(): array
    {
        $il = Il::inRandomOrder()->first();
        $ilce = $il ? Ilce::where('il_id', $il->id)->inRandomOrder()->first() : null;

        return [
            'ad_soyad' => fake()->name(),
            'e_posta' => fake()->unique()->safeEmail(),
            'sifre' => Hash::make('sifre123'),
            'telefon' => '0 (5'.fake()->numerify('##').') '.fake()->numerify('###').' '.fake()->numerify('##').' '.fake()->numerify('##'),
            'il_id' => $il?->id,
            'ilce_id' => $ilce?->id,
            'tur' => 'bireysel',
            'paket_id' => Paket::where('aktif_mi', true)->inRandomOrder()->first()?->id,
            'odeme_periyodu' => fake()->randomElement(['aylik', 'yillik']),
            'uyelik_baslangic' => now(),
            'uyelik_bitis' => now()->addYear(),
            'aktif_mi' => true,
            'unvan' => fake()->randomElement(['Prof. Dr.', 'Doç. Dr.', 'Uzm. Dr.', 'Op. Dr.', 'Dr.']),
            'uzmanlik_alani' => 'Genel',
        ];
    }

    /**
     * Indicate that the doctor is inactive.
     */
    public function pasif(): static
    {
        return $this->state(fn (array $attributes) => [
            'aktif_mi' => false,
        ]);
    }

    /**
     * Indicate that the doctor's subscription has expired.
     */
    public function uyelikSuresiDolmus(): static
    {
        return $this->state(fn (array $attributes) => [
            'uyelik_baslangic' => now()->subYear(),
            'uyelik_bitis' => now()->subDay(),
        ]);
    }
}
