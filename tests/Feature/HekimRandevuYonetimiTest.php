<?php

namespace Tests\Feature;

use App\Models\Brans;
use App\Models\Doktor;
use App\Models\Hasta;
use App\Models\Hizmet;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Randevu;
use App\Models\RandevuAyari;
use App\Models\SiteAyari;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HekimRandevuYonetimiTest extends TestCase
{
    use RefreshDatabase;

    private Doktor $doktor;

    private Hasta $hasta;

    private Hizmet $hizmet;

    protected function setUp(): void
    {
        parent::setUp();

        SiteAyari::create([
            'meta_baslik' => 'Randevu Ajandam',
            'meta_aciklama' => 'Test Aciklama',
            'meta_anahtar_kelimeler' => 'test, randevu',
            'meta_yazar' => 'Test Yazar',
        ]);

        $il = Il::create(['ad' => 'Istanbul', 'plaka' => '34']);
        $ilce = Ilce::create(['il_id' => $il->id, 'ad' => 'Sisli']);
        $brans = Brans::create(['ad' => 'Kardiyoloji']);

        $this->doktor = Doktor::create([
            'ad_soyad' => 'Test Doktor',
            'e_posta' => 'doktor@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '05551234567',
            'tur' => 'bireysel',
            'unvan' => 'Prof. Dr.',
            'uzmanlik_alani' => 'Kardiyoloji',
            'il_id' => $il->id,
            'ilce_id' => $ilce->id,
            'aktif_mi' => true,
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
            'ad' => 'Kardiyoloji Muayenesi',
            'slug' => 'kardiyoloji-muayenesi',
            'aciklama' => 'Detayli muayene.',
            'sure' => 30,
            'fiyat' => 1000.00,
            'aktif_mi' => true,
        ]);

        $this->hasta = Hasta::create([
            'ad' => 'Ahmet',
            'soyad' => 'Yilmaz',
            'e_posta' => 'ahmet@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '05559998877',
            'aktif_mi' => true,
        ]);
    }

    /**
     * Test doctor can view their calendar page.
     */
    public function test_doctor_can_view_calendar_page(): void
    {
        $response = $this->actingAs($this->doktor, 'doktor')
            ->get(route('hekim.randevu.takvim'));

        $response->assertStatus(200);
        $response->assertSee('Haftalık Randevu Takvimi');
    }

    /**
     * Test doctor can view pending requests.
     */
    public function test_doctor_can_view_pending_requests(): void
    {
        $response = $this->actingAs($this->doktor, 'doktor')
            ->get(route('hekim.randevu.talepler'));

        $response->assertStatus(200);
        $response->assertSee('Onay Bekleyen Randevu Talepleri');
    }

    /**
     * Test doctor can approve a pending appointment.
     */
    public function test_doctor_can_approve_appointment(): void
    {
        $randevu = Randevu::create([
            'doktor_id' => $this->doktor->id,
            'hizmet_id' => $this->hizmet->id,
            'hasta_id' => $this->hasta->id,
            'ad' => $this->hasta->ad,
            'soyad' => $this->hasta->soyad,
            'telefon' => $this->hasta->telefon,
            'e_posta' => $this->hasta->e_posta,
            'tarih' => date('Y-m-d', strtotime('+1 day')),
            'saat' => '10:00',
            'not' => 'Basim agriyor',
            'durum' => 'beklemede',
        ]);

        $response = $this->actingAs($this->doktor, 'doktor')
            ->post(route('hekim.randevu.durum-guncelle', $randevu->id), [
                'durum' => 'onaylandi',
                'hekim_notu' => 'Randevunuz onaylanmistir.',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('randevular', [
            'id' => $randevu->id,
            'durum' => 'onaylandi',
            'hekim_notu' => 'Randevunuz onaylanmistir.',
        ]);
    }

    /**
     * Test doctor can reject a pending appointment.
     */
    public function test_doctor_can_reject_appointment(): void
    {
        $randevu = Randevu::create([
            'doktor_id' => $this->doktor->id,
            'hizmet_id' => $this->hizmet->id,
            'hasta_id' => $this->hasta->id,
            'ad' => $this->hasta->ad,
            'soyad' => $this->hasta->soyad,
            'telefon' => $this->hasta->telefon,
            'e_posta' => $this->hasta->e_posta,
            'tarih' => date('Y-m-d', strtotime('+1 day')),
            'saat' => '10:00',
            'not' => 'Muayene olmak istiyorum',
            'durum' => 'beklemede',
        ]);

        $response = $this->actingAs($this->doktor, 'doktor')
            ->post(route('hekim.randevu.durum-guncelle', $randevu->id), [
                'durum' => 'iptal',
                'hekim_notu' => 'O tarihte ameliyatta olacagim.',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('randevular', [
            'id' => $randevu->id,
            'durum' => 'iptal',
            'hekim_notu' => 'O tarihte ameliyatta olacagim.',
        ]);
    }

    /**
     * Test doctor can view working hours page.
     */
    public function test_doctor_can_view_working_hours_page(): void
    {
        $response = $this->actingAs($this->doktor, 'doktor')
            ->get(route('hekim.randevu.calisma-saatleri'));

        $response->assertRedirect(route('hekim.randevu.ayarlar'));
        $response->assertSessionHas('active_tab', 'calisma-saatleri');
    }

    /**
     * Test doctor can update working hours.
     */
    public function test_doctor_can_update_working_hours(): void
    {
        $calismaSaatleri = $this->doktor->calismaSaatleri()->orderBy('gun')->get();
        $postData = ['saatler' => []];

        foreach ($calismaSaatleri as $cs) {
            $postData['saatler'][$cs->id] = [
                'gun' => $cs->gun,
                'aktif_mi' => '1',
                'mesai_baslangic' => '08:00',
                'mesai_bitis' => '16:00',
                'ogle_arasi_aktif_mi' => '1',
                'ogle_baslangic' => '12:00',
                'ogle_bitis' => '13:00',
            ];
        }

        $response = $this->actingAs($this->doktor, 'doktor')
            ->post(route('hekim.randevu.calisma-saatleri.post'), $postData);

        $response->assertRedirect();
        $this->assertDatabaseHas('doktor_calisma_saatleri', [
            'doktor_id' => $this->doktor->id,
            'gun' => 1,
            'aktif_mi' => true,
            'mesai_baslangic' => '08:00',
            'mesai_bitis' => '16:00',
        ]);
    }

    /**
     * Test doctor can view patients page.
     */
    public function test_doctor_can_view_patients_page(): void
    {
        Randevu::create([
            'doktor_id' => $this->doktor->id,
            'hizmet_id' => $this->hizmet->id,
            'hasta_id' => $this->hasta->id,
            'ad' => $this->hasta->ad,
            'soyad' => $this->hasta->soyad,
            'telefon' => $this->hasta->telefon,
            'e_posta' => $this->hasta->e_posta,
            'tarih' => date('Y-m-d', strtotime('+1 day')),
            'saat' => '10:00',
            'durum' => 'onaylandi',
        ]);

        $response = $this->actingAs($this->doktor, 'doktor')
            ->get(route('hekim.randevu.hastalar'));

        $response->assertStatus(200);
        $response->assertSee($this->hasta->ad);
        $response->assertSee('Kayıtlı Hastalarım');
    }

    /**
     * Test doctor can view settings page.
     */
    public function test_doctor_can_view_settings_page(): void
    {
        $response = $this->actingAs($this->doktor, 'doktor')
            ->get(route('hekim.randevu.ayarlar'));

        $response->assertStatus(200);
        $response->assertSee('Randevu Ayarları');
    }

    /**
     * Test doctor can update settings.
     */
    public function test_doctor_can_update_settings(): void
    {
        RandevuAyari::create([
            'doktor_id' => $this->doktor->id,
            'aktif_mi' => true,
            'randevu_onay_tipi' => 'manuel',
            'en_erken_randevu_saati' => 2,
            'en_gec_randevu_gunu' => 30,
            'randevu_periyodu' => 30,
            'randevu_iptal_aktif_mi' => true,
            'iptal_saat_limiti' => 24,
            'gunluk_maksimum_randevu' => 0,
            'email_bildirimleri' => true,
        ]);

        $response = $this->actingAs($this->doktor, 'doktor')
            ->post(route('hekim.randevu.ayarlar.post'), [
                'aktif_mi' => '1',
                'randevu_onay_tipi' => 'otomatik',
                'en_erken_randevu_saati' => 4,
                'en_gec_randevu_gunu' => 45,
                'randevu_periyodu' => 45,
                'randevu_iptal_aktif_mi' => '1',
                'iptal_saat_limiti' => 12,
                'gunluk_maksimum_randevu' => 5,
                'email_bildirimleri' => '1',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('randevu_ayarlari', [
            'doktor_id' => $this->doktor->id,
            'aktif_mi' => true,
            'randevu_onay_tipi' => 'otomatik',
            'en_erken_randevu_saati' => 4,
            'en_gec_randevu_gunu' => 45,
            'randevu_periyodu' => 45,
            'randevu_iptal_aktif_mi' => true,
            'iptal_saat_limiti' => 12,
            'gunluk_maksimum_randevu' => 5,
            'email_bildirimleri' => true,
        ]);
    }

    /**
     * Test doctor can add and delete leaves.
     */
    public function test_doctor_can_manage_leaves(): void
    {
        // Add leave
        $response = $this->actingAs($this->doktor, 'doktor')
            ->post(route('hekim.randevu.izin-ekle'), [
                'baslangic_tarih' => date('Y-m-d', strtotime('+2 days')),
                'baslangic_saat' => '09:00',
                'bitis_tarih' => date('Y-m-d', strtotime('+2 days')),
                'bitis_saat' => '17:00',
                'aciklama' => 'Yillik izin',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('doktor_izinleri', [
            'doktor_id' => $this->doktor->id,
            'aciklama' => 'Yillik izin',
        ]);

        $izin = $this->doktor->izinler()->first();

        // Delete leave
        $response = $this->actingAs($this->doktor, 'doktor')
            ->delete(route('hekim.randevu.izin-sil', $izin->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('doktor_izinleri', [
            'id' => $izin->id,
        ]);
    }

    /**
     * Test patient booking is automatically approved if doctor settings has otomatik approval.
     */
    public function test_patient_booking_auto_approved(): void
    {
        $this->doktor->calismaSaatleri()->update(['aktif_mi' => true]);

        RandevuAyari::create([
            'doktor_id' => $this->doktor->id,
            'aktif_mi' => true,
            'randevu_onay_tipi' => 'otomatik',
            'en_erken_randevu_saati' => 2,
            'en_gec_randevu_gunu' => 30,
        ]);

        $postData = [
            'doktor_id' => $this->doktor->id,
            'hizmet_id' => $this->hizmet->id,
            'tarih' => date('Y-m-d', strtotime('+5 days')),
            'saat' => '10:00',
            'not' => 'Baş ağrısı',
        ];

        $response = $this->actingAs($this->hasta, 'hasta')
            ->post(route('frontend.hasta.randevu.kaydet'), $postData);

        $response->assertRedirect();
        $this->assertDatabaseHas('randevular', [
            'doktor_id' => $this->doktor->id,
            'hizmet_id' => $this->hizmet->id,
            'hasta_id' => $this->hasta->id,
            'tarih' => $postData['tarih'].' 00:00:00',
            'saat' => '10:00',
            'durum' => 'onaylandi',
        ]);
    }

    /**
     * Test patient booking fails if doctor has disabled online booking.
     */
    public function test_patient_booking_fails_if_booking_disabled(): void
    {
        RandevuAyari::create([
            'doktor_id' => $this->doktor->id,
            'aktif_mi' => false,
            'randevu_onay_tipi' => 'manuel',
            'en_erken_randevu_saati' => 2,
            'en_gec_randevu_gunu' => 30,
        ]);

        $postData = [
            'doktor_id' => $this->doktor->id,
            'hizmet_id' => $this->hizmet->id,
            'tarih' => date('Y-m-d', strtotime('+1 day')),
            'saat' => '10:00',
        ];

        $response = $this->actingAs($this->hasta, 'hasta')
            ->post(route('frontend.hasta.randevu.kaydet'), $postData);

        $response->assertRedirect();
        $response->assertSessionHas('hata', 'Hekimimiz online randevu alımına geçici olarak kapalıdır.');
    }

    /**
     * Test patient booking fails if selected slot violates early notice settings limit.
     */
    public function test_patient_booking_fails_if_violates_early_limit(): void
    {
        RandevuAyari::create([
            'doktor_id' => $this->doktor->id,
            'aktif_mi' => true,
            'randevu_onay_tipi' => 'manuel',
            'en_erken_randevu_saati' => 48, // 48 hours minimum advance notice
            'en_gec_randevu_gunu' => 30,
        ]);

        // Attempting to book for tomorrow (24 hours notice) which is less than 48 hours
        $postData = [
            'doktor_id' => $this->doktor->id,
            'hizmet_id' => $this->hizmet->id,
            'tarih' => date('Y-m-d', strtotime('+1 day')),
            'saat' => '10:00',
        ];

        $response = $this->actingAs($this->hasta, 'hasta')
            ->post(route('frontend.hasta.randevu.kaydet'), $postData);

        $response->assertRedirect();
        $response->assertSessionHas('hata', 'En erken 48 saat sonrasına randevu alabilirsiniz.');
    }

    /**
     * Test patient booking fails if selected slot violates maximum days limit settings.
     */
    public function test_patient_booking_fails_if_violates_late_limit(): void
    {
        RandevuAyari::create([
            'doktor_id' => $this->doktor->id,
            'aktif_mi' => true,
            'randevu_onay_tipi' => 'manuel',
            'en_erken_randevu_saati' => 2,
            'en_gec_randevu_gunu' => 5, // 5 days max notice
        ]);

        // Attempting to book for 7 days in the future (violating 5 days limit)
        $postData = [
            'doktor_id' => $this->doktor->id,
            'hizmet_id' => $this->hizmet->id,
            'tarih' => date('Y-m-d', strtotime('+7 days')),
            'saat' => '10:00',
        ];

        $response = $this->actingAs($this->hasta, 'hasta')
            ->post(route('frontend.hasta.randevu.kaydet'), $postData);

        $response->assertRedirect();
        $response->assertSessionHas('hata', 'En fazla 5 gün sonrasına randevu alabilirsiniz.');
    }

    /**
     * Test patient cancellation fails if cancellation is disabled by doctor.
     */
    public function test_patient_cancellation_fails_if_disabled_by_doctor(): void
    {
        RandevuAyari::create([
            'doktor_id' => $this->doktor->id,
            'aktif_mi' => true,
            'randevu_iptal_aktif_mi' => false,
            'randevu_onay_tipi' => 'manuel',
            'en_erken_randevu_saati' => 2,
            'en_gec_randevu_gunu' => 30,
        ]);

        $randevu = Randevu::create([
            'doktor_id' => $this->doktor->id,
            'hizmet_id' => $this->hizmet->id,
            'hasta_id' => $this->hasta->id,
            'ad' => $this->hasta->ad,
            'soyad' => $this->hasta->soyad,
            'telefon' => $this->hasta->telefon,
            'e_posta' => $this->hasta->e_posta,
            'tarih' => date('Y-m-d', strtotime('+3 days')),
            'saat' => '10:00',
            'durum' => 'onaylandi',
        ]);

        $response = $this->actingAs($this->hasta, 'hasta')
            ->post(route('frontend.hasta.randevu.iptal', $randevu->id));

        $response->assertRedirect();
        $response->assertSessionHas('hata', 'Bu hekim için online randevu iptali kapatılmıştır.');
        $this->assertDatabaseHas('randevular', [
            'id' => $randevu->id,
            'durum' => 'onaylandi',
        ]);
    }

    /**
     * Test patient cancellation fails if within cancellation hour limit.
     */
    public function test_patient_cancellation_fails_if_within_hour_limit(): void
    {
        RandevuAyari::create([
            'doktor_id' => $this->doktor->id,
            'aktif_mi' => true,
            'randevu_iptal_aktif_mi' => true,
            'iptal_saat_limiti' => 24, // 24 hours cancellation limit
            'randevu_onay_tipi' => 'manuel',
            'en_erken_randevu_saati' => 2,
            'en_gec_randevu_gunu' => 30,
        ]);

        // Appointment is in 5 hours, which violates the 24 hour cancel limit
        $randevu = Randevu::create([
            'doktor_id' => $this->doktor->id,
            'hizmet_id' => $this->hizmet->id,
            'hasta_id' => $this->hasta->id,
            'ad' => $this->hasta->ad,
            'soyad' => $this->hasta->soyad,
            'telefon' => $this->hasta->telefon,
            'e_posta' => $this->hasta->e_posta,
            'tarih' => date('Y-m-d', strtotime('+5 hours')),
            'saat' => date('H:i', strtotime('+5 hours')),
            'durum' => 'onaylandi',
        ]);

        $response = $this->actingAs($this->hasta, 'hasta')
            ->post(route('frontend.hasta.randevu.iptal', $randevu->id));

        $response->assertRedirect();
        $response->assertSessionHas('hata', 'Randevu başlangıcına 24 saatten az süre kaldığı için iptal edemezsiniz.');
        $this->assertDatabaseHas('randevular', [
            'id' => $randevu->id,
            'durum' => 'onaylandi',
        ]);
    }

    /**
     * Test patient cancellation succeeds if cancellation is enabled and outside limit.
     */
    public function test_patient_cancellation_succeeds_if_allowed(): void
    {
        RandevuAyari::create([
            'doktor_id' => $this->doktor->id,
            'aktif_mi' => true,
            'randevu_iptal_aktif_mi' => true,
            'iptal_saat_limiti' => 24,
            'randevu_onay_tipi' => 'manuel',
            'en_erken_randevu_saati' => 2,
            'en_gec_randevu_gunu' => 30,
        ]);

        // Appointment is in 3 days, outside the 24 hour cancel limit
        $randevu = Randevu::create([
            'doktor_id' => $this->doktor->id,
            'hizmet_id' => $this->hizmet->id,
            'hasta_id' => $this->hasta->id,
            'ad' => $this->hasta->ad,
            'soyad' => $this->hasta->soyad,
            'telefon' => $this->hasta->telefon,
            'e_posta' => $this->hasta->e_posta,
            'tarih' => date('Y-m-d', strtotime('+3 days')),
            'saat' => '10:00',
            'durum' => 'onaylandi',
        ]);

        $response = $this->actingAs($this->hasta, 'hasta')
            ->post(route('frontend.hasta.randevu.iptal', $randevu->id));

        $response->assertRedirect();
        $response->assertSessionHas('basarili', 'Randevunuz başarıyla iptal edildi.');
        $this->assertDatabaseHas('randevular', [
            'id' => $randevu->id,
            'durum' => 'iptal',
        ]);
    }

    /**
     * Test patient booking fails if doctor reaches daily limit.
     */
    public function test_patient_booking_fails_if_daily_limit_reached(): void
    {
        RandevuAyari::create([
            'doktor_id' => $this->doktor->id,
            'aktif_mi' => true,
            'gunluk_maksimum_randevu' => 1, // Max 1 appointment per day
            'randevu_onay_tipi' => 'manuel',
            'en_erken_randevu_saati' => 2,
            'en_gec_randevu_gunu' => 30,
        ]);

        // Let's create an existing appointment for that day
        $tarih = date('Y-m-d', strtotime('+5 days'));
        Randevu::create([
            'doktor_id' => $this->doktor->id,
            'hizmet_id' => $this->hizmet->id,
            'hasta_id' => $this->hasta->id,
            'ad' => 'Other',
            'soyad' => 'Patient',
            'telefon' => '05559998877',
            'e_posta' => 'other@test.com',
            'tarih' => $tarih,
            'saat' => '09:00',
            'durum' => 'onaylandi',
        ]);

        // Attempting to book second slot on the same day
        $postData = [
            'doktor_id' => $this->doktor->id,
            'hizmet_id' => $this->hizmet->id,
            'tarih' => $tarih,
            'saat' => '10:00',
        ];

        $response = $this->actingAs($this->hasta, 'hasta')
            ->post(route('frontend.hasta.randevu.kaydet'), $postData);

        $response->assertRedirect();
        $response->assertSessionHas('hata', 'Hekimimizin bu gün için günlük randevu limiti dolmuştur. Lütfen başka bir gün seçin.');
    }

    /**
     * Test doctor can fetch quick lock slots.
     */
    public function test_doctor_can_fetch_quick_lock_slots(): void
    {
        $response = $this->actingAs($this->doktor, 'doktor')
            ->get(route('hekim.randevu.hizli-kapat-slotlar', [
                'tarih' => date('Y-m-d', strtotime('+1 day')),
            ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'aktif_mi',
            'periyot',
            'slots' => [
                '*' => [
                    'saat_baslangic',
                    'saat_bitis',
                    'saat_string',
                    'ogle_mi',
                    'kapali_mi',
                    'dolu_mu',
                ],
            ],
        ]);
    }

    /**
     * Test doctor can save quick lock selections.
     */
    public function test_doctor_can_save_quick_locks(): void
    {
        $this->doktor->calismaSaatleri()->update(['aktif_mi' => true]);
        $tarih = date('Y-m-d', strtotime('+4 days'));

        $response = $this->actingAs($this->doktor, 'doktor')
            ->post(route('hekim.randevu.hizli-kapat.post'), [
                'tarih' => $tarih,
                'saatler' => ['09:00', '10:00'],
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'basarili' => true,
        ]);

        $this->assertDatabaseHas('doktor_izinleri', [
            'doktor_id' => $this->doktor->id,
            'baslangic_zaman' => $tarih.' 09:00:00',
            'aciklama' => 'Hızlı Randevu Kapatma',
        ]);
    }

    /**
     * Test doctor can reschedule an appointment.
     */
    public function test_doctor_can_reschedule_appointment(): void
    {
        $originalDate = Carbon::parse('next monday')->toDateString();
        $newDate = Carbon::parse('next monday')->addDay()->toDateString();

        $randevu = Randevu::create([
            'doktor_id' => $this->doktor->id,
            'hizmet_id' => $this->hizmet->id,
            'hasta_id' => $this->hasta->id,
            'ad' => $this->hasta->ad,
            'soyad' => $this->hasta->soyad,
            'telefon' => $this->hasta->telefon,
            'e_posta' => $this->hasta->e_posta,
            'tarih' => $originalDate,
            'saat' => '10:00',
            'durum' => 'onaylandi',
        ]);

        $newTime = '11:00';

        $response = $this->actingAs($this->doktor, 'doktor')
            ->post(route('hekim.randevu.reschedule', $randevu->id), [
                'tarih' => $newDate,
                'saat' => $newTime,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('randevular', [
            'id' => $randevu->id,
            'tarih' => $newDate.' 00:00:00',
            'saat' => $newTime,
        ]);
    }

    /**
     * Test doctor can store appointment via ajax.
     */
    public function test_doctor_can_store_appointment_via_ajax(): void
    {
        $date = Carbon::parse('next monday')->toDateString();
        $time = '14:00';

        $response = $this->actingAs($this->doktor, 'doktor')
            ->post(route('hekim.randevu.store'), [
                'hizmet_id' => $this->hizmet->id,
                'danisan_id' => $this->hasta->id,
                'tarih' => $date,
                'saat' => $time,
                'aciklama' => 'Ajax randevu notu',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('randevular', [
            'doktor_id' => $this->doktor->id,
            'hasta_id' => $this->hasta->id,
            'hizmet_id' => $this->hizmet->id,
            'tarih' => $date.' 00:00:00',
            'saat' => $time,
            'not' => 'Ajax randevu notu',
            'durum' => 'onaylandi',
        ]);
    }

    /**
     * Test doctor can update appointment details.
     */
    public function test_doctor_can_update_appointment_via_ajax(): void
    {
        $randevu = Randevu::create([
            'doktor_id' => $this->doktor->id,
            'hizmet_id' => $this->hizmet->id,
            'hasta_id' => $this->hasta->id,
            'ad' => $this->hasta->ad,
            'soyad' => $this->hasta->soyad,
            'telefon' => $this->hasta->telefon,
            'e_posta' => $this->hasta->e_posta,
            'tarih' => Carbon::parse('next monday')->toDateString(),
            'saat' => '10:00',
            'durum' => 'onaylandi',
        ]);

        $response = $this->actingAs($this->doktor, 'doktor')
            ->put(route('hekim.randevu.update', $randevu->id), [
                'hizmet_id' => $this->hizmet->id,
                'aciklama' => 'Guncellenmis randevu aciklamasi',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('randevular', [
            'id' => $randevu->id,
            'hizmet_id' => $this->hizmet->id,
            'not' => 'Guncellenmis randevu aciklamasi',
        ]);
    }

    /**
     * Test doctor can delete appointment.
     */
    public function test_doctor_can_delete_appointment_via_ajax(): void
    {
        $randevu = Randevu::create([
            'doktor_id' => $this->doktor->id,
            'hizmet_id' => $this->hizmet->id,
            'hasta_id' => $this->hasta->id,
            'ad' => $this->hasta->ad,
            'soyad' => $this->hasta->soyad,
            'telefon' => $this->hasta->telefon,
            'e_posta' => $this->hasta->e_posta,
            'tarih' => Carbon::parse('next monday')->toDateString(),
            'saat' => '10:00',
            'durum' => 'onaylandi',
        ]);

        $response = $this->actingAs($this->doktor, 'doktor')
            ->delete(route('hekim.randevu.destroy', $randevu->id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertSoftDeleted('randevular', [
            'id' => $randevu->id,
        ]);
    }

    /**
     * Test doctor can search patients.
     */
    public function test_doctor_can_search_patients(): void
    {
        $response = $this->actingAs($this->doktor, 'doktor')
            ->get(route('hekim.randevu.hastalar-ara', ['q' => 'Ahmet']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'results' => [
                '*' => [
                    'id',
                    'text',
                ],
            ],
        ]);
    }

    /**
     * Test doctor can add a new patient.
     */
    public function test_doctor_can_add_patient_via_ajax(): void
    {
        $response = $this->actingAs($this->doktor, 'doktor')
            ->post(route('hekim.randevu.hasta-ekle'), [
                'name' => 'Caner Yildiz',
                'email' => 'caner@test.com',
                'telefon' => '05554443322',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('hastalar', [
            'ad' => 'Caner',
            'soyad' => 'Yildiz',
            'e_posta' => 'caner@test.com',
            'telefon' => '05554443322',
        ]);
    }
}
