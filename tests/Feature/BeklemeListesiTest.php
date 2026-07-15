<?php

namespace Tests\Feature;

use App\Models\BeklemeListesi;
use App\Models\Doktor;
use App\Models\Hasta;
use App\Models\Hizmet;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Randevu;
use App\Services\BeklemeListesiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BeklemeListesiTest extends TestCase
{
    use RefreshDatabase;

    private Doktor $doktor;

    private Hizmet $hizmet;

    protected function setUp(): void
    {
        parent::setUp();

        $il = Il::create(['ad' => 'Istanbul', 'plaka' => '34']);
        $ilce = Ilce::create(['il_id' => $il->id, 'ad' => 'Sisli']);

        $this->doktor = Doktor::create([
            'ad_soyad' => 'Bekleme Hekim',
            'e_posta' => 'bekleme-hekim@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '05551112233',
            'tur' => 'bireysel',
            'unvan' => 'Dr.',
            'uzmanlik_alani' => 'Genel',
            'il_id' => $il->id,
            'ilce_id' => $ilce->id,
            'aktif_mi' => true,
        ]);

        $this->hizmet = Hizmet::create([
            'doktor_id' => $this->doktor->id,
            'ad' => 'Muayene',
            'slug' => 'muayene-bekleme',
            'aciklama' => 'Test',
            'sure' => 30,
            'fiyat' => 500,
            'aktif_mi' => true,
        ]);
    }

    public function test_guest_can_join_waitlist(): void
    {
        $response = $this->post(route('frontend.bekleme-listesi.katil'), [
            'doktor_id' => $this->doktor->id,
            'hizmet_id' => $this->hizmet->id,
            'ad' => 'Ayşe',
            'soyad' => 'Yılmaz',
            'telefon' => '05321234567',
            'e_posta' => 'ayse@example.com',
            'tercih_tarih' => now()->addDays(3)->toDateString(),
            'kvkk_onay' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('basarili');

        $this->assertDatabaseHas('bekleme_listesi', [
            'doktor_id' => $this->doktor->id,
            'ad' => 'Ayşe',
            'soyad' => 'Yılmaz',
            'durum' => 'beklemede',
        ]);
    }

    public function test_duplicate_waitlist_is_rejected(): void
    {
        BeklemeListesi::create([
            'doktor_id' => $this->doktor->id,
            'ad' => 'Ayşe',
            'soyad' => 'Yılmaz',
            'telefon' => '05321234567',
            'e_posta' => 'ayse@example.com',
            'durum' => 'beklemede',
        ]);

        $response = $this->from('/')->post(route('frontend.bekleme-listesi.katil'), [
            'doktor_id' => $this->doktor->id,
            'ad' => 'Ayşe',
            'soyad' => 'Yılmaz',
            'telefon' => '05321234567',
            'kvkk_onay' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('hata');
        $this->assertSame(1, BeklemeListesi::where('doktor_id', $this->doktor->id)->count());
    }

    public function test_cancel_notifies_waitlist(): void
    {
        Notification::fake();

        $kayit = BeklemeListesi::create([
            'doktor_id' => $this->doktor->id,
            'ad' => 'Mehmet',
            'soyad' => 'Demir',
            'telefon' => '05329876543',
            'e_posta' => 'mehmet@example.com',
            'tercih_tarih' => now()->addDay()->toDateString(),
            'durum' => 'beklemede',
        ]);

        $hasta = Hasta::create([
            'ad' => 'X',
            'soyad' => 'Y',
            'e_posta' => 'hasta-x@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '05000000000',
            'aktif_mi' => true,
        ]);

        $randevu = Randevu::create([
            'doktor_id' => $this->doktor->id,
            'hizmet_id' => $this->hizmet->id,
            'hasta_id' => $hasta->id,
            'tarih' => now()->addDay()->toDateString(),
            'saat' => '10:00',
            'durum' => 'onaylandi',
            'ad' => 'X',
            'soyad' => 'Y',
            'telefon' => '05000000000',
        ]);

        $count = app(BeklemeListesiService::class)->notifyOnSlotOpened($randevu);

        $this->assertSame(1, $count);
        $this->assertSame('bildirildi', $kayit->fresh()->durum);
    }

    public function test_doctor_can_list_waitlist(): void
    {
        BeklemeListesi::create([
            'doktor_id' => $this->doktor->id,
            'ad' => 'Zeynep',
            'soyad' => 'Kaya',
            'telefon' => '05321112233',
            'durum' => 'beklemede',
        ]);

        $response = $this->actingAs($this->doktor, 'doktor')
            ->get(route('hekim.randevu.bekleme-listesi'));

        $response->assertStatus(200);
        $response->assertSee('Zeynep');
        $response->assertSee('Bekleme Listesi');
    }
}
