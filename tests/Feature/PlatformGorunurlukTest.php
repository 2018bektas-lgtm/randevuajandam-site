<?php

namespace Tests\Feature;

use App\Models\Brans;
use App\Models\Doktor;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Paket;
use App\Models\PaketOzelligi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PlatformGorunurlukTest extends TestCase
{
    use RefreshDatabase;

    private Doktor $doktor;

    private string $profilPath;

    protected function setUp(): void
    {
        parent::setUp();

        $il = Il::create(['ad' => 'Bursa', 'plaka' => '16', 'slug' => 'bursa']);
        $ilce = Ilce::create(['il_id' => $il->id, 'ad' => 'Nilufer', 'slug' => 'nilufer']);
        $brans = Brans::create(['ad' => 'Dermatoloji', 'slug' => 'dermatoloji']);

        $ozellik = PaketOzelligi::create([
            'kod' => 'web_sitesi',
            'ad' => 'Web Sitesi',
            'aciklama' => 'Test',
        ]);

        $paket = Paket::create([
            'ad' => 'Web Test Paket',
            'tur' => 'bireysel',
            'aciklama' => 'Test',
            'aylik_fiyat' => 100,
            'yillik_fiyat' => 1000,
            'aktif_mi' => true,
            'ozellikler' => [],
        ]);
        $paket->sistemOzellikleri()->sync([$ozellik->id]);

        $this->doktor = Doktor::create([
            'ad_soyad' => 'Gizli Hekim',
            'slug' => 'gizli-hekim',
            'e_posta' => 'gizli-hekim@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '05551112233',
            'tur' => 'bireysel',
            'unvan' => 'Uzm. Dr.',
            'il_id' => $il->id,
            'ilce_id' => $ilce->id,
            'paket_id' => $paket->id,
            'uyelik_baslangic' => now(),
            'uyelik_bitis' => now()->addMonth(),
            'meslek_dogrulama_durumu' => 'onaylandi',
            'aktif_mi' => true,
            'platformda_gorunur' => true,
        ]);
        $this->doktor->branslar()->attach($brans->id);

        $this->profilPath = '/bursa/nilufer/dermatoloji/gizli-hekim';
    }

    public function test_listed_doctor_appears_in_directory(): void
    {
        $response = $this->get(route('frontend.hekimler'));
        $response->assertStatus(200);
        $response->assertSee('Gizli Hekim');
    }

    public function test_hidden_doctor_not_in_directory(): void
    {
        $this->doktor->update(['platformda_gorunur' => false]);

        $response = $this->get(route('frontend.hekimler'));
        $response->assertStatus(200);
        $response->assertDontSee('Gizli Hekim');
    }

    public function test_hidden_doctor_profile_returns_404(): void
    {
        $this->doktor->update(['platformda_gorunur' => false]);

        $response = $this->get($this->profilPath);
        $response->assertStatus(404);
    }

    public function test_visible_doctor_profile_ok(): void
    {
        $response = $this->get($this->profilPath);
        $response->assertStatus(200);
        $response->assertSee('Gizli Hekim');
    }

    public function test_doctor_can_toggle_visibility_with_web_package(): void
    {
        $response = $this->actingAs($this->doktor, 'doktor')
            ->post(route('hekim.web-sitesi.platform-gorunurluk'), [
                // checkbox absent = false
            ]);

        $response->assertRedirect();
        $this->assertFalse((bool) $this->doktor->fresh()->platformda_gorunur);

        $response = $this->actingAs($this->doktor, 'doktor')
            ->post(route('hekim.web-sitesi.platform-gorunurluk'), [
                'platformda_gorunur' => '1',
            ]);

        $response->assertRedirect();
        $this->assertTrue((bool) $this->doktor->fresh()->platformda_gorunur);
    }

    public function test_is_listed_respects_platformda_gorunur_flag(): void
    {
        $this->doktor->update(['platformda_gorunur' => false]);
        $this->assertFalse($this->doktor->fresh()->isListedOnPlatform());

        $this->doktor->update(['platformda_gorunur' => true]);
        $this->assertTrue($this->doktor->fresh()->isListedOnPlatform());
    }

    public function test_unpaid_doctor_without_package_not_listed(): void
    {
        $this->doktor->update([
            'paket_id' => null,
            'uyelik_bitis' => null,
            'platformda_gorunur' => true,
        ]);

        $this->assertFalse($this->doktor->fresh()->isListedOnPlatform());

        $response = $this->get(route('frontend.hekimler'));
        $response->assertStatus(200);
        $response->assertDontSee('Gizli Hekim');
    }

    public function test_expired_membership_not_listed(): void
    {
        $this->doktor->update([
            'uyelik_bitis' => now()->subDay(),
            'platformda_gorunur' => true,
        ]);

        $this->assertFalse($this->doktor->fresh()->isListedOnPlatform());
    }
}
