<?php

namespace Tests\Feature;

use App\Models\SiteAyari;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        SiteAyari::create([
            'meta_baslik' => 'Randevu Ajandam',
            'meta_aciklama' => 'Test Aciklama',
            'meta_anahtar_kelimeler' => 'test, randevu',
            'meta_yazar' => 'Test Yazar',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
