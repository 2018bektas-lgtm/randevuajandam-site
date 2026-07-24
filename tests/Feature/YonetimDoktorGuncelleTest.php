<?php

namespace Tests\Feature;

use App\Models\Doktor;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Klinik;
use App\Models\Paket;
use App\Models\Yonetici;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class YonetimDoktorGuncelleTest extends TestCase
{
    use RefreshDatabase;

    private Yonetici $yonetici;

    private Doktor $doktor;

    private Il $il;

    private Ilce $ilce;

    private Paket $bireyselPaket;

    private Paket $klinikPaket;

    protected function setUp(): void
    {
        parent::setUp();

        $this->il = Il::create(['ad' => 'Bursa', 'plaka' => '16']);
        $this->ilce = Ilce::create(['il_id' => $this->il->id, 'ad' => 'Nilufer']);

        $this->yonetici = Yonetici::create([
            'ad_soyad' => 'Test Yonetici',
            'e_posta' => 'admin-doktor@test.com',
            'sifre' => Hash::make('sifre123'),
            'aktif_mi' => true,
        ]);

        $this->bireyselPaket = Paket::create([
            'ad' => 'Bireysel Web',
            'tur' => 'bireysel',
            'aylik_fiyat' => 100,
            'yillik_fiyat' => 1000,
            'aktif_mi' => true,
        ]);

        $this->klinikPaket = Paket::create([
            'ad' => 'Klinik Plus',
            'tur' => 'klinik',
            'aylik_fiyat' => 500,
            'yillik_fiyat' => 5000,
            'max_doktor_sayisi' => 10,
            'aktif_mi' => true,
        ]);

        $this->doktor = Doktor::create([
            'ad_soyad' => 'Hasan Hekim',
            'e_posta' => 'hasan-yonetim@test.com',
            'sifre' => Hash::make('sifre123'),
            'il_id' => $this->il->id,
            'ilce_id' => $this->ilce->id,
            'tur' => 'klinik',
            'paket_id' => $this->klinikPaket->id,
            'aktif_mi' => true,
            'platformda_gorunur' => true,
        ]);
    }

    public function test_edit_form_shows_klinik_fields(): void
    {
        $response = $this->actingAs($this->yonetici, 'yonetici')
            ->get(route('yonetim.doktorlar.duzenle', $this->doktor->id));

        $response->assertOk();
        $response->assertSee('Klinik Bağlantısı ve Yetkiler', false);
        $response->assertSee('name="klinik_id"', false);
        $response->assertSee('name="klinik_rolu"', false);
    }

    public function test_admin_can_attach_doctor_to_clinic_with_permissions(): void
    {
        $klinik = Klinik::create([
            'ad' => 'Test Klinik',
            'sahip_doktor_id' => $this->doktor->id,
            'paket_id' => $this->klinikPaket->id,
            'il_id' => $this->il->id,
            'ilce_id' => $this->ilce->id,
            'aktif_mi' => true,
            'max_doktor_sayisi' => 5,
        ]);

        $response = $this->actingAs($this->yonetici, 'yonetici')
            ->post(route('yonetim.doktorlar.update', $this->doktor->id), [
                'ad_soyad' => 'Hasan Hekim',
                'e_posta' => 'hasan-yonetim@test.com',
                'tur' => 'klinik',
                'paket_id' => $this->klinikPaket->id,
                'aktif_mi' => '1',
                'platformda_gorunur' => '1',
                'klinik_id' => $klinik->id,
                'klinik_rolu' => 'doktor',
                'klinik_aktif_mi' => '1',
                'komisyon_orani' => '15',
                'klinik_yetkileri' => [
                    'yonetim_paneli' => '1',
                    'hekim_yonetimi' => '1',
                ],
            ]);

        $response->assertRedirect(route('yonetim.doktorlar.duzenle', $this->doktor->id));

        $this->doktor->refresh();
        $this->assertEquals($klinik->id, $this->doktor->klinik_id);
        $this->assertEquals('doktor', $this->doktor->klinik_rolu);
        $this->assertTrue((bool) $this->doktor->klinik_aktif_mi);
        $this->assertEquals(15.0, (float) $this->doktor->komisyon_orani);
        $this->assertTrue($this->doktor->hasClinicPermission('yonetim_paneli'));
        $this->assertTrue($this->doktor->hasClinicPermission('hekim_yonetimi'));
        $this->assertFalse($this->doktor->hasClinicPermission('finans_yonetimi'));
    }

    public function test_admin_can_detach_doctor_from_clinic_and_switch_to_bireysel(): void
    {
        $klinik = Klinik::create([
            'ad' => 'Test Klinik 2',
            'sahip_doktor_id' => $this->doktor->id,
            'paket_id' => $this->klinikPaket->id,
            'il_id' => $this->il->id,
            'ilce_id' => $this->ilce->id,
            'aktif_mi' => true,
            'max_doktor_sayisi' => 5,
        ]);

        $this->doktor->update([
            'klinik_id' => $klinik->id,
            'klinik_rolu' => 'sahip',
            'klinik_aktif_mi' => true,
            'klinik_yetkileri' => [
                'yonetim_paneli' => true,
                'hekim_yonetimi' => true,
            ],
            'komisyon_orani' => 10,
        ]);

        $response = $this->actingAs($this->yonetici, 'yonetici')
            ->post(route('yonetim.doktorlar.update', $this->doktor->id), [
                'ad_soyad' => 'Hasan Hekim',
                'e_posta' => 'hasan-yonetim@test.com',
                'tur' => 'bireysel',
                'paket_id' => $this->bireyselPaket->id,
                'aktif_mi' => '1',
                'platformda_gorunur' => '1',
                'klinik_id' => '',
                'klinik_rolu' => '',
            ]);

        $response->assertRedirect(route('yonetim.doktorlar.duzenle', $this->doktor->id));

        $this->doktor->refresh();
        $this->assertNull($this->doktor->klinik_id);
        $this->assertNull($this->doktor->klinik_rolu);
        $this->assertNull($this->doktor->klinik_yetkileri);
        $this->assertNull($this->doktor->klinik_aktif_mi);
        $this->assertEquals(0.0, (float) $this->doktor->komisyon_orani);
        $this->assertEquals('bireysel', $this->doktor->tur);
        $this->assertEquals($this->bireyselPaket->id, $this->doktor->paket_id);
        $this->assertFalse($this->doktor->hasClinicPermission('yonetim_paneli'));

        // sahip_doktor_id NOT NULL; başka üye yoksa eski referans kalabilir
        $klinik->refresh();
        $this->assertNotNull($klinik->sahip_doktor_id);
    }

    public function test_sahip_role_gets_full_permissions_and_updates_clinic_owner(): void
    {
        $diger = Doktor::create([
            'ad_soyad' => 'Eski Sahip',
            'e_posta' => 'eski-sahip@test.com',
            'sifre' => Hash::make('sifre123'),
            'il_id' => $this->il->id,
            'ilce_id' => $this->ilce->id,
            'tur' => 'klinik',
            'aktif_mi' => true,
        ]);

        $klinik = Klinik::create([
            'ad' => 'Sahiplik Klinigi',
            'sahip_doktor_id' => $diger->id,
            'paket_id' => $this->klinikPaket->id,
            'il_id' => $this->il->id,
            'ilce_id' => $this->ilce->id,
            'aktif_mi' => true,
            'max_doktor_sayisi' => 5,
        ]);

        $response = $this->actingAs($this->yonetici, 'yonetici')
            ->post(route('yonetim.doktorlar.update', $this->doktor->id), [
                'ad_soyad' => 'Hasan Hekim',
                'e_posta' => 'hasan-yonetim@test.com',
                'tur' => 'klinik',
                'paket_id' => $this->klinikPaket->id,
                'aktif_mi' => '1',
                'klinik_id' => $klinik->id,
                'klinik_rolu' => 'sahip',
                'klinik_aktif_mi' => '1',
                'komisyon_orani' => '0',
            ]);

        $response->assertRedirect();

        $this->doktor->refresh();
        $this->assertEquals('sahip', $this->doktor->klinik_rolu);
        $this->assertTrue($this->doktor->hasClinicPermission('finans_yonetimi'));
        $this->assertTrue($this->doktor->hasClinicPermission('hakedis_yonetimi'));

        $klinik->refresh();
        $this->assertEquals($this->doktor->id, $klinik->sahip_doktor_id);
    }
}
