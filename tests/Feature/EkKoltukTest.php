<?php

namespace Tests\Feature;

use App\Models\Doktor;
use App\Models\Klinik;
use App\Models\KlinikEkKoltukOdeme;
use App\Models\Paket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EkKoltukTest extends TestCase
{
    use RefreshDatabase;

    public function test_klinik_doktor_limit_calculation_with_extra_seats(): void
    {
        $paket = Paket::create([
            'ad' => 'Klinik Başlangıç Test',
            'tur' => 'klinik',
            'aciklama' => 'Test paket',
            'aylik_fiyat' => 1000,
            'yillik_fiyat' => 10000,
            'max_doktor_sayisi' => 3,
            'max_personel_sayisi' => 1,
            'ek_doktor_aylik_fiyat' => 1000,
            'ek_doktor_yillik_fiyat' => 10000,
            'aktif_mi' => true,
        ]);

        $sahip = Doktor::factory()->create();

        $klinik = Klinik::create([
            'ad' => 'Test Klinik',
            'slug' => 'test-klinik',
            'sahip_doktor_id' => $sahip->id,
            'paket_id' => $paket->id,
            'max_doktor_sayisi' => 3,
            'ek_doktor_koltuk_sayisi' => 0,
            'aktif_mi' => true,
        ]);

        $sahip->update(['klinik_id' => $klinik->id, 'klinik_rolu' => 'sahip']);

        // Dahil limit: 3, Ek: 0, Efektif: 3
        $this->assertEquals(3, $klinik->dahilDoktorLimiti());
        $this->assertEquals(3, $klinik->efektifDoktorLimiti());
        $this->assertFalse($klinik->doktorLimitiDolduMu());

        // Add 2 more doctors (total 3 including owner)
        Doktor::factory()->create(['klinik_id' => $klinik->id]);
        Doktor::factory()->create(['klinik_id' => $klinik->id]);

        $this->assertTrue($klinik->doktorLimitiDolduMu());

        // Add 2 extra seats
        $klinik->update(['ek_doktor_koltuk_sayisi' => 2]);
        $klinik->syncMaxDoktorSayisi();

        $this->assertEquals(3, $klinik->dahilDoktorLimiti());
        $this->assertEquals(5, $klinik->efektifDoktorLimiti());
        $this->assertEquals(5, $klinik->max_doktor_sayisi);
        $this->assertFalse($klinik->doktorLimitiDolduMu());
    }

    public function test_ek_koltuk_odeme_record_creation(): void
    {
        $sahip = Doktor::factory()->create();
        $klinik = Klinik::create([
            'ad' => 'Test Klinik 2',
            'slug' => 'test-klinik-2',
            'sahip_doktor_id' => $sahip->id,
            'max_doktor_sayisi' => 3,
            'ek_doktor_koltuk_sayisi' => 0,
            'aktif_mi' => true,
        ]);

        $odeme = KlinikEkKoltukOdeme::create([
            'klinik_id' => $klinik->id,
            'doktor_id' => $sahip->id,
            'adet' => 2,
            'periyot' => 'aylik',
            'birim_fiyat' => 1000.00,
            'tutar' => 2000.00,
            'durum' => 'beklemede',
            'merchant_oid' => 'EK1234567890TEST',
            'okudum_anladim_at' => now(),
        ]);

        $this->assertDatabaseHas('klinik_ek_koltuk_odemeleri', [
            'merchant_oid' => 'EK1234567890TEST',
            'durum' => 'beklemede',
            'adet' => 2,
            'tutar' => 2000.00,
        ]);
    }
}
