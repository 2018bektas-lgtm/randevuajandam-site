<?php

namespace Tests\Feature;

use App\Models\Doktor;
use App\Models\Paket;
use App\Models\ReferansDavet;
use App\Models\UyelikOdeme;
use App\Services\ReferansService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReferansSistemiTest extends TestCase
{
    use RefreshDatabase;

    public function test_indirim_ve_odul_ilk_ucretli_odemede(): void
    {
        $svc = app(ReferansService::class);

        $a = Doktor::create([
            'ad_soyad' => 'Davet Eden',
            'e_posta' => 'a-ref@test.com',
            'sifre' => Hash::make('x'),
            'telefon' => '05550000001',
            'tur' => 'bireysel',
            'aktif_mi' => true,
            'paket_id' => null,
            'uyelik_bitis' => now()->addDays(10),
            'platformda_gorunur' => true,
        ]);
        $kod = $svc->ensureKod($a);

        $paket = Paket::create([
            'ad' => 'Test Plus',
            'tur' => 'bireysel',
            'aciklama' => 't',
            'aylik_fiyat' => 1000,
            'yillik_fiyat' => 10000,
            'aktif_mi' => true,
            'ozellikler' => ['x'],
        ]);

        $b = Doktor::create([
            'ad_soyad' => 'Yeni Hekim',
            'e_posta' => 'b-ref@test.com',
            'sifre' => Hash::make('x'),
            'telefon' => '05550000002',
            'tur' => 'bireysel',
            'aktif_mi' => true,
            'platformda_gorunur' => false,
        ]);
        $svc->attachOnRegister($b, $kod);
        $b->refresh();

        $this->assertEquals($a->id, $b->davet_eden_id);

        $fiyat = $svc->indirimliTutar($b, 1000.0);
        $this->assertTrue($fiyat['indirim_uygulandi']);
        $this->assertEquals(15, $fiyat['indirim_yuzde']);
        $this->assertEquals(850.0, $fiyat['tutar']);

        $odeme = UyelikOdeme::create([
            'doktor_id' => $b->id,
            'paket_id' => $paket->id,
            'odeme_yontemi' => 'paytr',
            'provider' => 'paytr',
            'odeme_periyodu' => 'aylik',
            'tutar' => 850,
            'durum' => 'onaylandi',
            'onaylandi_at' => now(),
            'kurulum_verisi' => ['tutar_brut' => 1000],
        ]);

        $b->update([
            'paket_id' => $paket->id,
            'uyelik_bitis' => now()->addMonth(),
            'platformda_gorunur' => true,
        ]);

        $aBitOnce = $a->fresh()->uyelik_bitis->copy();
        $svc->odullendir($odeme);

        $davet = ReferansDavet::where('davet_edilen_id', $b->id)->first();
        $this->assertNotNull($davet);
        $this->assertEquals('odullendirildi', $davet->durum);
        $this->assertEquals(6, $davet->odul_gun_davet_eden); // 30 * 20%

        $a->refresh();
        $this->assertTrue($a->uyelik_bitis->gt($aBitOnce));
        $this->assertEquals(6, (int) $aBitOnce->diffInDays($a->uyelik_bitis));

        // İkinci ödül yok
        $svc->odullendir($odeme);
        $this->assertEquals(1, ReferansDavet::where('davet_edilen_id', $b->id)->where('durum', 'odullendirildi')->count());
    }

    public function test_ucretsiz_odeme_odul_yok(): void
    {
        $svc = app(ReferansService::class);
        $a = Doktor::create([
            'ad_soyad' => 'A',
            'e_posta' => 'a2@test.com',
            'sifre' => Hash::make('x'),
            'telefon' => '05550000003',
            'tur' => 'bireysel',
            'aktif_mi' => true,
            'uyelik_bitis' => now()->addMonth(),
        ]);
        $kod = $svc->ensureKod($a);
        $b = Doktor::create([
            'ad_soyad' => 'B',
            'e_posta' => 'b2@test.com',
            'sifre' => Hash::make('x'),
            'telefon' => '05550000004',
            'tur' => 'bireysel',
            'aktif_mi' => true,
        ]);
        $svc->attachOnRegister($b, $kod);

        $paket = Paket::create([
            'ad' => 'Free',
            'tur' => 'bireysel',
            'aciklama' => 't',
            'aylik_fiyat' => 0,
            'yillik_fiyat' => 0,
            'aktif_mi' => true,
            'ozellikler' => [],
        ]);

        $odeme = UyelikOdeme::create([
            'doktor_id' => $b->id,
            'paket_id' => $paket->id,
            'odeme_yontemi' => 'free',
            'provider' => 'free',
            'odeme_periyodu' => 'aylik',
            'tutar' => 0,
            'durum' => 'onaylandi',
            'onaylandi_at' => now(),
        ]);

        $bitOnce = $a->fresh()->uyelik_bitis->copy();
        $svc->odullendir($odeme);
        $a->refresh();
        $this->assertTrue($a->uyelik_bitis->equalTo($bitOnce));
        $this->assertNotEquals('odullendirildi', ReferansDavet::where('davet_edilen_id', $b->id)->value('durum'));
    }
}
