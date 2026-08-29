<?php

namespace Tests\Feature;

use App\Models\Doktor;
use App\Models\Klinik;
use App\Models\KlinikPersonel;
use App\Services\RecaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Giris uclari icin kaba kuvvet ve reCAPTCHA davranisi.
 *
 * Regresyon: PersonelAuthController::girisYap hicbir hiz sinirlamasi
 * icermiyordu; rotada da `throttle` yoktu. Tek savunma reCAPTCHA idi ve o da
 * anahtar tanimli degilse / Google'a ulasilamiyorsa sessizce geciyordu.
 * Ayrica pasif hesap icin farkli hata mesaji donduruldugu icin gecerli
 * e-posta adresleri disaridan tespit edilebiliyordu.
 */
class GirisGuvenligiTest extends TestCase
{
    use RefreshDatabase;

    private KlinikPersonel $personel;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('personel-giris:personel@test.com|127.0.0.1');

        $sahip = Doktor::factory()->create();

        $klinik = Klinik::create([
            'ad' => 'Test Klinik',
            'slug' => 'test-klinik-giris',
            'sahip_doktor_id' => $sahip->id,
            'aktif_mi' => true,
        ]);

        $this->personel = KlinikPersonel::create([
            'klinik_id' => $klinik->id,
            'ad_soyad' => 'Test Personel',
            'e_posta' => 'personel@test.com',
            'sifre' => Hash::make('dogru-sifre'),
            'aktif_mi' => true,
            'sifre_degistirildi_mi' => true,
        ]);
    }

    public function test_personel_girisi_kaba_kuvvete_karsi_sinirlanir(): void
    {
        // İlk 5 hatalı deneme normal kimlik hatası vermeli
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('personel.giris.post'), [
                'e_posta' => 'personel@test.com',
                'sifre' => 'yanlis-sifre-'.$i,
            ]);
            $response->assertSessionHasErrors('e_posta');
            $this->assertStringNotContainsString(
                'Çok fazla başarısız giriş denemesi',
                (string) session('errors')?->first('e_posta')
            );
        }

        // 6. deneme hız sınırına takılmalı
        $this->post(route('personel.giris.post'), [
            'e_posta' => 'personel@test.com',
            'sifre' => 'yine-yanlis',
        ])->assertSessionHasErrors('e_posta');

        $this->assertStringContainsString(
            'Çok fazla başarısız giriş denemesi',
            (string) session('errors')?->first('e_posta')
        );

        // Sınıra takılıyken doğru şifre bile giriş yaptırmamalı
        $this->post(route('personel.giris.post'), [
            'e_posta' => 'personel@test.com',
            'sifre' => 'dogru-sifre',
        ]);
        $this->assertGuest('personel');
    }

    public function test_dogru_sifre_ile_giris_sayaci_sifirlanir(): void
    {
        $this->post(route('personel.giris.post'), [
            'e_posta' => 'personel@test.com',
            'sifre' => 'yanlis',
        ])->assertSessionHasErrors('e_posta');

        $this->post(route('personel.giris.post'), [
            'e_posta' => 'personel@test.com',
            'sifre' => 'dogru-sifre',
        ])->assertRedirect(route('personel.panel'));

        $this->assertAuthenticated('personel');
        $this->assertSame(
            0,
            RateLimiter::attempts('personel-giris:personel@test.com|127.0.0.1')
        );
    }

    public function test_pasif_hesap_mesaji_kullanici_numaralandirmasina_izin_vermez(): void
    {
        $this->personel->update(['aktif_mi' => false]);

        // Yanlış şifre: hesabın var olduğunu ele vermeyen genel mesaj
        $this->post(route('personel.giris.post'), [
            'e_posta' => 'personel@test.com',
            'sifre' => 'yanlis-sifre',
        ]);
        $this->assertSame(
            'Girdiğiniz bilgiler sistemdekilerle eşleşmiyor.',
            (string) session('errors')?->first('e_posta')
        );

        // Doğru şifre: ancak o zaman "pasif hesap" bilgisi paylaşılır
        $this->post(route('personel.giris.post'), [
            'e_posta' => 'personel@test.com',
            'sifre' => 'dogru-sifre',
        ]);
        $this->assertStringContainsString(
            'pasif duruma getirilmiştir',
            (string) session('errors')?->first('e_posta')
        );
        $this->assertGuest('personel');
    }

    public function test_strict_aksiyonda_yapilandirilmamis_recaptcha_gecmez(): void
    {
        config([
            'recaptcha.enabled' => true,
            'recaptcha.secret_key' => '',
            'recaptcha.soft_fail_when_unconfigured' => true,
            'recaptcha.strict_actions' => ['hasta_kayit'],
        ]);

        $service = app(RecaptchaService::class);

        // Strict listede: anahtar yokken istek reddedilmeli
        $strict = $service->verify(null, 'hasta_kayit');
        $this->assertFalse($strict['ok']);
        $this->assertSame('unconfigured', $strict['reason'] ?? null);

        // Listede olmayan aksiyon eski davranışı korur (kurulumu kilitlememek için)
        $gevsek = $service->verify(null, 'hekim_giris');
        $this->assertTrue($gevsek['ok']);
        $this->assertTrue($gevsek['skipped'] ?? false);
    }

    public function test_strict_actions_varsayilan_olarak_bostur(): void
    {
        // Varsayılan davranış değişmemeli: anahtar tanımlamamış kurulum kilitlenmez
        $this->assertSame([], config('recaptcha.strict_actions'));
        $this->assertFalse(app(RecaptchaService::class)->isStrictAction('hasta_kayit'));
    }
}
