<?php

namespace Tests\Feature;

use App\Models\Brans;
use App\Models\Doktor;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Paket;
use App\Models\Unvan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private Paket $paket;
    private Brans $brans;
    private Unvan $unvan;
    private Il $il;
    private Ilce $ilce;

    protected function setUp(): void
    {
        parent::setUp();

        $this->il = Il::create(['ad' => 'Bursa', 'plaka' => '16']);
        $this->ilce = Ilce::create(['il_id' => $this->il->id, 'ad' => 'Nilufer']);
        $this->brans = Brans::create(['ad' => 'Fizyoterapi']);
        $this->unvan = Unvan::create(['ad' => 'Uzm. Dr.']);

        $this->paket = Paket::create([
            'ad' => 'Bireysel Standart',
            'tur' => 'bireysel',
            'aciklama' => 'Standart bireysel hekim paketi',
            'aylik_fiyat' => 299.00,
            'yillik_fiyat' => 2999.00,
            'ozellikler' => ['randevu_limit' => 100],
            'aktif_mi' => true,
            'iyzico_plan_aylik' => 'plan-bireysel-aylik',
            'iyzico_plan_yillik' => 'plan-bireysel-yillik',
        ]);
    }

    /**
     * Test doctor can register successfully without a package (package selected post-registration).
     */
    public function test_doctor_can_register_successfully_without_package(): void
    {
        $response = $this->post(route('frontend.hekim.kayit.post'), [
            'ad_soyad' => 'Hasan Hekim',
            'e_posta' => 'hasan@hekim.com',
            'sifre' => 'Sifre123!',
            'sifre_confirmation' => 'Sifre123!',
            'telefon' => '0 (555) 123 45 67',
            'unvan' => 'Uzm. Dr.',
            'il' => 'Bursa',
            'ilce' => 'Nilufer',
            'branslar' => [$this->brans->id],
        ]);

        $response->assertRedirect(route('frontend.hekim.paket_sec'));

        $this->assertDatabaseHas('doktorlar', [
            'ad_soyad' => 'Hasan Hekim',
            'e_posta' => 'hasan@hekim.com',
            'paket_id' => null,
            'uyelik_bitis' => null,
        ]);

        $doktor = Doktor::where('e_posta', 'hasan@hekim.com')->first();
        $this->assertNotNull($doktor);
        $this->assertTrue(auth('doktor')->check());
        $this->assertEquals(auth('doktor')->id(), $doktor->id);
    }

    /**
     * Test doctor registration fails if validation is incorrect.
     */
    public function test_doctor_registration_fails_due_to_validation(): void
    {
        $response = $this->post(route('frontend.hekim.kayit.post'), [
            'ad_soyad' => 'Hasan Hekim',
            'e_posta' => 'invalid-email',
            'sifre' => 'short',
            'sifre_confirmation' => 'mismatch',
            'telefon' => '12345',
            'unvan' => 'Uzm. Dr.',
            'il' => 'Bursa',
            'ilce' => 'Nilufer',
            'branslar' => [],
        ]);

        $response->assertSessionHasErrors(['e_posta', 'sifre', 'telefon', 'branslar']);
        $this->assertDatabaseCount('doktorlar', 0);
    }
}
