<?php

namespace Tests\Feature;

use App\Models\Brans;
use App\Models\Doktor;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Klinik;
use App\Models\KlinikDavetiye;
use App\Models\Paket;
use App\Models\Unvan;
use App\Notifications\KlinikDavetBildirimi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class KlinikDavetiyeTest extends TestCase
{
    use RefreshDatabase;

    private Klinik $klinik;

    private Doktor $owner;

    private Paket $paket;

    private Brans $brans;

    private Unvan $unvan;

    private Il $il;

    private Ilce $ilce;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->il = Il::create(['ad' => 'Bursa', 'plaka' => '16']);
        $this->ilce = Ilce::create(['il_id' => $this->il->id, 'ad' => 'Nilufer']);
        $this->brans = Brans::create(['ad' => 'Fizyoterapi']);
        $this->unvan = Unvan::create(['ad' => 'Uzm. Dr.']);

        $this->paket = Paket::create([
            'ad' => 'Klinik Profesyonel',
            'tur' => 'klinik',
            'aciklama' => 'Klinik Paketi',
            'aylik_fiyat' => 999.00,
            'yillik_fiyat' => 9990.00,
            'ozellikler' => ['Merkezi finans'],
            'max_doktor_sayisi' => 3,
            'max_personel_sayisi' => 5,
            'aktif_mi' => true,
        ]);

        $this->owner = Doktor::create([
            'ad_soyad' => 'Klinik Sahibi',
            'e_posta' => 'owner@klinik.com',
            'sifre' => bcrypt('Password123!'),
            'telefon' => '05551234567',
            'tur' => 'klinik',
            'klinik_rolu' => 'sahip',
            'aktif_mi' => true,
        ]);

        $this->klinik = Klinik::create([
            'ad' => 'Mega Klinik',
            'sahip_doktor_id' => $this->owner->id,
            'paket_id' => $this->paket->id,
            'il_id' => $this->il->id,
            'aktif_mi' => true,
        ]);

        $this->owner->update(['klinik_id' => $this->klinik->id]);
    }

    /**
     * Test clinic owner can invite a doctor.
     */
    public function test_clinic_owner_can_invite_doctor_successfully(): void
    {
        $response = $this->actingAs($this->owner, 'doktor')
            ->post(route('hekim.klinik.doktorlar.davet'), [
                'e_posta' => 'davetli@doktor.com',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('klinik_davetiyeleri', [
            'klinik_id' => $this->klinik->id,
            'davet_edilen_eposta' => 'davetli@doktor.com',
            'durum' => 'beklemede',
        ]);

        // Assert Notification was sent
        Notification::assertSentTo(
            new AnonymousNotifiable,
            KlinikDavetBildirimi::class,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routes['mail'] === 'davetli@doktor.com';
            }
        );
    }

    /**
     * Test clinic owner cannot exceed clinic package doctor limit.
     */
    public function test_owner_cannot_invite_beyond_doctor_limit(): void
    {
        // Limit is 3. Owner is already 1 doctor. Let's add 2 other doctors to fill the limit.
        for ($i = 1; $i <= 2; $i++) {
            $doktor = Doktor::create([
                'ad_soyad' => 'Klinik Doktora '.$i,
                'e_posta' => "doktor{$i}@klinik.com",
                'sifre' => bcrypt('Password123!'),
                'klinik_id' => $this->klinik->id,
                'klinik_rolu' => 'doktor',
                'aktif_mi' => true,
            ]);
        }

        $this->assertEquals(3, $this->klinik->doktorlar()->count());

        // Now try to invite another doctor
        $response = $this->actingAs($this->owner, 'doktor')
            ->post(route('hekim.klinik.doktorlar.davet'), [
                'e_posta' => 'extra@doktor.com',
            ]);

        $response->assertSessionHas('hata', 'Paketinizin hekim limitine ulaştınız. Limit arttırmak için lütfen paketinizi yükseltin.');
        $this->assertDatabaseMissing('klinik_davetiyeleri', [
            'davet_edilen_eposta' => 'extra@doktor.com',
        ]);
    }

    /**
     * Test guest invitation accept/reject page loads correctly.
     */
    public function test_invitation_page_renders_successfully(): void
    {
        $davetiye = KlinikDavetiye::create([
            'klinik_id' => $this->klinik->id,
            'davet_eden_id' => $this->owner->id,
            'davet_edilen_eposta' => 'test@doktor.com',
            'durum' => 'beklemede',
            'son_kullanma_tarihi' => now()->addDays(7),
        ]);

        $response = $this->get(route('frontend.hekim.klinik.davet.kabul', ['token' => $davetiye->token]));

        $response->assertStatus(200);
        $response->assertSee('Klinik Daveti');
        $response->assertSee('Mega Klinik');
        $response->assertSee('Ad Soyad'); // shows registration form since email is unregistered
    }

    /**
     * Test existing doctor accepts invitation.
     */
    public function test_existing_doctor_can_accept_invitation_successfully(): void
    {
        $invitedDoctor = Doktor::create([
            'ad_soyad' => 'Kayıtlı Doktor',
            'e_posta' => 'kayitli@doktor.com',
            'sifre' => bcrypt('Password123!'),
            'tur' => 'bireysel',
            'aktif_mi' => true,
        ]);

        $davetiye = KlinikDavetiye::create([
            'klinik_id' => $this->klinik->id,
            'davet_eden_id' => $this->owner->id,
            'davet_edilen_eposta' => 'kayitli@doktor.com',
            'davet_edilen_doktor_id' => $invitedDoctor->id,
            'durum' => 'beklemede',
            'son_kullanma_tarihi' => now()->addDays(7),
        ]);

        // Login as the invited doctor to accept
        $response = $this->actingAs($invitedDoctor, 'doktor')
            ->post(route('frontend.hekim.klinik.davet.kabul.post', ['token' => $davetiye->token]));

        $response->assertRedirect(route('hekim.panel'));

        $this->assertDatabaseHas('doktorlar', [
            'id' => $invitedDoctor->id,
            'klinik_id' => $this->klinik->id,
            'klinik_rolu' => 'doktor',
            'klinik_aktif_mi' => true,
        ]);

        $this->assertDatabaseHas('klinik_davetiyeleri', [
            'id' => $davetiye->id,
            'durum' => 'kabul_edildi',
        ]);
    }

    /**
     * Test unregistered doctor accepts invitation and registers.
     */
    public function test_unregistered_doctor_can_accept_and_register(): void
    {
        $davetiye = KlinikDavetiye::create([
            'klinik_id' => $this->klinik->id,
            'davet_eden_id' => $this->owner->id,
            'davet_edilen_eposta' => 'yeni@doktor.com',
            'durum' => 'beklemede',
            'son_kullanma_tarihi' => now()->addDays(7),
        ]);

        $response = $this->post(route('frontend.hekim.klinik.davet.kabul.post', ['token' => $davetiye->token]), [
            'ad_soyad' => 'Yeni Davet Edilen Doktor',
            'sifre' => 'Sifre123!',
            'sifre_confirmation' => 'Sifre123!',
            'telefon' => '0 (555) 987 65 43',
            'unvan' => 'Uzm. Dr.',
            'branslar' => [$this->brans->id],
            'il_id' => $this->il->id,
            'ilce_id' => $this->ilce->ad,
        ]);

        $response->assertRedirect(route('hekim.panel'));

        $this->assertDatabaseHas('doktorlar', [
            'ad_soyad' => 'Yeni Davet Edilen Doktor',
            'e_posta' => 'yeni@doktor.com',
            'klinik_id' => $this->klinik->id,
            'klinik_rolu' => 'doktor',
            'klinik_aktif_mi' => true,
        ]);

        $this->assertDatabaseHas('klinik_davetiyeleri', [
            'id' => $davetiye->id,
            'durum' => 'kabul_edildi',
        ]);

        // Auto login check
        $this->assertTrue(auth('doktor')->check());
        $this->assertEquals(auth('doktor')->user()->e_posta, 'yeni@doktor.com');
    }

    /**
     * Test rejecting invitation.
     */
    public function test_invitation_rejection(): void
    {
        $davetiye = KlinikDavetiye::create([
            'klinik_id' => $this->klinik->id,
            'davet_eden_id' => $this->owner->id,
            'davet_edilen_eposta' => 'reddeden@doktor.com',
            'durum' => 'beklemede',
            'son_kullanma_tarihi' => now()->addDays(7),
        ]);

        $response = $this->post(route('frontend.hekim.klinik.davet.reddet', ['token' => $davetiye->token]));

        $response->assertRedirect('/');

        $this->assertDatabaseHas('klinik_davetiyeleri', [
            'id' => $davetiye->id,
            'durum' => 'reddedildi',
        ]);
    }
}
