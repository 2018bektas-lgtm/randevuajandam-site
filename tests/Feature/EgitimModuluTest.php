<?php

namespace Tests\Feature;

use App\Models\Brans;
use App\Models\Doktor;
use App\Models\Egitim;
use App\Models\FinansKategori;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Odeme;
use App\Models\Paket;
use App\Models\PaketOzelligi;
use App\Services\EgitimBasvuruService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EgitimModuluTest extends TestCase
{
    use RefreshDatabase;

    private Doktor $doktor;

    private Egitim $egitim;

    protected function setUp(): void
    {
        parent::setUp();

        $il = Il::create(['ad' => 'Izmir', 'plaka' => '35', 'slug' => 'izmir']);
        $ilce = Ilce::create(['il_id' => $il->id, 'ad' => 'Konak', 'slug' => 'konak']);
        $brans = Brans::create(['ad' => 'Psikoloji', 'slug' => 'psikoloji']);

        $oz = PaketOzelligi::create(['kod' => 'egitimler', 'ad' => 'Egitim', 'aciklama' => 't']);
        $paket = Paket::create([
            'ad' => 'VIP Test',
            'tur' => 'bireysel',
            'aciklama' => 't',
            'aylik_fiyat' => 100,
            'yillik_fiyat' => 1000,
            'aktif_mi' => true,
            'ozellikler' => [],
        ]);
        $paket->sistemOzellikleri()->sync([$oz->id]);

        $this->doktor = Doktor::create([
            'ad_soyad' => 'Egitim Hekim',
            'slug' => 'egitim-hekim',
            'e_posta' => 'egitim-hekim@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '05551112233',
            'tur' => 'bireysel',
            'unvan' => 'Uzm. Psk.',
            'il_id' => $il->id,
            'ilce_id' => $ilce->id,
            'paket_id' => $paket->id,
            'aktif_mi' => true,
            'platformda_gorunur' => true,
        ]);
        $this->doktor->branslar()->attach($brans->id);

        $this->egitim = Egitim::create([
            'doktor_id' => $this->doktor->id,
            'baslik' => 'Travma Semineri',
            'ozet' => 'Test ozet',
            'icerik' => 'Icerik',
            'tip' => 'online',
            'fiyat' => 1500,
            'basvuru_acik_mi' => true,
            'durum' => 'yayinda',
        ]);
    }

    public function test_guest_can_apply_to_education(): void
    {
        $response = $this->post(route('frontend.egitim.basvuru'), [
            'egitim_id' => $this->egitim->id,
            'ad' => 'Ayşe',
            'soyad' => 'Yılmaz',
            'telefon' => '05321112233',
            'e_posta' => 'ayse@example.com',
            'kvkk_onay' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('basarili');
        $this->assertDatabaseHas('egitim_basvurulari', [
            'egitim_id' => $this->egitim->id,
            'ad' => 'Ayşe',
            'durum' => 'beklemede',
            'ucret_durumu' => 'beklemede',
        ]);
    }

    public function test_payment_received_creates_finance_income(): void
    {
        $basvuru = app(EgitimBasvuruService::class)->basvur($this->egitim, [
            'ad' => 'Mehmet',
            'soyad' => 'Demir',
            'telefon' => '05329876543',
            'e_posta' => 'm@example.com',
            'kvkk_onay' => true,
        ]);

        $this->assertSame(0, Odeme::count());

        app(EgitimBasvuruService::class)->odemeAlindi($basvuru, 1500, 'havale');

        $this->assertDatabaseHas('finans_kategoriler', [
            'doktor_id' => $this->doktor->id,
            'ad' => 'Eğitim',
            'tur' => 'gelir',
        ]);
        $this->assertDatabaseHas('odemeler', [
            'doktor_id' => $this->doktor->id,
            'egitim_basvuru_id' => $basvuru->id,
            'durum' => 'odendi',
        ]);
        $this->assertSame('odendi', $basvuru->fresh()->ucret_durumu);
    }

    public function test_doctor_can_list_educations_with_package(): void
    {
        $response = $this->actingAs($this->doktor, 'doktor')
            ->get(route('hekim.egitimler.index'));

        $response->assertStatus(200);
        $response->assertSee('Travma Semineri');
    }

    public function test_public_detail_page(): void
    {
        $url = '/izmir/konak/psikoloji/egitim-hekim/egitim/'.$this->egitim->slug;
        $response = $this->get($url);
        $response->assertStatus(200);
        $response->assertSee('Travma Semineri');
        $response->assertSee('Başvuru formu');
    }
}
