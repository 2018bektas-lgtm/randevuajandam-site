<?php

namespace Tests\Feature;

use App\Models\Doktor;
use App\Models\Hasta;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Paket;
use App\Models\PaketOzelligi;
use App\Models\SiteAyari;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HekimHastaImportTest extends TestCase
{
    use RefreshDatabase;

    private Doktor $doktor;

    protected function setUp(): void
    {
        parent::setUp();

        SiteAyari::create([
            'meta_baslik' => 'RA',
            'meta_aciklama' => 'x',
            'meta_anahtar_kelimeler' => 'x',
            'meta_yazar' => 'x',
        ]);

        $il = Il::create(['ad' => 'Istanbul', 'plaka' => '34']);
        $ilce = Ilce::create(['il_id' => $il->id, 'ad' => 'Sisli']);

        $kartlari = PaketOzelligi::firstOrCreate(['kod' => 'hasta_kartlari'], ['ad' => 'Hasta Kartlari']);
        $export = PaketOzelligi::firstOrCreate(['kod' => 'hasta_export'], ['ad' => 'Hasta Export']);

        $paket = Paket::create([
            'ad' => 'Test',
            'tur' => 'bireysel',
            'aciklama' => 't',
            'aylik_fiyat' => 0,
            'yillik_fiyat' => 0,
            'ozellikler' => [],
            'aktif_mi' => true,
        ]);
        $paket->sistemOzellikleri()->sync([$kartlari->id, $export->id]);

        $this->doktor = Doktor::create([
            'ad_soyad' => 'Test Hekim',
            'e_posta' => 'himp@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '05551234567',
            'tur' => 'bireysel',
            'unvan' => 'Dr.',
            'uzmanlik_alani' => 'Kardiyoloji',
            'il_id' => $il->id,
            'ilce_id' => $ilce->id,
            'aktif_mi' => true,
            'paket_id' => $paket->id,
            'meslek_dogrulama_durumu' => 'onaylandi',
            'uyelik_bitis' => now()->addYear(),
        ]);
    }

    public function test_sablon_indirilebilir(): void
    {
        $response = $this->actingAs($this->doktor, 'doktor')
            ->get(route('hekim.randevu.hastalar.sablon', ['tip' => 'az']));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('ad;soyad;telefon;e_posta;notlar', $content);
        $this->assertStringContainsString('Ayşe;Kaya', $content);
    }

    public function test_import_yeni_hastalar_ekler(): void
    {
        $csv = "\xEF\xBB\xBFad;soyad;telefon;e_posta;notlar\n"
             ."Ali;Yılmaz;05321112233;ali@test.com;\n"
             ."Ayşe;Kaya;05442223344;ayse@test.com;İlk seans\n";

        $file = UploadedFile::fake()->createWithContent('hastalar.csv', $csv);

        $response = $this->actingAs($this->doktor, 'doktor')
            ->post(route('hekim.randevu.hastalar.import'), ['dosya' => $file]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('sonuc.eklendi', 2);
        $response->assertJsonPath('sonuc.atlanan', 0);

        $this->assertDatabaseHas('hastalar', ['e_posta' => 'ali@test.com']);
        $this->assertDatabaseHas('hastalar', ['e_posta' => 'ayse@test.com']);
        $this->assertEquals(2, $this->doktor->hastalar()->count());
    }

    public function test_mukerrer_email_yeni_yaratmaz_havuza_ekler(): void
    {
        // Zaten var olan hasta
        $mevcut = Hasta::create([
            'ad' => 'Mehmet',
            'soyad' => 'Demir',
            'e_posta' => 'mehmet@test.com',
            'telefon' => '05559998877',
            'sifre' => Hash::make('sifre'),
            'aktif_mi' => true,
        ]);

        $csv = "ad;soyad;telefon;e_posta;notlar\n"
             ."Mehmet;Demir;05559998877;mehmet@test.com;\n";

        $file = UploadedFile::fake()->createWithContent('h.csv', $csv);

        $this->actingAs($this->doktor, 'doktor')
            ->post(route('hekim.randevu.hastalar.import'), ['dosya' => $file])
            ->assertJsonPath('success', true)
            ->assertJsonPath('sonuc.eklendi', 0)
            ->assertJsonPath('sonuc.guncellendi', 1);

        // Hasta tekrar yaratılmadı
        $this->assertEquals(1, Hasta::where('e_posta', 'mehmet@test.com')->count());
        // Ama doktor havuzuna eklendi
        $this->assertTrue($this->doktor->hastalar()->where('hasta_id', $mevcut->id)->exists());
    }

    public function test_gecersiz_satir_atlanir_hata_raporlanir(): void
    {
        $csv = "ad;soyad;telefon;e_posta;notlar\n"
             ."A;Yılmaz;05321112233;ok@test.com;\n"          // ad çok kısa
             ."Fatih;Kaya;abc;kaya@test.com;\n"              // telefon rakam değil
             ."Zeynep;Şahin;05551234567;bozuk-email;\n"      // geçersiz e-posta
             ."Emre;Demir;05055551122;emre@test.com;\n";     // geçerli

        $file = UploadedFile::fake()->createWithContent('h.csv', $csv);

        $response = $this->actingAs($this->doktor, 'doktor')
            ->post(route('hekim.randevu.hastalar.import'), ['dosya' => $file]);

        $response->assertOk()->assertJsonPath('sonuc.eklendi', 1)->assertJsonPath('sonuc.atlanan', 3);
        $hatalar = $response->json('sonuc.hatalar');
        $this->assertCount(3, $hatalar);
    }

    public function test_zorunlu_sutun_eksikse_reddedilir(): void
    {
        $csv = "ad;telefon\nAyşe;05321112233\n"; // soyad yok

        $file = UploadedFile::fake()->createWithContent('h.csv', $csv);

        $this->actingAs($this->doktor, 'doktor')
            ->post(route('hekim.randevu.hastalar.import'), ['dosya' => $file])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_export_hasta_havuzunu_csv_uretir(): void
    {
        $h = Hasta::create([
            'ad' => 'Test', 'soyad' => 'User',
            'e_posta' => 'exp@test.com', 'telefon' => '05551112233',
            'sifre' => Hash::make('x'), 'aktif_mi' => true,
        ]);
        $this->doktor->hastalar()->attach($h->id, [
            'kayit_tarihi' => now()->toDateString(), 'kaynak' => 'manuel',
        ]);

        $response = $this->actingAs($this->doktor, 'doktor')
            ->get(route('hekim.randevu.hastalar.export'));

        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString('exp@test.com', $content);
        $this->assertStringContainsString('manuel', $content);
    }

    public function test_export_paket_yoksa_yonlendirir(): void
    {
        // Paketten hasta_export'u kaldır
        $paket = $this->doktor->paket;
        $kartlari = PaketOzelligi::where('kod', 'hasta_kartlari')->first();
        $paket->sistemOzellikleri()->sync([$kartlari->id]);

        // paket.yetki middleware paket seçme sayfasına redirect eder (403 değil)
        $this->actingAs($this->doktor, 'doktor')
            ->get(route('hekim.randevu.hastalar.export'))
            ->assertRedirect();
    }
}
