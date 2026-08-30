<?php

namespace Tests\Feature;

use App\Models\Brans;
use App\Models\Doktor;
use App\Models\DoktorIzin;
use App\Models\Hasta;
use App\Models\Hizmet;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Paket;
use App\Models\PaketOzelligi;
use App\Models\Randevu;
use App\Models\RandevuAyari;
use App\Models\SiteAyari;
use App\Services\AppointmentBookingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * Hekim kendi takviminde randevusunu sürükleyip taşıyabilmeli.
 *
 * Bildirilen sorun: "sürükle bırak kısmında hatalar var; Cuma günü 15:00'dan
 * 18:00'a çektim, sonra 15:00'a geri çekemiyorum."
 *
 * Kök neden: AppointmentBookingService::reschedule(), HASTAYA yönelik online
 * randevu politikalarını hekimin kendi taşımasına da uyguluyordu. Üretimde
 * `en_erken_randevu_saati = 2` (saat). Yani:
 *
 *   - randevu 15:00'da, şu an 13:30  -> 1,5 saat sonra, 2 saatlik eşiğin altı
 *   - 18:00'a taşı  -> 4,5 saat sonra, eşiği geçer, BAŞARILI
 *   - 15:00'a geri  -> yine 1,5 saat sonra, eşiğin altı, 422 ile REDDEDİLİR
 *
 * Sonuç: ileri taşınabiliyor, geri taşınamıyor. Blok sessizce eski yerine
 * sıçradığı için "sürükle bırak bozuk" gibi görünüyordu.
 *
 * Bu kurallar hastanın son dakika randevu almasını engellemek içindir;
 * hekimin kendi ajandasını düzenlemesini engellememelidir. Aynı şey
 * `randevuya_acik_mi` (online alım kapalıysa hekim kendi randevusunu bile
 * taşıyamıyordu), `en_gec_randevu_gunu` ve `gunluk_maksimum_randevu` için
 * de geçerli.
 *
 * Fiziksel kısıtlar (çakışma, izin, çalışma saati, öğle arası) AYNEN
 * geçerli kalmalı — onları da bu test koruyor.
 */
class HekimRandevuTasimaTest extends TestCase
{
    use RefreshDatabase;

    private Doktor $doktor;

    private Hizmet $hizmet;

    private Hasta $hasta;

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
        $brans = Brans::create(['ad' => 'Psikoloji']);

        $paket = Paket::create([
            'ad' => 'Tasima Testi',
            'tur' => 'bireysel',
            'aciklama' => 'Test',
            'aylik_fiyat' => 0,
            'yillik_fiyat' => 0,
            'ozellikler' => [],
            'aktif_mi' => true,
        ]);
        $paket->sistemOzellikleri()->sync([
            PaketOzelligi::firstOrCreate(['kod' => 'online_takvim'], ['ad' => 'Online Takvim'])->id,
        ]);

        $this->doktor = Doktor::create([
            'ad_soyad' => 'Test Hekim',
            'e_posta' => 'tasima@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '05551234567',
            'tur' => 'bireysel',
            'il_id' => $il->id,
            'ilce_id' => $ilce->id,
            'aktif_mi' => true,
            'paket_id' => $paket->id,
            'platformda_gorunur' => true,
            'meslek_dogrulama_durumu' => 'onaylandi',
        ]);
        $this->doktor->branslar()->attach($brans->id);

        // Her gün 08:00–22:00 açık: saat kısıtı bu testin konusu değil
        for ($gun = 1; $gun <= 7; $gun++) {
            $this->doktor->calismaSaatleri()->create([
                'gun' => $gun,
                'aktif_mi' => true,
                'mesai_baslangic' => '08:00',
                'mesai_bitis' => '22:00',
            ]);
        }

        $this->hizmet = Hizmet::create([
            'doktor_id' => $this->doktor->id,
            'ad' => 'Seans',
            'slug' => 'seans',
            'aciklama' => 'Test',
            'sure' => 30,
            'fiyat' => 1000,
            'aktif_mi' => true,
        ]);

