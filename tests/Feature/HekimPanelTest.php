<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Brans;
use App\Models\Doktor;
use App\Models\Il;
use App\Models\Ilce;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HekimPanelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test doctor panel renders successfully with array-cast mezuniyet field.
     */
    public function test_hekim_panel_renders_successfully_with_mezuniyet_array(): void
    {
        $il = Il::create(['ad' => 'İstanbul', 'plaka' => '34']);
        $ilce = Ilce::create(['il_id' => $il->id, 'ad' => 'Şişli']);

        $doktor = Doktor::create([
            'ad_soyad' => 'Test Hekim',
            'e_posta' => 'hekim@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '05551234567',
            'il_id' => $il->id,
            'ilce_id' => $ilce->id,
            'tur' => 'bireysel',
            'unvan' => 'Prof. Dr.',
            'uzmanlik_alani' => 'Kardiyoloji',
            'mezuniyet' => [
                'Hacettepe Üniversitesi Tıp Fakültesi (2000)',
                'Uzmanlık - Ankara Üniversitesi (2005)',
            ],
            'biyografi' => 'Test biyografi.',
            'aktif_mi' => true,
        ]);

        $response = $this->actingAs($doktor, 'doktor')
            ->get(route('hekim.panel'));

        $response->assertStatus(200);
        $response->assertSee('Test Hekim');
        $response->assertSee('Hacettepe Üniversitesi Tıp Fakültesi (2000)');
        $response->assertSee('Uzmanlık - Ankara Üniversitesi (2005)');
    }

    /**
     * Test doctor panel renders successfully with empty/null mezuniyet field.
     */
    public function test_hekim_panel_renders_successfully_with_empty_mezuniyet(): void
    {
        $doktor = Doktor::create([
            'ad_soyad' => 'Test Hekim Boş',
            'e_posta' => 'hekim_bos@test.com',
            'sifre' => Hash::make('sifre123'),
            'tur' => 'bireysel',
            'mezuniyet' => null,
            'aktif_mi' => true,
        ]);

        $response = $this->actingAs($doktor, 'doktor')
            ->get(route('hekim.panel'));

        $response->assertStatus(200);
        $response->assertSee('Test Hekim Boş');
        $response->assertSee('Belirtilmedi');
    }

    /**
     * Test doctor list filtering works successfully by city, district, unvan, etc.
     */
    public function test_hekim_list_filtering_works_successfully(): void
    {
        $il1 = Il::create(['ad' => 'Istanbul', 'plaka' => '34']);
        $ilce1 = Ilce::create(['il_id' => $il1->id, 'ad' => 'Kadikoy']);

        $il2 = Il::create(['ad' => 'Izmir', 'plaka' => '35']);
        $ilce2 = Ilce::create(['il_id' => $il2->id, 'ad' => 'Konak']);

        $doktor1 = Doktor::create([
            'ad_soyad' => 'Ahmet Yurt',
            'e_posta' => 'ahmet_yurt@test.com',
            'sifre' => Hash::make('sifre123'),
            'il_id' => $il1->id,
            'ilce_id' => $ilce1->id,
            'tur' => 'bireysel',
            'unvan' => 'Prof. Dr.',
            'uzmanlik_alani' => 'Kardiyoloji',
            'aktif_mi' => true,
        ]);

        $doktor2 = Doktor::create([
            'ad_soyad' => 'Elif Demir',
            'e_posta' => 'elif_demir@test.com',
            'sifre' => Hash::make('sifre123'),
            'il_id' => $il2->id,
            'ilce_id' => $ilce2->id,
            'tur' => 'bireysel',
            'unvan' => 'Uzm. Dr.',
            'uzmanlik_alani' => 'Dermatoloji',
            'aktif_mi' => true,
        ]);

        // Filter by Istanbul
        $response = $this->get(route('frontend.hekimler', ['il' => $il1->id]));
        $response->assertStatus(200);
        $response->assertSee('Ahmet Yurt');
        $response->assertDontSee('Elif Demir');

        // Filter by Izmir and Konak
        $response = $this->get(route('frontend.hekimler', ['il' => $il2->id, 'ilce' => $ilce2->id]));
        $response->assertStatus(200);
        $response->assertSee('Elif Demir');
        $response->assertDontSee('Ahmet Yurt');

        // Filter by unvan
        $response = $this->get(route('frontend.hekimler', ['unvan' => 'Prof. Dr.']));
        $response->assertStatus(200);
        $response->assertSee('Ahmet Yurt');
        $response->assertDontSee('Elif Demir');
    }

    /**
     * Test doctor blog CRUD flow.
     */
    public function test_doctor_can_manage_blogs(): void
    {
        $il = Il::create(['ad' => 'Bursa', 'plaka' => '16']);
        $ilce = Ilce::create(['il_id' => $il->id, 'ad' => 'Nilufer']);
        $brans = Brans::create(['ad' => 'Fizyoterapi']);

        $doktor = Doktor::create([
            'ad_soyad' => 'Kemal Can',
            'e_posta' => 'kemal@test.com',
            'sifre' => Hash::make('sifre123'),
            'il_id' => $il->id,
            'ilce_id' => $ilce->id,
            'tur' => 'bireysel',
            'aktif_mi' => true,
            'uzmanlik_alani' => 'Fizyoterapi',
        ]);
        $doktor->branslar()->attach($brans->id);

        // 1. View empty blog list
        $response = $this->actingAs($doktor, 'doktor')
            ->get(route('hekim.bloglar.index'));

        $response->assertStatus(200);
        $response->assertSee('Henüz Blog Yazısı Eklemediniz');

        // 2. Create blog post
        $response = $this->actingAs($doktor, 'doktor')
            ->post(route('hekim.bloglar.store'), [
                'baslik' => 'Sağlıklı Beslenme Önerileri',
                'icerik' => 'Bu makalede sağlıklı beslenme yollarını anlatacağız.',
                'meta_baslik' => 'Sağlıklı Beslenme SEO',
                'meta_aciklama' => 'Sağlık beslenme açıklaması.',
                'meta_anahtar_kelimeler' => 'saglik, beslenme',
                'aktif_mi' => '1',
            ]);

        $response->assertRedirect(route('hekim.bloglar.index'));
        $this->assertDatabaseHas('bloglar', [
            'doktor_id' => $doktor->id,
            'baslik' => 'Sağlıklı Beslenme Önerileri',
            'aktif_mi' => true,
        ]);

        $blog = Blog::where('baslik', 'Sağlıklı Beslenme Önerileri')->first();
        $this->assertNotNull($blog->slug);

        // 3. View blog details publicly
        $response = $this->get($blog->url);
        $response->assertStatus(200);
        $response->assertSee('Sağlıklı Beslenme Önerileri');
        $response->assertSee('Bu makalede sağlıklı beslenme yollarını anlatacağız.');

        // Verify view count incremented
        $blog->refresh();
        $this->assertEquals(1, $blog->okunma_sayisi);

        // 4. Edit blog post
        $response = $this->actingAs($doktor, 'doktor')
            ->put(route('hekim.bloglar.update', $blog->id), [
                'baslik' => 'Güncellenmiş Beslenme Önerileri',
                'icerik' => 'Güncellenmiş içerik detayları.',
                'meta_baslik' => 'Güncellenmiş SEO',
                'meta_aciklama' => 'Güncellenmiş açıklama.',
                'aktif_mi' => '1',
            ]);

        $response->assertRedirect(route('hekim.bloglar.index'));
        $this->assertDatabaseHas('bloglar', [
            'id' => $blog->id,
            'baslik' => 'Güncellenmiş Beslenme Önerileri',
        ]);

        // 5. Delete blog post
        $response = $this->actingAs($doktor, 'doktor')
            ->delete(route('hekim.bloglar.destroy', $blog->id));

        $response->assertRedirect(route('hekim.bloglar.index'));
        $this->assertSoftDeleted('bloglar', [
            'id' => $blog->id,
        ]);
    }

    /**
     * Test doctor cannot edit another doctor's blog.
     */
    public function test_doctor_cannot_manage_other_doctors_blogs(): void
    {
        $doktor1 = Doktor::create([
            'ad_soyad' => 'Kemal Can 1',
            'e_posta' => 'kemal1@test.com',
            'sifre' => Hash::make('sifre123'),
            'tur' => 'bireysel',
            'aktif_mi' => true,
        ]);

        $doktor2 = Doktor::create([
            'ad_soyad' => 'Kemal Can 2',
            'e_posta' => 'kemal2@test.com',
            'sifre' => Hash::make('sifre123'),
            'tur' => 'bireysel',
            'aktif_mi' => true,
        ]);

        $blogOfDoktor2 = Blog::create([
            'doktor_id' => $doktor2->id,
            'baslik' => 'Doktor 2 Makalesi',
            'icerik' => 'Doktor 2 İçeriği',
            'aktif_mi' => true,
        ]);

        // Doktor 1 tries to edit Doktor 2's blog
        $response = $this->actingAs($doktor1, 'doktor')
            ->get(route('hekim.bloglar.edit', $blogOfDoktor2->id));

        $response->assertStatus(404);

        // Doktor 1 tries to update Doktor 2's blog
        $response = $this->actingAs($doktor1, 'doktor')
            ->put(route('hekim.bloglar.update', $blogOfDoktor2->id), [
                'baslik' => 'Hacklendi',
                'icerik' => 'Hacklendi',
            ]);

        $response->assertStatus(404);

        // Doktor 1 tries to delete Doktor 2's blog
        $response = $this->actingAs($doktor1, 'doktor')
            ->delete(route('hekim.bloglar.destroy', $blogOfDoktor2->id));

        $response->assertStatus(404);
    }

    /**
     * Test hierarchical SEO directory routes work successfully.
     */
    public function test_seo_directory_routes_work(): void
    {
        $il = Il::create(['ad' => 'Bursa', 'plaka' => '16']);
        $ilce = Ilce::create(['il_id' => $il->id, 'ad' => 'Nilufer']);
        $brans = Brans::create(['ad' => 'Fizyoterapi']);

        $doktor = Doktor::create([
            'ad_soyad' => 'Banu Can',
            'e_posta' => 'banu@test.com',
            'sifre' => Hash::make('sifre123'),
            'il_id' => $il->id,
            'ilce_id' => $ilce->id,
            'tur' => 'bireysel',
            'aktif_mi' => true,
            'uzmanlik_alani' => 'Fizyoterapi',
        ]);
        $doktor->branslar()->attach($brans->id);

        // 1. Test City Route
        $response = $this->get('/bursa');
        $response->assertStatus(200);
        $response->assertSee('Banu Can');

        // 2. Test City + District Route
        $response = $this->get('/bursa/nilufer');
        $response->assertStatus(200);
        $response->assertSee('Banu Can');

        // 3. Test City + District + Branch Route
        $response = $this->get('/bursa/nilufer/fizyoterapi');
        $response->assertStatus(200);
        $response->assertSee('Banu Can');
    }
}
