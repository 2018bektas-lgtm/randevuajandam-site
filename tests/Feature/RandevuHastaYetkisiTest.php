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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Hekim panelinden randevu olustururken `danisan_id` yetkilendirmesi.
 *
 * Regresyon: bu uc daha once `Hasta::findOrFail($request->danisan_id)`
 * kullaniyordu; dogrulama sadece `exists:hastalar,id` oldugu icin bir hekim
 * BASKA bir muayenehanenin hastasina randevu yazabiliyordu. Randevu kaydina
 * hastanin ad/soyad/telefon/e-posta bilgisi kopyalandigi icin bu ayni zamanda
 * bir kisisel veri sizdirma yoluydu.
 */
class RandevuHastaYetkisiTest extends TestCase
{
    use RefreshDatabase;

    private Doktor $doktorA;

    private Doktor $doktorB;

    private Hizmet $hizmetA;

    private Hasta $hastaB;

    protected function setUp(): void
    {
        parent::setUp();

        $il = Il::create(['ad' => 'Ankara', 'plaka' => '06']);
        $ilce = Ilce::create(['il_id' => $il->id, 'ad' => 'Cankaya']);
        $brans = Brans::create(['ad' => 'Psikoloji']);

        $paket = Paket::create([
            'ad' => 'Test Paketi',
            'tur' => 'bireysel',
            'aciklama' => 'Test',
            'aylik_fiyat' => 0,
            'yillik_fiyat' => 0,
            'ozellikler' => [],
            'aktif_mi' => true,
        ]);
        // Rota `paket.yetki:online_takvim` ile korunuyor; bu yetki verilmezse
        // istek controller'a hic ulasmaz ve testler yanlis sebeple gecer.
        $ozTakvim = PaketOzelligi::firstOrCreate(['kod' => 'online_takvim'], ['ad' => 'Online randevu takvimi']);
        $paket->sistemOzellikleri()->sync([$ozTakvim->id]);

        $this->doktorA = $this->doktorOlustur('A Hekim', 'hekim-a@test.com', $il, $ilce, $brans, $paket);
        $this->doktorB = $this->doktorOlustur('B Hekim', 'hekim-b@test.com', $il, $ilce, $brans, $paket);

        $this->hizmetA = Hizmet::create([
            'doktor_id' => $this->doktorA->id,
            'ad' => 'Danismanlik',
            'slug' => 'danismanlik-a',
            'aciklama' => 'Test hizmeti',
            'sure' => 30,
            'fiyat' => 500,
            'aktif_mi' => true,
        ]);

        // Yalnizca B hekiminin hasta havuzundaki hasta
        $this->hastaB = Hasta::create([
            'ad' => 'Gizli',
            'soyad' => 'Danisan',
            'ad_soyad' => 'Gizli Danisan',
            'e_posta' => 'gizli.danisan@test.com',
            'telefon' => '05559998877',
            'sifre' => Hash::make('sifre123'),
        ]);
        $this->doktorB->hastalar()->attach($this->hastaB->id);
    }

    private function doktorOlustur(string $ad, string $eposta, Il $il, Ilce $ilce, Brans $brans, Paket $paket): Doktor
    {
        $doktor = Doktor::create([
            'ad_soyad' => $ad,
            'e_posta' => $eposta,
            'sifre' => Hash::make('sifre123'),
            'il_id' => $il->id,
            'ilce_id' => $ilce->id,
            'tur' => 'bireysel',
            'aktif_mi' => true,
            'uzmanlik_alani' => 'Psikoloji',
            'paket_id' => $paket->id,
            'platformda_gorunur' => true,
            'meslek_dogrulama_durumu' => 'onaylandi',
        ]);
        $doktor->branslar()->attach($brans->id);

        for ($gun = 1; $gun <= 7; $gun++) {
            $doktor->calismaSaatleri()->create([
                'gun' => $gun,
                'aktif_mi' => true,
                'mesai_baslangic' => '09:00',
                'mesai_bitis' => '18:00',
            ]);
        }

        return $doktor;
    }

    private function yarin(): string
    {
        return now()->addDay()->format('Y-m-d');
    }

    public function test_hekim_baska_hekimin_hastasina_randevu_yazamaz(): void
    {
        $response = $this->actingAs($this->doktorA, 'doktor')
            ->postJson(route('hekim.randevu.store'), [
                'hizmet_id' => $this->hizmetA->id,
                'danisan_id' => $this->hastaB->id,
                'tarih' => $this->yarin(),
                'saat' => '10:00',
            ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Bu danışan hasta listenizde bulunmuyor.',
        ]);

        $this->assertDatabaseMissing('randevular', [
            'doktor_id' => $this->doktorA->id,
            'hasta_id' => $this->hastaB->id,
        ]);
    }

    public function test_yetkisiz_randevu_denemesi_hasta_bilgisini_sizdirmaz(): void
    {
        $response = $this->actingAs($this->doktorA, 'doktor')
            ->postJson(route('hekim.randevu.store'), [
                'hizmet_id' => $this->hizmetA->id,
                'danisan_id' => $this->hastaB->id,
                'tarih' => $this->yarin(),
                'saat' => '11:00',
            ]);

        $response->assertStatus(403);
        $response->assertDontSee('Gizli');
        $response->assertDontSee('05559998877');
        $response->assertDontSee('gizli.danisan@test.com');
    }

    public function test_hekim_kendi_hastasina_randevu_yazabilir(): void
    {
        $hastaA = Hasta::create([
            'ad' => 'Kendi',
            'soyad' => 'Danisan',
            'ad_soyad' => 'Kendi Danisan',
            'e_posta' => 'kendi.danisan@test.com',
            'telefon' => '05551112233',
            'sifre' => Hash::make('sifre123'),
        ]);
        $this->doktorA->hastalar()->attach($hastaA->id);

        $response = $this->actingAs($this->doktorA, 'doktor')
            ->postJson(route('hekim.randevu.store'), [
                'hizmet_id' => $this->hizmetA->id,
                'danisan_id' => $hastaA->id,
                'tarih' => $this->yarin(),
                'saat' => '15:00', // 12:00 ogle molasina denk geliyor
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('randevular', [
            'doktor_id' => $this->doktorA->id,
            'hasta_id' => $hastaA->id,
        ]);
    }

    public function test_randevu_hastasi_bul_klinik_havuzunu_da_kapsar(): void
    {
        // Kendi havuzunda yok → null
        $this->assertNull($this->doktorA->randevuHastasiBul($this->hastaB->id));

        // B hekiminin kendi havuzunda var → bulunur
        $bulunan = $this->doktorB->randevuHastasiBul($this->hastaB->id);
        $this->assertNotNull($bulunan);
        $this->assertSame($this->hastaB->id, $bulunan->id);

        // Gecersiz id
        $this->assertNull($this->doktorA->randevuHastasiBul(0));
        $this->assertNull($this->doktorA->randevuHastasiBul(999999));
    }

    public function test_randevu_olusturulmadi_dogrulamasi(): void
    {
        $this->actingAs($this->doktorA, 'doktor')
            ->postJson(route('hekim.randevu.store'), [
                'hizmet_id' => $this->hizmetA->id,
                'danisan_id' => $this->hastaB->id,
                'tarih' => $this->yarin(),
                'saat' => '13:00',
            ]);

        $this->assertSame(0, Randevu::query()->where('doktor_id', $this->doktorA->id)->count());
    }
}
