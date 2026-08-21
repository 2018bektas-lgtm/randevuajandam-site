<?php

namespace Tests\Feature;

use App\Models\Brans;
use App\Models\Doktor;
use App\Models\Hasta;
use App\Models\Hizmet;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Paket;
use App\Models\PaketOzelligi;
use App\Models\Randevu;
use App\Models\RandevuAyari;
use App\Models\SiteAyari;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RandevuSistemiTest extends TestCase
{
    use RefreshDatabase;

    private Doktor $doktor;

    private Hizmet $hizmet;

    protected function setUp(): void
    {
        parent::setUp();

        SiteAyari::create([
            'meta_baslik' => 'Randevu Ajandam',
            'meta_aciklama' => 'Test Aciklama',
            'meta_anahtar_kelimeler' => 'test, randevu',
            'meta_yazar' => 'Test Yazar',
        ]);

        $il = Il::create(['ad' => 'Istanbul', 'plaka' => '34']);
        $ilce = Ilce::create(['il_id' => $il->id, 'ad' => 'Sisli']);
        $brans = Brans::create(['ad' => 'Kardiyoloji']);

        // Public profil sayfasının açılabilmesi için: aktif üyelik + profil_sayfasi özelliği + platformda_gorunur + meslek onayı
        $ozellik = PaketOzelligi::firstOrCreate(
            ['kod' => 'profil_sayfasi'],
            ['ad' => 'Profil Sayfası']
        );
        $paket = Paket::create([
            'ad' => 'Vitrin Test',
            'tur' => 'bireysel',
            'aciklama' => 'Test paketi',
            'aylik_fiyat' => 0,
            'yillik_fiyat' => 0,
            'ozellikler' => [],
            'aktif_mi' => true,
        ]);
        $paket->sistemOzellikleri()->sync([$ozellik->id]);

        $this->doktor = Doktor::create([
            'ad_soyad' => 'Test Hekim',
            'e_posta' => 'hekim-takvim@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '05551234567',
            'tur' => 'bireysel',
            'unvan' => 'Prof. Dr.',
            'uzmanlik_alani' => 'Kardiyoloji',
            'il_id' => $il->id,
            'ilce_id' => $ilce->id,
            'aktif_mi' => true,
            'paket_id' => $paket->id,
            'platformda_gorunur' => true,
            'meslek_dogrulama_durumu' => 'onaylandi',
        ]);

        $this->doktor->branslar()->attach($brans->id);

        for ($gun = 1; $gun <= 7; $gun++) {
            $this->doktor->calismaSaatleri()->create([
                'gun' => $gun,
                'aktif_mi' => $gun <= 5,
                'mesai_baslangic' => '09:00',
                'mesai_bitis' => '17:00',
            ]);
        }

        $this->hizmet = Hizmet::create([
            'doktor_id' => $this->doktor->id,
            'ad' => 'Kardiyoloji Muayenesi',
            'slug' => 'kardiyoloji-muayenesi',
            'aciklama' => 'Detayli muayene.',
            'sure' => 30,
            'fiyat' => 1000.00,
            'aktif_mi' => true,
        ]);
    }

    /**
     * Doktor profil sayfası: randevu açıkken misafir kullanıcı da randevu wizard'ını görebilir.
     */
    public function test_doctor_profile_displays_guest_prompt_to_unauthenticated_user(): void
    {
        RandevuAyari::create([
            'doktor_id' => $this->doktor->id,
            'aktif_mi' => true,
            'randevu_onay_tipi' => 'manuel',
        ]);

        $response = $this->get($this->doktor->profil_url);

        $response->assertStatus(200);
        $response->assertSee('Randevu al'); // wizard başlığı
        $response->assertSee('id="randevu-wizard"', false);
    }

    /**
     * Doktor profil sayfası: randevu açıkken giriş yapmış hasta da wizard'ı görür.
     */
    public function test_doctor_profile_displays_booking_form_when_patient_is_logged_in(): void
    {
        RandevuAyari::create([
            'doktor_id' => $this->doktor->id,
            'aktif_mi' => true,
            'randevu_onay_tipi' => 'manuel',
        ]);

        $hasta = Hasta::create([
            'ad' => 'Ahmet',
            'soyad' => 'Yilmaz',
            'e_posta' => 'ahmet@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '05559998877',
            'aktif_mi' => true,
        ]);

        $response = $this->actingAs($hasta, 'hasta')->get($this->doktor->profil_url);

        $response->assertStatus(200);
        $response->assertSee('Randevu al');
        $response->assertSee('id="randevu-wizard"', false);
    }

    /**
     * Doktor profil sayfası: randevu kapalıysa wizard render edilmez.
     */
    public function test_doctor_profile_displays_contact_info_when_appointments_are_disabled(): void
    {
        RandevuAyari::create([
            'doktor_id' => $this->doktor->id,
            'aktif_mi' => false,
            'randevu_onay_tipi' => 'manuel',
        ]);

        $response = $this->get($this->doktor->profil_url);

        $response->assertStatus(200);
        $response->assertDontSee('id="randevu-wizard"', false);
    }

    /**
     * Hizmet detay sayfası: randevu açık + misafir → wizard + "Hesap oluşturmadan" not.
     */
    public function test_service_detail_displays_guest_prompt_to_unauthenticated_user(): void
    {
        RandevuAyari::create([
            'doktor_id' => $this->doktor->id,
            'aktif_mi' => true,
            'randevu_onay_tipi' => 'manuel',
        ]);

        $response = $this->get($this->hizmet->url);

        $response->assertStatus(200);
        $response->assertSee('Hesap oluşturmadan randevu alabilirsiniz.');
        $response->assertSee('giriş yapın');
        $response->assertSee('üye olun');
        $response->assertDontSee('Hekimimiz online randevu alımına geçici olarak kapalıdır');
    }

    /**
     * Hizmet detay sayfası: randevu açık + hasta giriş yapmış → wizard render, misafir notu YOK.
     */
    public function test_service_detail_displays_booking_form_when_patient_is_logged_in(): void
    {
        RandevuAyari::create([
            'doktor_id' => $this->doktor->id,
            'aktif_mi' => true,
            'randevu_onay_tipi' => 'manuel',
        ]);

        $hasta = Hasta::create([
            'ad' => 'Ahmet',
            'soyad' => 'Yilmaz',
            'e_posta' => 'ahmet@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '05559998877',
            'aktif_mi' => true,
        ]);

        $response = $this->actingAs($hasta, 'hasta')->get($this->hizmet->url);

        $response->assertStatus(200);
        $response->assertSee('id="randevu-wizard"', false);
        $response->assertDontSee('Hesap oluşturmadan randevu alabilirsiniz.');
    }

    /**
     * Test service detail displays contact info when appointments are disabled.
     */
    public function test_service_detail_displays_contact_info_when_appointments_are_disabled(): void
    {
        RandevuAyari::create([
            'doktor_id' => $this->doktor->id,
            'aktif_mi' => false,
            'randevu_onay_tipi' => 'manuel',
        ]);

        $response = $this->get($this->hizmet->url);

        $response->assertStatus(200);
        $response->assertDontSee('Online Randevu Planla');
        $response->assertSee('Hekimimiz online randevu alımına geçici olarak kapalıdır');
        $response->assertSee('05551234567');
    }

    /**
     * Test patient can successfully book an appointment.
     */
    public function test_patient_can_successfully_book_appointment(): void
    {
        RandevuAyari::create([
            'doktor_id' => $this->doktor->id,
            'aktif_mi' => true,
            'randevu_onay_tipi' => 'manuel',
        ]);

        $hasta = Hasta::create([
            'ad' => 'Ahmet',
            'soyad' => 'Yilmaz',
            'e_posta' => 'ahmet@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '05559998877',
            'aktif_mi' => true,
        ]);

        $postData = [
            'doktor_id' => $this->doktor->id,
            'hizmet_id' => $this->hizmet->id,
            'tarih' => date('Y-m-d', strtotime('next Monday')),
            'saat' => '10:00',
            'not' => 'Bas agrisi sikayeti',
        ];

        $response = $this->actingAs($hasta, 'hasta')
            ->post(route('frontend.hasta.randevu.kaydet'), $postData);

        $response->assertRedirect();
        $response->assertSessionHas('basarili');

        $this->assertDatabaseHas('randevular', [
            'doktor_id' => $this->doktor->id,
            'hizmet_id' => $this->hizmet->id,
            'hasta_id' => $hasta->id,
            'tarih' => $postData['tarih'].' 00:00:00',
            'saat' => '10:00',
            'not' => 'Bas agrisi sikayeti',
            'durum' => 'beklemede',
        ]);
    }

    /**
     * Test patient cannot book a slot that is already booked.
     */
    public function test_patient_cannot_book_already_booked_slot(): void
    {
        RandevuAyari::create([
            'doktor_id' => $this->doktor->id,
            'aktif_mi' => true,
            'randevu_onay_tipi' => 'manuel',
        ]);

        $hasta1 = Hasta::create([
            'ad' => 'Ahmet',
            'soyad' => 'Yilmaz',
            'e_posta' => 'ahmet@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '05559998877',
            'aktif_mi' => true,
        ]);

        $hasta2 = Hasta::create([
            'ad' => 'Mehmet',
            'soyad' => 'Demir',
            'e_posta' => 'mehmet@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '05559998866',
            'aktif_mi' => true,
        ]);

        $tarih = date('Y-m-d', strtotime('next Monday'));

        // First appointment
        Randevu::create([
            'doktor_id' => $this->doktor->id,
            'hizmet_id' => $this->hizmet->id,
            'hasta_id' => $hasta1->id,
            'ad' => $hasta1->ad,
            'soyad' => $hasta1->soyad,
            'telefon' => $hasta1->telefon,
            'e_posta' => $hasta1->e_posta,
            'tarih' => $tarih,
            'saat' => '10:00',
            'not' => 'Ilk randevu',
            'durum' => 'beklemede',
        ]);

        $postData = [
            'doktor_id' => $this->doktor->id,
            'hizmet_id' => $this->hizmet->id,
            'tarih' => $tarih,
            'saat' => '10:00',
            'not' => 'Ikinci randevu ayni saatte',
        ];

        $response = $this->actingAs($hasta2, 'hasta')
            ->post(route('frontend.hasta.randevu.kaydet'), $postData);

        $response->assertRedirect();
        $response->assertSessionHas('hata', 'Seçtiğiniz randevu saati maalesef doludur. Lütfen başka bir saat seçin.');

        $this->assertDatabaseMissing('randevular', [
            'hasta_id' => $hasta2->id,
            'not' => 'Ikinci randevu ayni saatte',
        ]);
    }
}
