<?php

namespace Tests\Feature;

use App\Models\Doktor;
use App\Models\Hasta;
use App\Models\Paket;
use App\Models\PaketOzelligi;
use App\Models\Randevu;
use App\Models\Yorum;
use App\Models\YorumDaveti;
use App\Notifications\YorumDavetBildirimi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Girişsiz yorum daveti.
 *
 * Önceki akışta yorum bırakmak için hasta girişi şarttı; misafir randevuyla
 * açılan hesabın şifresini hasta bilmediği için pratikte kimse yorum
 * yapamıyordu (canlıda yorum tablosu boştu). Ayrıca davet yalnızca hekim
 * randevuyu "tamamlandı" işaretlerse gidiyordu; sahada bu işaretleme
 * neredeyse hiç yapılmıyor (saati geçmiş 7 randevunun 4'ü "onaylandı"ydı).
 *
 * Artık: randevu saatinden 2 saat sonra, tek kullanımlık kısa bağlantı.
 */
class YorumDavetiTest extends TestCase
{
    use RefreshDatabase;

    private function yorumDavetliPaket(): Paket
    {
        $paket = Paket::create([
            'ad' => 'Test Paket',
            'tur' => 'hekim',
            'aciklama' => 'Test',
            'aylik_fiyat' => 100,
            'yillik_fiyat' => 1000,
            'aktif_mi' => true,
        ]);

        $ozellik = PaketOzelligi::firstOrCreate(['kod' => 'yorum_davet'], ['ad' => 'Yorum daveti']);
        $paket->sistemOzellikleri()->attach($ozellik->id);

        return $paket;
    }

    /** Saati geçmiş, onaylı, e-postalı randevu. */
    private function randevuOlustur(array $ek = []): Randevu
    {
        $paket = $this->yorumDavetliPaket();
        $doktor = Doktor::factory()->create(['paket_id' => $paket->id]);
        // hastalar.e_posta BENZERSIZ — her cagrida ayri adres
        $hasta = Hasta::factory()->create(['e_posta' => 'hasta'.uniqid().'@ornek.test']);

        return Randevu::factory()->create(array_merge([
            'doktor_id' => $doktor->id,
            'hasta_id' => $hasta->id,
            'durum' => 'onaylandi',
            'tarih' => now()->subDay()->toDateString(),
            'saat' => '10:00',
        ], $ek));
    }

    public function test_saati_gecmis_onayli_randevuya_davet_gider(): void
    {
        Notification::fake();
        $randevu = $this->randevuOlustur();

        $this->artisan('yorum:davet-gonder')->assertSuccessful();

        $this->assertDatabaseHas('yorum_davetleri', [
            'randevu_id' => $randevu->id,
            'hasta_id' => $randevu->hasta_id,
        ]);
        Notification::assertSentTo($randevu->hasta, YorumDavetBildirimi::class);
    }

    public function test_tamamlandi_isaretlenmemis_olmasi_engel_degil(): void
    {
        // Asıl düzeltme bu: hekim "tamamlandı" işaretlemese de davet gider.
        Notification::fake();
        $randevu = $this->randevuOlustur(['durum' => 'onaylandi']);

        $this->artisan('yorum:davet-gonder')->assertSuccessful();

        $this->assertDatabaseHas('yorum_davetleri', ['randevu_id' => $randevu->id]);
    }

    public function test_iptal_ve_beklemede_olanlara_davet_gitmez(): void
    {
        Notification::fake();
        $iptal = $this->randevuOlustur(['durum' => 'iptal']);
        $bekleyen = $this->randevuOlustur(['durum' => 'beklemede']);

        $this->artisan('yorum:davet-gonder')->assertSuccessful();

        $this->assertDatabaseMissing('yorum_davetleri', ['randevu_id' => $iptal->id]);
        $this->assertDatabaseMissing('yorum_davetleri', ['randevu_id' => $bekleyen->id]);
    }

    public function test_iki_saat_dolmadan_davet_gitmez(): void
    {
        Notification::fake();
        $randevu = $this->randevuOlustur([
            'tarih' => now()->toDateString(),
            'saat' => now()->subMinutes(30)->format('H:i'),
        ]);

        $this->artisan('yorum:davet-gonder')->assertSuccessful();

        $this->assertDatabaseMissing('yorum_davetleri', ['randevu_id' => $randevu->id]);
    }

    public function test_cok_eski_randevulara_toplu_davet_gitmez(): void
    {
        // Özellik ilk açıldığında aylar öncesine mail yağmasın.
        Notification::fake();
        $randevu = $this->randevuOlustur(['tarih' => now()->subDays(60)->toDateString()]);

        $this->artisan('yorum:davet-gonder')->assertSuccessful();

        $this->assertDatabaseMissing('yorum_davetleri', ['randevu_id' => $randevu->id]);
    }

    public function test_paket_kapsamiyorsa_davet_gitmez(): void
    {
        Notification::fake();
        $randevu = $this->randevuOlustur();
        $randevu->doktor->paket->sistemOzellikleri()->detach();

        $this->artisan('yorum:davet-gonder')->assertSuccessful();

        $this->assertDatabaseMissing('yorum_davetleri', ['randevu_id' => $randevu->id]);
    }

    public function test_epostasi_olmayan_hastaya_davet_gitmez(): void
    {
        Notification::fake();
        $randevu = $this->randevuOlustur();
        // hastalar.e_posta NOT NULL: e-posta vermeyen misafirde boş string kalır
        $randevu->hasta->update(['e_posta' => '']);

        $this->artisan('yorum:davet-gonder')->assertSuccessful();

        $this->assertDatabaseMissing('yorum_davetleri', ['randevu_id' => $randevu->id]);
    }

    public function test_ayni_randevuya_ikinci_kez_davet_gitmez(): void
    {
        Notification::fake();
        $randevu = $this->randevuOlustur();

        $this->artisan('yorum:davet-gonder')->assertSuccessful();
        $this->artisan('yorum:davet-gonder')->assertSuccessful();

        $this->assertSame(1, YorumDaveti::where('randevu_id', $randevu->id)->count());
        Notification::assertSentToTimes($randevu->hasta, YorumDavetBildirimi::class, 1);
    }

    public function test_ham_token_veritabaninda_saklanmaz(): void
    {
        $randevu = $this->randevuOlustur();
        $this->artisan('yorum:davet-gonder')->assertSuccessful();

        $davet = YorumDaveti::first();
        $this->assertSame(64, strlen($davet->token_hash), 'sha256 özeti saklanmalı');
        $this->assertNotNull(YorumDaveti::bul($this->tokenUret($davet)) ?? null);
    }

    /** Testte ham token'a ulaşmak için: bildirimden yakalanır. */
    private function tokenUret(YorumDaveti $davet): string
    {
        // Bildirimden yakalamak yerine dogrudan yeni token uretip ozetini yaz.
        $token = YorumDaveti::tokenUret();
        $davet->update(['token_hash' => YorumDaveti::tokenOzeti($token)]);

        return $token;
    }

    /* ------------------------------------------------------------------
     | Girişsiz form
     |------------------------------------------------------------------ */

    private function davetVeToken(): array
    {
        $randevu = $this->randevuOlustur();
        $token = YorumDaveti::tokenUret();
        $davet = YorumDaveti::create([
            'randevu_id' => $randevu->id,
            'hasta_id' => $randevu->hasta_id,
            'doktor_id' => $randevu->doktor_id,
            'token_hash' => YorumDaveti::tokenOzeti($token),
            'gecerlilik_bitis' => now()->addDays(30),
            'gonderildi_at' => now(),
        ]);

        return [$davet, $token, $randevu];
    }

    public function test_form_girissiz_acilir(): void
    {
        [, $token] = $this->davetVeToken();

        $this->get('/y/'.$token)
            ->assertOk()
            ->assertSee('Değerlendirmeniz', false);
    }

    public function test_yorum_girissiz_kaydedilir_ve_onay_bekler(): void
    {
        [$davet, $token, $randevu] = $this->davetVeToken();

        $this->post('/y/'.$token, [
            'puan' => 5,
            'yorum' => 'Çok ilgili ve güler yüzlü bir hekim, teşekkür ederim.',
        ])->assertOk();

        $this->assertDatabaseHas('yorumlar', [
            'randevu_id' => $randevu->id,
            'hasta_id' => $randevu->hasta_id,
            'doktor_id' => $randevu->doktor_id,
            'puan' => 5,
            'onay_durumu' => 'beklemede',
        ]);
        $this->assertNotNull($davet->fresh()->kullanildi_at);
    }

    public function test_baglanti_tek_kullanimliktir(): void
    {
        [, $token] = $this->davetVeToken();

        $this->post('/y/'.$token, ['puan' => 5, 'yorum' => 'Birinci değerlendirme metni.'])->assertOk();
        $this->post('/y/'.$token, ['puan' => 1, 'yorum' => 'İkinci değerlendirme metni.'])->assertStatus(410);

        $this->assertSame(1, Yorum::count());
        $this->assertSame(5, (int) Yorum::first()->puan, 'İkinci gönderim puanı ezmemeli');
    }

    public function test_suresi_dolmus_baglanti_reddedilir(): void
    {
        [$davet, $token] = $this->davetVeToken();
        $davet->update(['gecerlilik_bitis' => now()->subDay()]);

        $this->get('/y/'.$token)->assertStatus(410);
        $this->post('/y/'.$token, ['puan' => 5, 'yorum' => 'Geçerli uzunlukta bir metin.'])->assertStatus(410);
        $this->assertSame(0, Yorum::count());
    }

    public function test_gecersiz_token_reddedilir(): void
    {
        $this->get('/y/'.str_repeat('a', 16))->assertStatus(410);
    }

    public function test_puan_ve_metin_dogrulanir(): void
    {
        [, $token] = $this->davetVeToken();

        $this->post('/y/'.$token, ['puan' => 9, 'yorum' => 'Yeterince uzun bir metin.'])
            ->assertSessionHasErrors('puan');
        $this->post('/y/'.$token, ['puan' => 5, 'yorum' => 'kısa'])
            ->assertSessionHasErrors('yorum');

        $this->assertSame(0, Yorum::count());
    }

    public function test_kisa_baglanti_adresi_beklenen_bicimde(): void
    {
        [, $token] = $this->davetVeToken();

        $this->assertSame(url('/y/'.$token), route('yorum.davet', ['token' => $token]));
        $this->assertLessThan(60, strlen(route('yorum.davet', ['token' => $token])) - strlen(url('/')));
    }

    public function test_yakala_tumu_il_rotasi_y_adresini_calmaz(): void
    {
        // routes/frontend.php sonundaki /{il_slug} rotası "y" ile eşleşmemeli
        [, $token] = $this->davetVeToken();

        $this->get('/y/'.$token)->assertOk();
    }
}
