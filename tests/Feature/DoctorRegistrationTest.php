<?php

namespace Tests\Feature;

use App\Models\Brans;
use App\Models\Doktor;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Paket;
use App\Models\Unvan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
     * Kayıt: paket seçilmiş şekilde, meslek belgesi manuel yüklenerek.
     * Sonrası: doktor beklemede oluşturulur, paket_id null (kayit_paket_id dolu), meslek belgesi bekleme ekranına yönlendirilir.
     */
    public function test_doctor_can_register_successfully_with_package_pending_review(): void
    {
        Storage::fake('local');

        $response = $this->post(route('frontend.hekim.kayit.post'), [
            'ad_soyad' => 'Hasan Hekim',
            'e_posta' => 'hasan@hekim.com',
            'sifre' => 'Sifre123!',
            'sifre_confirmation' => 'Sifre123!',
            'telefon' => '0 (555) 123 45 67',
            'tc_kimlik_no' => '12345678950',
            'diploma_no' => 'D-2024-000123',
            'meslek_belgesi' => UploadedFile::fake()->create('diploma.pdf', 120, 'application/pdf'),
            'unvan' => 'Uzm. Dr.',
            'il' => 'Bursa',
            'ilce' => 'Nilufer',
            'branslar' => [$this->brans->id],
            'kvkk_onay' => '1',
            'sozlesme_onay' => '1',
            'paket_id' => $this->paket->id,
            'odeme_periyodu' => 'aylik',
        ]);

        $response->assertRedirect(route('frontend.hekim.meslek.bekleme'));

        $this->assertDatabaseHas('doktorlar', [
            'ad_soyad' => 'Hasan Hekim',
            'e_posta' => 'hasan@hekim.com',
            'paket_id' => null,
            'kayit_paket_id' => $this->paket->id,
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
