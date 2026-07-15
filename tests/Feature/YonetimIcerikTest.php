<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Brans;
use App\Models\Doktor;
use App\Models\DoktorGaleri;
use App\Models\Faq;
use App\Models\Hizmet;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Yonetici;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class YonetimIcerikTest extends TestCase
{
    use RefreshDatabase;

    private Yonetici $yonetici;
    private Doktor $doktor;

    protected function setUp(): void
    {
        parent::setUp();

        // Create standard location and brand details
        $il = Il::create(['ad' => 'Bursa', 'plaka' => '16']);
        $ilce = Ilce::create(['il_id' => $il->id, 'ad' => 'Nilufer']);
        $brans = Brans::create(['ad' => 'Fizyoterapi']);

        // Create an administrator
        $this->yonetici = Yonetici::create([
            'ad_soyad' => 'Test Yonetici',
            'e_posta' => 'admin@test.com',
            'sifre' => Hash::make('sifre123'),
            'aktif_mi' => true,
        ]);

        // Create a doctor
        $this->doktor = Doktor::create([
            'ad_soyad' => 'Hasan Hekim',
            'e_posta' => 'hasan@test.com',
            'sifre' => Hash::make('sifre123'),
            'il_id' => $il->id,
            'ilce_id' => $ilce->id,
            'tur' => 'bireysel',
            'aktif_mi' => true,
            'uzmanlik_alani' => 'Fizyoterapi',
        ]);
        $this->doktor->branslar()->attach($brans->id);
    }

    /**
     * Test admin can view and manage services.
     */
    public function test_admin_can_manage_services(): void
    {
        $hizmet = Hizmet::create([
            'doktor_id' => $this->doktor->id,
            'ad' => 'Bel Ağrısı Tedavisi',
            'aciklama' => 'Bel ağrısı için fizyoterapi hizmeti.',
            'sure' => 45,
            'fiyat' => 500.00,
            'aktif_mi' => true,
        ]);

        // 1. View listing page
        $response = $this->actingAs($this->yonetici, 'yonetici')
            ->get(route('yonetim.hizmetler.index'));

        $response->assertStatus(200);
        $response->assertSee('Bel Ağrısı Tedavisi');
        $response->assertSee('Hasan Hekim');

        // 2. Toggle active status
        $response = $this->actingAs($this->yonetici, 'yonetici')
            ->post(route('yonetim.hizmetler.durum', $hizmet->id));

        $response->assertRedirect();
        $this->assertFalse($hizmet->fresh()->aktif_mi);

        // 3. Delete service
        $response = $this->actingAs($this->yonetici, 'yonetici')
            ->post(route('yonetim.hizmetler.sil', $hizmet->id));

        $response->assertRedirect();
        // Since SoftDeletes is used, the record should be soft deleted.
        $this->assertSoftDeleted('hizmetler', ['id' => $hizmet->id]);
    }

    /**
     * Test admin can view and manage blogs.
     */
    public function test_admin_can_manage_blogs(): void
    {
        $blog = Blog::create([
            'doktor_id' => $this->doktor->id,
            'baslik' => 'Fizyoterapinin Faydaları',
            'icerik' => 'Fizyoterapinin sağlık açısından faydaları ve egzersizler.',
            'aktif_mi' => true,
        ]);

        // 1. View listing page
        $response = $this->actingAs($this->yonetici, 'yonetici')
            ->get(route('yonetim.bloglar.index'));

        $response->assertStatus(200);
        $response->assertSee('Fizyoterapinin Faydaları');
        $response->assertSee('Hasan Hekim');

        // 2. Delete blog post
        $response = $this->actingAs($this->yonetici, 'yonetici')
            ->post(route('yonetim.bloglar.sil', $blog->id));

        $response->assertRedirect();
        $this->assertSoftDeleted('bloglar', ['id' => $blog->id]);
    }

    /**
     * Test admin can view and manage faqs.
     */
    public function test_admin_can_manage_faqs(): void
    {
        $faq = Faq::create([
            'doktor_id' => $this->doktor->id,
            'soru' => 'Tedavi süresi ne kadardır?',
            'cevap' => 'Tedavi süresi ortalama 45 dakikadır.',
            'aktif' => true,
            'sira' => 1,
        ]);

        // 1. View listing page
        $response = $this->actingAs($this->yonetici, 'yonetici')
            ->get(route('yonetim.faqs.index'));

        $response->assertStatus(200);
        $response->assertSee('Tedavi süresi ne kadardır?');
        $response->assertSee('Hasan Hekim');

        // 2. Toggle active status
        $response = $this->actingAs($this->yonetici, 'yonetici')
            ->post(route('yonetim.faqs.durum', $faq->id));

        $response->assertRedirect();
        $this->assertFalse($faq->fresh()->aktif);

        // 3. Delete FAQ
        $response = $this->actingAs($this->yonetici, 'yonetici')
            ->post(route('yonetim.faqs.sil', $faq->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('faqs', ['id' => $faq->id]);
    }

    /**
     * Test admin can view and manage galleries.
     */
    public function test_admin_can_manage_galleries(): void
    {
        $galeri = DoktorGaleri::create([
            'doktor_id' => $this->doktor->id,
            'resim_yolu' => 'uploads/galeri/test_clinic.jpg',
            'baslik' => 'Klinik Giriş',
            'sira' => 1,
        ]);

        // 1. View listing page
        $response = $this->actingAs($this->yonetici, 'yonetici')
            ->get(route('yonetim.galeriler.index'));

        $response->assertStatus(200);
        $response->assertSee('Klinik Giriş');
        $response->assertSee('Hasan Hekim');

        // 2. Delete gallery item
        $response = $this->actingAs($this->yonetici, 'yonetici')
            ->post(route('yonetim.galeriler.sil', $galeri->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('doktor_galerileri', ['id' => $galeri->id]);
    }
}
