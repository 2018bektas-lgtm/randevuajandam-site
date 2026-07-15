<?php

namespace Tests\Feature;

use App\Models\Brans;
use App\Models\Doktor;
use App\Models\DoktorGaleri;
use App\Models\Il;
use App\Models\Ilce;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HekimGaleriTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test doctor can manage their photo gallery.
     */
    public function test_doctor_can_manage_gallery(): void
    {
        Storage::fake('public');

        $il = Il::create(['ad' => 'Bursa', 'plaka' => '16']);
        $ilce = Ilce::create(['il_id' => $il->id, 'ad' => 'Nilufer']);
        $brans = Brans::create(['ad' => 'Fizyoterapi']);

        $doktor = Doktor::create([
            'ad_soyad' => 'Hasan Hekim',
            'e_posta' => 'hasan@test.com',
            'sifre' => Hash::make('sifre123'),
            'il_id' => $il->id,
            'ilce_id' => $ilce->id,
            'tur' => 'bireysel',
            'aktif_mi' => true,
            'uzmanlik_alani' => 'Fizyoterapi',
        ]);
        $doktor->branslar()->attach($brans->id);

        // 1. View empty gallery list
        $response = $this->actingAs($doktor, 'doktor')
            ->get(route('hekim.galeriler.index'));

        $response->assertStatus(200);
        $response->assertSee('Galeriniz Henüz Boş');

        // 2. Upload photos
        $file1 = UploadedFile::fake()->image('clinic_front.jpg');
        $file2 = UploadedFile::fake()->image('clinic_room.jpg');

        $response = $this->actingAs($doktor, 'doktor')
            ->post(route('hekim.galeriler.store'), [
                'resimler' => [$file1, $file2],
                'basliklar' => ['Klinik Giriş', 'Muayene Odası'],
            ]);

        $response->assertRedirect(route('hekim.galeriler.index'));
        
        $this->assertDatabaseHas('doktor_galerileri', [
            'doktor_id' => $doktor->id,
            'baslik' => 'Klinik Giriş',
            'sira' => 1,
        ]);

        $this->assertDatabaseHas('doktor_galerileri', [
            'doktor_id' => $doktor->id,
            'baslik' => 'Muayene Odası',
            'sira' => 2,
        ]);

        $galeri1 = DoktorGaleri::where('baslik', 'Klinik Giriş')->first();
        $galeri2 = DoktorGaleri::where('baslik', 'Muayene Odası')->first();

        // 3. View public detail profile showing gallery
        $response = $this->get($doktor->profil_url);
        $response->assertStatus(200);
        $response->assertSee('Klinik / Muayenehane Fotoğrafları');
        $response->assertSee('Klinik Giriş');
        $response->assertSee('Muayene Odası');

        // 4. Update photo description
        $response = $this->actingAs($doktor, 'doktor')
            ->post(route('hekim.galeriler.update', $galeri1->id), [
                'baslik' => 'Yeni Giriş Açıklaması',
            ]);

        $response->assertRedirect(route('hekim.galeriler.index'));
        $this->assertDatabaseHas('doktor_galerileri', [
            'id' => $galeri1->id,
            'baslik' => 'Yeni Giriş Açıklaması',
        ]);

        // 5. Sort photos
        $response = $this->actingAs($doktor, 'doktor')
            ->post(route('hekim.galeriler.sirala'), [
                'ids' => [$galeri2->id, $galeri1->id]
            ]);

        $response->assertJson(['success' => true]);
        
        // Assert sorting updated sira
        $this->assertEquals(0, $galeri2->fresh()->sira);
        $this->assertEquals(1, $galeri1->fresh()->sira);

        // 6. Delete photo
        $response = $this->actingAs($doktor, 'doktor')
            ->delete(route('hekim.galeriler.destroy', $galeri1->id));

        $response->assertRedirect(route('hekim.galeriler.index'));
        $this->assertDatabaseMissing('doktor_galerileri', [
            'id' => $galeri1->id,
        ]);
    }

    /**
     * Test doctor cannot manage other doctors' gallery images.
     */
    public function test_doctor_cannot_manage_other_doctors_gallery(): void
    {
        $doktor1 = Doktor::create([
            'ad_soyad' => 'Hasan Hekim 1',
            'e_posta' => 'hasan1@test.com',
            'sifre' => Hash::make('sifre123'),
            'tur' => 'bireysel',
            'aktif_mi' => true,
        ]);

        $doktor2 = Doktor::create([
            'ad_soyad' => 'Hasan Hekim 2',
            'e_posta' => 'hasan2@test.com',
            'sifre' => Hash::make('sifre123'),
            'tur' => 'bireysel',
            'aktif_mi' => true,
        ]);

        $galeriOfDoktor2 = DoktorGaleri::create([
            'doktor_id' => $doktor2->id,
            'resim_yolu' => 'uploads/galeri/test.jpg',
            'baslik' => 'Doktor 2 Odası',
            'sira' => 1,
        ]);

        // Doktor 1 tries to update Doktor 2's photo description
        $response = $this->actingAs($doktor1, 'doktor')
            ->post(route('hekim.galeriler.update', $galeriOfDoktor2->id), [
                'baslik' => 'Hacklendi',
            ]);

        $response->assertStatus(404);

        // Doktor 1 tries to delete Doktor 2's photo
        $response = $this->actingAs($doktor1, 'doktor')
            ->delete(route('hekim.galeriler.destroy', $galeriOfDoktor2->id));

        $response->assertStatus(404);
    }
}
