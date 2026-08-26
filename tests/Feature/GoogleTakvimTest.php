<?php

namespace Tests\Feature;

use App\Events\RandevuDurumuDegisti;
use App\Events\RandevuOlusturuldu;
use App\Jobs\PullGoogleBloklariJob;
use App\Jobs\SyncRandevuToGoogleJob;
use App\Listeners\RandevuGoogleTakvimeYaz;
use App\Models\Brans;
use App\Models\Doktor;
use App\Models\DoktorGoogleBlok;
use App\Models\Hasta;
use App\Models\Hizmet;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Paket;
use App\Models\PaketOzelligi;
use App\Models\Randevu;
use App\Models\RandevuAyari;
use App\Models\SiteAyari;
use App\Services\RandevuDogrulamaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GoogleTakvimTest extends TestCase
{
    use RefreshDatabase;

    private Doktor $doktor;

    private Hizmet $hizmet;

    protected function setUp(): void
    {
        parent::setUp();

        SiteAyari::create([
            'meta_baslik' => 'Randevu Ajandam',
            'meta_aciklama' => 'Test',
            'meta_anahtar_kelimeler' => 'test',
            'meta_yazar' => 'Test',
        ]);

        $il = Il::create(['ad' => 'Istanbul', 'plaka' => '34']);
        $ilce = Ilce::create(['il_id' => $il->id, 'ad' => 'Sisli']);
        $brans = Brans::create(['ad' => 'Kardiyoloji']);

        $profil = PaketOzelligi::firstOrCreate(['kod' => 'profil_sayfasi'], ['ad' => 'Profil']);
        $gcal = PaketOzelligi::firstOrCreate(['kod' => 'google_takvim'], ['ad' => 'Google Takvim']);

        $paket = Paket::create([
            'ad' => 'Test Paket',
            'tur' => 'bireysel',
            'aciklama' => 't',
            'aylik_fiyat' => 0,
            'yillik_fiyat' => 0,
            'ozellikler' => [],
            'aktif_mi' => true,
        ]);
        $paket->sistemOzellikleri()->sync([$profil->id, $gcal->id]);

        $this->doktor = Doktor::create([
            'ad_soyad' => 'Test Hekim',
            'e_posta' => 'gcal@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '05551234567',
            'tur' => 'bireysel',
            'unvan' => 'Dr.',
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
            'ad' => 'Muayene',
            'slug' => 'muayene',
            'aciklama' => 'test',
            'sure' => 30,
            'fiyat' => 100.00,
            'aktif_mi' => true,
        ]);

        RandevuAyari::create([
            'doktor_id' => $this->doktor->id,
            'aktif_mi' => true,
            'randevu_onay_tipi' => 'manuel',
        ]);

        Config::set('google_calendar.enabled', true);
        Config::set('google_calendar.webhook_token', 'test-webhook-token');
    }

    public function test_google_takvim_bagli_degilse_listener_no_op(): void
    {
        Bus::fake();

        // Config yok -> baglanti yok
        $randevu = $this->onaylanmisRandevuOlustur();

        (new RandevuGoogleTakvimeYaz())->olusturuldu(new RandevuOlusturuldu($randevu));

        Bus::assertNotDispatched(SyncRandevuToGoogleJob::class);
    }

    public function test_bagli_hekimde_onayli_randevu_sync_job_kuyruklar(): void
    {
        Bus::fake();
        $this->doktor->forceFill([
            'google_calendar_config' => [
                'access_token' => 'ac',
                'refresh_token' => 'rt',
                'expires_at' => time() + 3600,
                'calendar_id' => 'primary',
            ],
            'google_calendar_baglandi_at' => now(),
        ])->save();
        $this->doktor->refresh();

        $randevu = $this->onaylanmisRandevuOlustur();

        (new RandevuGoogleTakvimeYaz())->olusturuldu(new RandevuOlusturuldu($randevu));

        Bus::assertDispatched(SyncRandevuToGoogleJob::class, fn ($job) => $job->randevuId === $randevu->id);
    }

    public function test_beklemede_randevu_google_a_yazilmaz(): void
    {
        Bus::fake();
        $this->doktor->forceFill([
            'google_calendar_config' => [
                'access_token' => 'ac',
                'refresh_token' => 'rt',
                'expires_at' => time() + 3600,
                'calendar_id' => 'primary',
            ],
        ])->save();
        $this->doktor->refresh();

        $randevu = Randevu::create([
            'doktor_id' => $this->doktor->id,
            'hizmet_id' => $this->hizmet->id,
            'hasta_id' => $this->misafirHasta()->id,
            'ad' => 'A', 'soyad' => 'B',
            'telefon' => '05550000000',
            'tarih' => now()->addDays(3)->toDateString(),
            'saat' => '10:00',
            'durum' => 'beklemede',
        ]);

        (new RandevuGoogleTakvimeYaz())->olusturuldu(new RandevuOlusturuldu($randevu));

        Bus::assertNotDispatched(SyncRandevuToGoogleJob::class);
    }

    public function test_iptal_edildiginde_sync_job_kuyruklar(): void
    {
        Bus::fake();
        $this->doktor->forceFill([
            'google_calendar_config' => [
                'access_token' => 'ac',
                'refresh_token' => 'rt',
                'expires_at' => time() + 3600,
                'calendar_id' => 'primary',
            ],
        ])->save();
        $this->doktor->refresh();

        $randevu = $this->onaylanmisRandevuOlustur();
        $randevu->update(['durum' => 'iptal']);

        (new RandevuGoogleTakvimeYaz())->durumDegisti(new RandevuDurumuDegisti($randevu, 'onaylandi', 'iptal'));

        Bus::assertDispatched(SyncRandevuToGoogleJob::class);
    }

    public function test_google_blok_slotu_engeller(): void
    {
        // Hekim bagli
        $this->doktor->forceFill([
            'google_calendar_config' => [
                'access_token' => 'ac',
                'refresh_token' => 'rt',
                'expires_at' => time() + 3600,
                'calendar_id' => 'primary',
            ],
        ])->save();
        $this->doktor->refresh();

        // 10:00-10:30 arasi blok var
        $bugun = now()->addDays(2)->startOfDay();
        DoktorGoogleBlok::create([
            'doktor_id' => $this->doktor->id,
            'google_event_id' => 'test-event-1',
            'baslangic_at' => $bugun->copy()->setTime(10, 0),
            'bitis_at' => $bugun->copy()->setTime(10, 30),
            'baslik' => 'Kisisel etkinlik',
            'hepsi_gun_mu' => false,
        ]);

        $service = app(RandevuDogrulamaService::class);
        $hata = $service->dogrula(
            $this->doktor->fresh(),
            $bugun->toDateString(),
            '10:00'
        );

        $this->assertNotNull($hata);
        $this->assertStringContainsString('Google Takvim', $hata);
    }

    public function test_webhook_yanlis_token_ile_401_doner(): void
    {
        $response = $this->postJson('/api/google-calendar/webhook', [], [
            'X-Goog-Channel-Token' => 'yanlis-token',
        ]);
        $response->assertStatus(401);
    }

    public function test_webhook_dogru_token_ile_200_doner(): void
    {
        $response = $this->post('/api/google-calendar/webhook', [], [
            'X-Goog-Channel-Token' => 'test-webhook-token',
            'X-Goog-Resource-State' => 'sync',
            'X-Goog-Channel-ID' => 'test-channel',
        ]);
        $response->assertStatus(200);
    }

    public function test_webhook_eslesen_channel_pull_dispatch(): void
    {
        Bus::fake();

        $this->doktor->forceFill([
            'google_calendar_config' => [
                'access_token' => 'ac',
                'refresh_token' => 'rt',
                'expires_at' => time() + 3600,
                'calendar_id' => 'primary',
                'channel_id' => 'ch-123',
                'channel_resource_id' => 'res-123',
            ],
        ])->save();

        $response = $this->post('/api/google-calendar/webhook', [], [
            'X-Goog-Channel-Token' => 'test-webhook-token',
            'X-Goog-Resource-State' => 'exists',
            'X-Goog-Channel-ID' => 'ch-123',
            'X-Goog-Resource-ID' => 'res-123',
        ]);
        $response->assertStatus(200);

        Bus::assertDispatched(PullGoogleBloklariJob::class, fn ($j) => $j->doktorId === $this->doktor->id);
    }

    private function misafirHasta(): Hasta
    {
        return Hasta::create([
            'ad' => 'Misafir',
            'soyad' => 'Test',
            'e_posta' => 'misafir-'.uniqid().'@t.com',
            'sifre' => Hash::make('sifre'),
            'telefon' => '05551112233',
            'aktif_mi' => true,
        ]);
    }

    private function onaylanmisRandevuOlustur(): Randevu
    {
        return Randevu::create([
            'doktor_id' => $this->doktor->id,
            'hizmet_id' => $this->hizmet->id,
            'hasta_id' => $this->misafirHasta()->id,
            'ad' => 'A', 'soyad' => 'B',
            'telefon' => '05550000000',
            'tarih' => now()->addDays(3)->toDateString(),
            'saat' => '11:00',
            'durum' => 'onaylandi',
        ]);
    }
}