        $this->hasta = Hasta::create([
            'ad' => 'Ayse',
            'soyad' => 'Yilmaz',
            'e_posta' => 'ayse@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '05559998877',
            'aktif_mi' => true,
        ]);
    }

    private function ayar(array $ek = []): void
    {
        RandevuAyari::create(array_merge([
            'doktor_id' => $this->doktor->id,
            'aktif_mi' => true,
            'en_erken_randevu_saati' => 2,
            'en_gec_randevu_gunu' => 30,
            'gunluk_maksimum_randevu' => 0,
        ], $ek));
    }

    private function randevu(Carbon $zaman): Randevu
    {
        return Randevu::create([
            'doktor_id' => $this->doktor->id,
            'hizmet_id' => $this->hizmet->id,
            'hasta_id' => $this->hasta->id,
            'ad' => $this->hasta->ad,
            'soyad' => $this->hasta->soyad,
            'telefon' => $this->hasta->telefon,
            'e_posta' => $this->hasta->e_posta,
            'tarih' => $zaman->toDateString(),
            'saat' => $zaman->format('H:i'),
            'durum' => 'onaylandi',
        ]);
    }

    private function servis(): AppointmentBookingService
    {
        return app(AppointmentBookingService::class);
    }

    // ------------------------------------------------------------ asıl hata

    /**
     * Bildirilen senaryo: saat 13:30, randevu 15:00'da (2 saatlik eşiğin
     * altında). Hekim 18:00'a taşıyor, sonra 15:00'a geri almak istiyor.
     */
    public function test_hekim_randevuyu_en_erken_esiginin_altina_geri_tasiyabilir(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-04 13:30:00')); // Cuma
        $this->ayar(['en_erken_randevu_saati' => 2]);

        $randevu = $this->randevu(Carbon::parse('2026-09-04 15:00'));

        // 1) Ileri tasima: 18:00 esigi gecer, sorunsuz olmali
        $this->servis()->reschedule($randevu, '2026-09-04', '18:00', hekimTarafindan: true);
        $this->assertSame('18:00', substr((string) $randevu->fresh()->saat, 0, 5));

        // 2) Geri tasima: 15:00 hala 1,5 saat sonra -> ESKIDEN 422 ile reddediliyordu
        $this->servis()->reschedule($randevu->fresh(), '2026-09-04', '15:00', hekimTarafindan: true);

        $this->assertSame(
            '15:00',
            substr((string) $randevu->fresh()->saat, 0, 5),
            'Hekim kendi randevusunu geri tasiyamadi.'
        );
    }

    /**
     * Online randevu alımı kapalıyken bile hekim kendi ajandasını
     * düzenleyebilmeli.
     */
    public function test_online_alim_kapaliyken_de_tasinabilir(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-04 09:00:00'));
        $this->ayar(['aktif_mi' => false]);

        $randevu = $this->randevu(Carbon::parse('2026-09-04 15:00'));

        $this->servis()->reschedule($randevu, '2026-09-04', '16:00', hekimTarafindan: true);

        $this->assertSame('16:00', substr((string) $randevu->fresh()->saat, 0, 5));
    }

    /**
     * `en_gec_randevu_gunu` de hekimi kısıtlamamalı.
     */
    public function test_en_gec_gun_siniri_hekimi_kisitlamaz(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-04 09:00:00'));
        $this->ayar(['en_gec_randevu_gunu' => 30]);

        $randevu = $this->randevu(Carbon::parse('2026-09-04 15:00'));
        $ileri = Carbon::parse('2026-09-04')->addDays(60);

        $this->servis()->reschedule($randevu, $ileri->toDateString(), '15:00', hekimTarafindan: true);

        $this->assertSame($ileri->toDateString(), $randevu->fresh()->tarih->toDateString());
    }

    /**
     * Günlük limit sayımı, TAŞINAN randevunun kendisini saymamalı.
     * Aksi halde limit 1 olan bir günde randevu hiç oynatılamaz.
     */
    public function test_gunluk_limit_tasinan_randevuyu_saymaz(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-04 09:00:00'));
        $this->ayar(['gunluk_maksimum_randevu' => 1]);

        $randevu = $this->randevu(Carbon::parse('2026-09-04 15:00'));

        $this->servis()->reschedule($randevu, '2026-09-04', '16:00', hekimTarafindan: true);

        $this->assertSame('16:00', substr((string) $randevu->fresh()->saat, 0, 5));
    }

    // ------------------------------------------- fiziksel kısıtlar korunmalı

    /**
     * Hekim modu politikayı atlar ama ÇAKIŞMAYI atlamaz: iki randevu
     * üst üste bindirilememeli.
     */
    public function test_cakisan_saate_tasinamaz(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-04 09:00:00'));
        $this->ayar();

        $randevu = $this->randevu(Carbon::parse('2026-09-04 15:00'));
        $this->randevu(Carbon::parse('2026-09-04 16:00'));   // dolu slot

        $this->expectException(InvalidArgumentException::class);
        $this->servis()->reschedule($randevu, '2026-09-04', '16:00', hekimTarafindan: true);
    }

    /**
     * İzin/blok konulmuş bir zamana da taşınamamalı.
     */
    public function test_izinli_zamana_tasinamaz(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-04 09:00:00'));
        $this->ayar();

        DoktorIzin::create([
            'doktor_id' => $this->doktor->id,
            'baslangic_zaman' => '2026-09-04 16:00:00',
            'bitis_zaman' => '2026-09-04 17:00:00',
            'aciklama' => 'Toplanti',
        ]);

        $randevu = $this->randevu(Carbon::parse('2026-09-04 15:00'));

        $this->expectException(InvalidArgumentException::class);
        $this->servis()->reschedule($randevu, '2026-09-04', '16:00', hekimTarafindan: true);
    }

    /**
     * Çalışma saatleri dışına taşınamamalı.
     */
    public function test_mesai_disina_tasinamaz(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-04 09:00:00'));
        $this->ayar();

        $randevu = $this->randevu(Carbon::parse('2026-09-04 15:00'));

        $this->expectException(InvalidArgumentException::class);
        $this->servis()->reschedule($randevu, '2026-09-04', '23:00', hekimTarafindan: true);
    }

    // ------------------------------------------------- hasta tarafı bozulmadı

    /**
     * Varsayılan (hasta) modda `en_erken_randevu_saati` HÂLÂ geçerli
     * olmalı; kural kaldırılmadı, yalnızca hekim tarafında atlanıyor.
     */
    public function test_hasta_modunda_en_erken_kurali_gecerli(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-04 13:30:00'));
        $this->ayar(['en_erken_randevu_saati' => 2]);

        $randevu = $this->randevu(Carbon::parse('2026-09-04 18:00'));

        $this->expectException(InvalidArgumentException::class);
        $this->servis()->reschedule($randevu, '2026-09-04', '15:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }
}
