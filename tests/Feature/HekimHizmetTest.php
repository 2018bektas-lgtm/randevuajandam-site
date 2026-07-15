<?php

namespace Tests\Feature;

use App\Models\Brans;
use App\Models\Doktor;
use App\Models\Hizmet;
use App\Models\Il;
use App\Models\Ilce;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HekimHizmetTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up a doctor for testing.
     */
    private function createDoktor(string $email = 'hekim@test.com'): Doktor
    {
        return Doktor::create([
            'ad_soyad' => 'Test Hekim',
            'e_posta' => $email,
            'sifre' => Hash::make('sifre123'),
            'telefon' => '05551234567',
            'tur' => 'bireysel',
            'unvan' => 'Prof. Dr.',
            'uzmanlik_alani' => 'Kardiyoloji',
            'mezuniyet' => null,
            'aktif_mi' => true,
        ]);
    }

    /**
     * Test doctor can manage services (CRUD).
     */
    public function test_hekim_can_manage_services(): void
    {
        $doktor = $this->createDoktor();

        // 1. View empty services list
        $response = $this->actingAs($doktor, 'doktor')
            ->get(route('hekim.hizmetler.index'));

        $response->assertStatus(200);
        $response->assertSee('Henüz Hizmet Eklemediniz');

        // 2. View create form
        $response = $this->actingAs($doktor, 'doktor')
            ->get(route('hekim.hizmetler.create'));
        $response->assertStatus(200);

        // 3. Create service
        $response = $this->actingAs($doktor, 'doktor')
            ->post(route('hekim.hizmetler.store'), [
                'ad' => 'Kardiyoloji Muayenesi',
                'aciklama' => 'Detaylı kalp taraması ve rutin muayene.',
                'sure' => 30,
                'fiyat' => 1500.00,
                'aktif_mi' => '1',
                'meta_baslik' => 'Muayene Meta',
                'meta_aciklama' => 'Muayene Açıklama',
                'meta_anahtar_kelimeler' => 'muayene, kalp',
            ]);

        $response->assertRedirect(route('hekim.hizmetler.index'));
        $this->assertDatabaseHas('hizmetler', [
            'doktor_id' => $doktor->id,
            'ad' => 'Kardiyoloji Muayenesi',
            'sure' => 30,
            'fiyat' => '1500.00',
            'aktif_mi' => true,
        ]);

        $hizmet = Hizmet::where('ad', 'Kardiyoloji Muayenesi')->first();
        $this->assertNotNull($hizmet);
        $this->assertNotNull($hizmet->slug);

        // 4. View edit form
        $response = $this->actingAs($doktor, 'doktor')
            ->get(route('hekim.hizmetler.edit', $hizmet->id));
        $response->assertStatus(200);
        $response->assertSee('Kardiyoloji Muayenesi');

        // 5. Update service
        $response = $this->actingAs($doktor, 'doktor')
            ->put(route('hekim.hizmetler.update', $hizmet->id), [
                'ad' => 'Kardiyoloji Muayenesi Guncel',
                'aciklama' => 'Guncellenmis muayene.',
                'sure' => 45,
                'fiyat' => 1800.00,
                'aktif_mi' => '1',
                'meta_baslik' => 'Muayene Guncel Meta',
                'meta_aciklama' => 'Muayene Guncel Aciklama',
                'meta_anahtar_kelimeler' => 'guncel, kalp',
            ]);

        $response->assertRedirect(route('hekim.hizmetler.index'));
        $this->assertDatabaseHas('hizmetler', [
            'id' => $hizmet->id,
            'ad' => 'Kardiyoloji Muayenesi Guncel',
            'sure' => 45,
            'fiyat' => '1800.00',
        ]);

        // 6. Delete service
        $response = $this->actingAs($doktor, 'doktor')
            ->delete(route('hekim.hizmetler.destroy', $hizmet->id));

        $response->assertRedirect(route('hekim.hizmetler.index'));
        $this->assertSoftDeleted('hizmetler', [
            'id' => $hizmet->id,
        ]);
    }

    /**
     * Test doctor cannot manage another doctor's service.
     */
    public function test_hekim_cannot_manage_another_doctors_service(): void
    {
        $doktor1 = $this->createDoktor('doktor1@test.com');
        $doktor2 = $this->createDoktor('doktor2@test.com');

        $hizmetOfDoktor2 = Hizmet::create([
            'doktor_id' => $doktor2->id,
            'ad' => 'Bilinmeyen Hizmet',
            'slug' => 'bilinmeyen-hizmet',
            'aciklama' => 'Aciklama',
            'sure' => 30,
            'fiyat' => 500,
            'aktif_mi' => true,
        ]);

        // Trying to edit
        $response = $this->actingAs($doktor1, 'doktor')
            ->get(route('hekim.hizmetler.edit', $hizmetOfDoktor2->id));
        $response->assertStatus(404);

        // Trying to update
        $response = $this->actingAs($doktor1, 'doktor')
            ->put(route('hekim.hizmetler.update', $hizmetOfDoktor2->id), [
                'ad' => 'Hack',
                'sure' => 30,
            ]);
        $response->assertStatus(404);

        // Trying to delete
        $response = $this->actingAs($doktor1, 'doktor')
            ->delete(route('hekim.hizmetler.destroy', $hizmetOfDoktor2->id));
        $response->assertStatus(404);
    }

    /**
     * Test public service detail page loads successfully.
     */
    public function test_public_service_detail_page_loads_successfully(): void
    {
        $il = Il::create([
            'ad' => 'Istanbul',
            'plaka' => '34',
        ]);

        $ilce = Ilce::create([
            'il_id' => $il->id,
            'ad' => 'Sisli',
        ]);

        $brans = Brans::create([
            'ad' => 'Kardiyoloji',
        ]);

        $doktor = Doktor::create([
            'ad_soyad' => 'Test Hekim',
            'e_posta' => 'public-service@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '05551234567',
            'tur' => 'bireysel',
            'unvan' => 'Prof. Dr.',
            'uzmanlik_alani' => 'Kardiyoloji',
            'il_id' => $il->id,
            'ilce_id' => $ilce->id,
            'aktif_mi' => true,
        ]);

        $doktor->branslar()->attach($brans->id);

        $hizmet = Hizmet::create([
            'doktor_id' => $doktor->id,
            'ad' => 'Kardiyoloji Muayenesi',
            'slug' => 'kardiyoloji-muayenesi',
            'aciklama' => 'Detayli kalp taramasi ve rutin muayene.',
            'sure' => 30,
            'fiyat' => 1500.00,
            'aktif_mi' => true,
        ]);

        // Access via public URL helper
        $this->assertEquals(
            route('frontend.hekim.hizmet.detay', [
                'il_slug' => 'istanbul',
                'ilce_slug' => 'sisli',
                'brans_slug' => 'kardiyoloji',
                'doctor_slug' => $doktor->slug,
                'hizmet_slug' => 'kardiyoloji-muayenesi',
            ]),
            $hizmet->url
        );

        $response = $this->get($hizmet->url);
        $response->assertStatus(200);
        $response->assertSee('Kardiyoloji Muayenesi');
        $response->assertSee('30 Dakika Süre');
        $response->assertSee('Test Hekim');
        $response->assertDontSee('1500.00'); // Ensure price is never visible on public pages
    }
}
