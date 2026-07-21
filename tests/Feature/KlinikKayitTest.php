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

        // Ücretsiz klinik paketi — PayTR merchant olmadan aktivasyon test edilebilir
        $this->paket = Paket::create([
            'ad' => 'Klinik Test Ücretsiz',
            'tur' => 'klinik',
            'aciklama' => 'Test klinik paketi',
            'aylik_fiyat' => 0,
            'yillik_fiyat' => 0,
            'aylik_indirimli_fiyat' => 0,
            'yillik_indirimli_fiyat' => 0,
            'ozellikler' => ['Ortak hasta havuzu'],
            'max_doktor_sayisi' => 10,
            'max_personel_sayisi' => 5,
            'aktif_mi' => true,
        ]);
    }

    public function test_klinik_kayit_formu_redirects_to_hekim_kayit(): void
    {
        $response = $this->get('/hekim/klinik/kayit-ol');

        $response->assertRedirect(route('frontend.hekim.kayit'));
    }

    /**
     * Girişli hekim klinik paketi (ücretsiz) ile klinik kurabilir — kart/iyzico yok, PayTR only ürün.
     */
    public function test_doctor_registration_and_post_payment_clinic_setup(): void
    {
        $doktor = Doktor::create([
            'ad_soyad' => 'Ahmet Tabip',
            'e_posta' => 'ahmet@sifa.com',
            'sifre' => bcrypt('Sifre123!'),
            'telefon' => '0 (555) 123 45 67',
            'il_id' => $this->il->id,
            'ilce_id' => $this->ilce->id,
            'unvan' => 'Uzm. Dr.',
            'tur' => 'bireysel',
            'aktif_mi' => true,
            'meslek_dogrulama_durumu' => 'onaylandi',
            'uyelik_bitis' => now()->addMonth(),
        ]);

        $response = $this->actingAs($doktor, 'doktor')
            ->post(route('frontend.hekim.paket_ode.post'), [
                'paket_id' => $this->paket->id,
                'odeme_periyodu' => 'aylik',
                'klinik_adi' => 'Şifa Polikliniği',
                'telefon' => '0 (224) 123 45 67',
                'e_posta' => 'info@sifa.com',
                'adres' => 'Fatih Mh. Sanayi Cd. No:44',
                'il_id' => $this->il->id,
                'ilce_id' => $this->ilce->ad,
                'mesafeli_onay' => '1',
                'kvkk_odeme_onay' => '1',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('klinikler', [
            'ad' => 'Şifa Polikliniği',
            'telefon' => '0 (224) 123 45 67',
            'e_posta' => 'info@sifa.com',
            'paket_id' => $this->paket->id,
            'il_id' => $this->il->id,
        ]);

        $doktor->refresh();
        $this->assertEquals($this->paket->id, $doktor->paket_id);
        $this->assertEquals('sahip', $doktor->klinik_rolu);
        $this->assertTrue((bool) $doktor->klinik_aktif_mi);
        $this->assertEquals('klinik', $doktor->tur);
        $this->assertNotNull($doktor->klinik_id);
    }

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

        $response = $this->actingAs($doktor, 'doktor')
            ->get(route('frontend.hekim.klinik.gecis'));

        $response->assertStatus(200);

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
            ]);

        $response->assertRedirect();

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
