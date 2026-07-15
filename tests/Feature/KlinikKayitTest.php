<?php

namespace Tests\Feature;

use App\Models\Brans;
use App\Models\Doktor;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Klinik;
use App\Models\Paket;
use App\Models\Unvan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KlinikKayitTest extends TestCase
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
            'ad' => 'Klinik Profesyonel',
            'tur' => 'klinik',
            'aciklama' => 'Klinikler için profesyonel paket',
            'aylik_fiyat' => 999.00,
            'yillik_fiyat' => 9990.00,
            'ozellikler' => ['Merkezi finans', 'Ortak hasta havuzu'],
            'max_doktor_sayisi' => 10,
            'max_personel_sayisi' => 5,
            'aktif_mi' => true,
            'iyzico_plan_aylik' => 'plan-klinik-prof-aylik',
            'iyzico_plan_yillik' => 'plan-klinik-prof-yillik',
        ]);
    }

    /**
     * Test guest clinic registration route redirects to guest doctor registration.
     */
    public function test_klinik_kayit_formu_redirects_to_hekim_kayit(): void
    {
        $response = $this->get('/hekim/klinik/kayit-ol');

        $response->assertRedirect(route('frontend.hekim.kayit'));
    }

    /**
     * Test doctor registration first, then selecting and paying for a clinic package.
     */
    public function test_doctor_registration_and_post_payment_clinic_setup(): void
    {
        // Gerçek Iyzico sandbox/mock olmadan ödeme adımı "Sistem hatası" döner
        if (! filter_var(env('IYZICO_ALLOW_MOCK', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->markTestSkipped('Klinik paket ödemesi Iyzico sandbox/mock gerektirir (IYZICO_ALLOW_MOCK=true).');
        }

        // 1. Doctor registers as guest
        $response = $this->post(route('frontend.hekim.kayit.post'), [
            'ad_soyad' => 'Ahmet Tabip',
            'e_posta' => 'ahmet@sifa.com',
            'sifre' => 'Sifre123!',
            'sifre_confirmation' => 'Sifre123!',
            'telefon' => '0 (555) 123 45 67',
            'unvan' => 'Uzm. Dr.',
            'il' => 'Bursa',
            'ilce' => 'Nilufer',
            'branslar' => [$this->brans->id],
        ]);

        // Should redirect to package selection
        $response->assertRedirect(route('frontend.hekim.paket_sec'));

        $doktor = Doktor::where('e_posta', 'ahmet@sifa.com')->first();
        $this->assertNotNull($doktor);
        $this->assertNull($doktor->paket_id);
        $this->assertTrue(auth('doktor')->check());

        // 2. Doctor accesses package selection page
        $response = $this->actingAs($doktor, 'doktor')
            ->get(route('frontend.hekim.paket_sec'));
        $response->assertStatus(200);

        // 3. Doctor accesses checkout page for clinic package
        $response = $this->actingAs($doktor, 'doktor')
            ->get(route('frontend.hekim.paket_ode', [
                'paket' => $this->paket->id,
                'periyot' => 'aylik'
            ]));
        $response->assertStatus(200);

        // 4. Doctor submits checkout with Clinic Details
        $response = $this->actingAs($doktor, 'doktor')
            ->post(route('frontend.hekim.paket_ode.post'), [
                // General
                'paket_id' => $this->paket->id,
                'odeme_periyodu' => 'aylik',

                // Clinic Details
                'klinik_adi' => 'Şifa Polikliniği',
                'telefon' => '0 (224) 123 45 67',
                'e_posta' => 'info@sifa.com',
                'adres' => 'Fatih Mh. Sanayi Cd. No:44',
                'il_id' => $this->il->id,
                'ilce_id' => $this->ilce->ad,

                // Card Details
                'kart_sahibi' => 'Ahmet Tabip',
                'kart_no' => '5430000000000000',
                'kart_skt' => '12/29',
                'kart_cvv' => '123',
            ]);

        $response->assertRedirect(route('frontend.hekim.basarili'));

        // Verify Clinic database entry
        $this->assertDatabaseHas('klinikler', [
            'ad' => 'Şifa Polikliniği',
            'telefon' => '0 (224) 123 45 67',
            'e_posta' => 'info@sifa.com',
            'paket_id' => $this->paket->id,
            'il_id' => $this->il->id,
        ]);

        // Verify Doctor database entry
        $doktor->refresh();
        $this->assertEquals($this->paket->id, $doktor->paket_id);
        $this->assertEquals('sahip', $doktor->klinik_rolu);
        $this->assertTrue($doktor->klinik_aktif_mi);
        $this->assertEquals('klinik', $doktor->tur);
        $this->assertNotNull($doktor->klinik_id);
    }

    /**
     * Test existing individual doctor can transition (upgrade) to a clinic.
     */
    public function test_individual_doctor_can_transition_to_clinic_successfully(): void
    {
        $bireyselPaket = Paket::create([
            'ad' => 'Bireysel Başlangıç',
            'tur' => 'bireysel',
            'aciklama' => 'Bireysel başlangıç paketi',
            'aylik_fiyat' => 199.00,
            'yillik_fiyat' => 1990.00,
            'ozellikler' => ['Randevu Yönetimi'],
            'aktif_mi' => true,
        ]);

        $doktor = Doktor::create([
            'ad_soyad' => 'Bireysel Doktor',
            'e_posta' => 'bireysel@doktor.com',
            'sifre' => bcrypt('Password123!'),
            'telefon' => '0 (555) 123 45 67',
            'il_id' => $this->il->id,
            'ilce_id' => $this->ilce->id,
            'tur' => 'bireysel',
            'aktif_mi' => true,
            'paket_id' => $bireyselPaket->id,
            'uyelik_bitis' => now()->addMonth(),
        ]);

        // Access transition page
        $response = $this->actingAs($doktor, 'doktor')
            ->get(route('frontend.hekim.klinik.gecis'));

        $response->assertStatus(200);

        if (! filter_var(env('IYZICO_ALLOW_MOCK', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->markTestSkipped('Klinik geçiş ödemesi Iyzico sandbox/mock gerektirir (IYZICO_ALLOW_MOCK=true).');
        }

        // Post transition request
        $response = $this->actingAs($doktor, 'doktor')
            ->post(route('frontend.hekim.klinik.gecis.post'), [
                'klinik_adi' => 'Tabip Sağlık',
                'telefon' => '0 (224) 987 65 43',
                'e_posta' => 'info@tabip.com',
                'adres' => 'Yeni Sk. No:55',
                'il_id' => $this->il->id,
                'ilce_id' => $this->ilce->ad,
                'paket_id' => $this->paket->id,
                'odeme_periyodu' => 'aylik',
                'kart_sahibi' => 'Bireysel Doktor',
                'kart_no' => '5430000000000000',
                'kart_skt' => '12/29',
                'kart_cvv' => '123',
            ]);

        $response->assertRedirect(route('frontend.hekim.basarili'));

        // Check if database updated correctly
        $this->assertDatabaseHas('klinikler', [
            'ad' => 'Tabip Sağlık',
            'sahip_doktor_id' => $doktor->id,
            'paket_id' => $this->paket->id,
        ]);

        $klinik = Klinik::where('ad', 'Tabip Sağlık')->first();

        $this->assertDatabaseHas('doktorlar', [
            'id' => $doktor->id,
            'klinik_id' => $klinik->id,
            'klinik_rolu' => 'sahip',
            'klinik_aktif_mi' => true,
        ]);
    }
}
