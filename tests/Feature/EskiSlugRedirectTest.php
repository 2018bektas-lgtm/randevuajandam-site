<?php

namespace Tests\Feature;

use App\Models\Brans;
use App\Models\Doktor;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Klinik;
use App\Models\Paket;
use App\Models\PaketOzelligi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EskiSlugRedirectTest extends TestCase
{
    use RefreshDatabase;

    private function vitrinPaketi(): Paket
    {
        $paket = Paket::create([
            'ad' => 'Vitrin Test',
            'tur' => 'bireysel',
            'aciklama' => 'Test',
            'aylik_fiyat' => 0,
            'yillik_fiyat' => 0,
            'ozellikler' => [],
            'aktif_mi' => true,
        ]);
        $oz = PaketOzelligi::firstOrCreate(['kod' => 'profil_sayfasi'], ['ad' => 'Profil Sayfası']);
        $paket->sistemOzellikleri()->sync([$oz->id]);

        return $paket;
    }

    public function test_doktor_slug_only_uses_ad_soyad_not_unvan(): void
    {
        $il = Il::create(['ad' => 'Ankara', 'plaka' => '06']);
        $ilce = Ilce::create(['il_id' => $il->id, 'ad' => 'Cankaya']);
        $brans = Brans::create(['ad' => 'Kardiyoloji']);
        $paket = $this->vitrinPaketi();

        $doktor = Doktor::create([
            'ad_soyad' => 'Elif Kara',
            'e_posta' => 'elif-slug@test.com',
            'sifre' => Hash::make('sifre123'),
            'unvan' => 'Uzm. Dr.',
            'il_id' => $il->id,
            'ilce_id' => $ilce->id,
            'tur' => 'bireysel',
            'aktif_mi' => true,
            'paket_id' => $paket->id,
            'platformda_gorunur' => true,
            'meslek_dogrulama_durumu' => 'onaylandi',
        ]);
        $doktor->branslar()->attach($brans->id);

        // Slug yalnızca ad_soyad'dan üretilmeli — unvan girmez
        $this->assertSame('elif-kara', $doktor->slug);
    }

    public function test_doktor_ad_soyad_degistirilince_eski_slug_saklanir_ve_301_yonlendirir(): void
    {
        $il = Il::create(['ad' => 'Ankara', 'plaka' => '06']);
        $ilce = Ilce::create(['il_id' => $il->id, 'ad' => 'Cankaya']);
        $brans = Brans::create(['ad' => 'Kardiyoloji']);
        $paket = $this->vitrinPaketi();

        $doktor = Doktor::create([
            'ad_soyad' => 'Elif Kara',
            'e_posta' => 'elif-migrate@test.com',
            'sifre' => Hash::make('sifre123'),
            'il_id' => $il->id,
            'ilce_id' => $ilce->id,
            'tur' => 'bireysel',
            'aktif_mi' => true,
            'paket_id' => $paket->id,
            'platformda_gorunur' => true,
            'meslek_dogrulama_durumu' => 'onaylandi',
        ]);
        $doktor->branslar()->attach($brans->id);
        $eskiSlug = $doktor->slug;

        // Ad değişikliği: slug yeniden üretilir, eski_slug'a saklanır
        $doktor->update(['ad_soyad' => 'Elif Kara Yildiz']);
        $doktor->refresh();

        $this->assertSame('elif-kara-yildiz', $doktor->slug);
        $this->assertSame($eskiSlug, $doktor->eski_slug);

        // Eski URL 301 → yeni URL
        $eskiUrl = route('frontend.hekim.detay', [
            'il_slug' => $il->slug,
            'ilce_slug' => $ilce->slug,
            'brans_slug' => $brans->slug,
            'doctor_slug' => $eskiSlug,
        ]);

        $response = $this->get($eskiUrl);
        $response->assertStatus(301);
        $response->assertRedirect($doktor->profil_url);
    }

    public function test_doktor_hizmet_ve_blog_yolu_da_eski_slug_ile_301_doner(): void
    {
        $il = Il::create(['ad' => 'Ankara', 'plaka' => '06']);
        $ilce = Ilce::create(['il_id' => $il->id, 'ad' => 'Cankaya']);
        $brans = Brans::create(['ad' => 'Kardiyoloji']);
        $paket = $this->vitrinPaketi();

        $doktor = Doktor::create([
            'ad_soyad' => 'Ali Veli',
            'e_posta' => 'ali-veli@test.com',
            'sifre' => Hash::make('sifre123'),
            'il_id' => $il->id,
            'ilce_id' => $ilce->id,
            'tur' => 'bireysel',
            'aktif_mi' => true,
            'paket_id' => $paket->id,
            'platformda_gorunur' => true,
            'meslek_dogrulama_durumu' => 'onaylandi',
        ]);
        $doktor->branslar()->attach($brans->id);
        $eskiSlug = $doktor->slug;

        $doktor->update(['ad_soyad' => 'Ali Veli Kaya']);

        // /il/ilce/brans/eski/hizmet/x  →  301  →  /il/ilce/brans/yeni/hizmet/x
        $response = $this->get(route('frontend.hekim.hizmet.detay', [
            'il_slug' => $il->slug,
            'ilce_slug' => $ilce->slug,
            'brans_slug' => $brans->slug,
            'doctor_slug' => $eskiSlug,
            'hizmet_slug' => 'muayene',
        ]));
        $response->assertStatus(301);

        // /il/ilce/brans/eski/blog/y  →  301
        $response = $this->get(route('frontend.hekim.blog.detay', [
            'il_slug' => $il->slug,
            'ilce_slug' => $ilce->slug,
            'brans_slug' => $brans->slug,
            'doctor_slug' => $eskiSlug,
            'blog_slug' => 'yazi',
        ]));
        $response->assertStatus(301);
    }

    public function test_klinik_ad_degistirilince_eski_slug_saklanir_ve_301_yonlendirir(): void
    {
        $il = Il::create(['ad' => 'Istanbul', 'plaka' => '34']);
        $ilce = Ilce::create(['il_id' => $il->id, 'ad' => 'Sisli']);

        $ozKlinik = PaketOzelligi::firstOrCreate(['kod' => 'klinik_profil_sayfasi'], ['ad' => 'Klinik Profil']);
        $paketKlinik = Paket::create([
            'ad' => 'Klinik Test',
            'tur' => 'klinik',
            'aciklama' => 'Test',
            'aylik_fiyat' => 100,
            'yillik_fiyat' => 1000,
            'ozellikler' => [],
            'aktif_mi' => true,
        ]);
        $paketKlinik->sistemOzellikleri()->sync([$ozKlinik->id]);

        $sahipDoktor = Doktor::create([
            'ad_soyad' => 'Klinik Sahibi',
            'e_posta' => 'sahip@test.com',
            'sifre' => Hash::make('sifre123'),
            'il_id' => $il->id,
            'ilce_id' => $ilce->id,
            'tur' => 'klinik',
            'aktif_mi' => true,
        ]);

        $klinik = Klinik::create([
            'ad' => 'Yesim Kliniği',
            'sahip_doktor_id' => $sahipDoktor->id,
            'il_id' => $il->id,
            'ilce_id' => $ilce->id,
            'paket_id' => $paketKlinik->id,
            'aktif_mi' => true,
            'platformda_gorunur' => true,
        ]);
        $eskiKlinikSlug = $klinik->slug;

        // Klinik adı değişince slug yeniden üretilir, eski_slug'a alınır
        $klinik->update(['ad' => 'Yesim Sağlık Kliniği']);
        $klinik->refresh();

        $this->assertNotSame($eskiKlinikSlug, $klinik->slug);
        $this->assertSame($eskiKlinikSlug, $klinik->eski_slug);

        // Eski URL 301 dönmeli
        $response = $this->get(route('frontend.klinik.profil', [
            'il_slug' => $il->slug,
            'ilce_slug' => $ilce->slug,
            'klinik_slug' => $eskiKlinikSlug,
        ]));
        $response->assertStatus(301);
    }
}
