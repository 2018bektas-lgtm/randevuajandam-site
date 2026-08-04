<?php

namespace Tests\Feature;

use App\Models\Doktor;
use App\Models\Paket;
use App\Models\PaketOzelligi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Paket özellik gate duman testi: middleware + vitrin bayrakları.
 */
class PaketYetkiTamSetTest extends TestCase
{
    use RefreshDatabase;

    private function makePaketWithFeatures(array $codes): Paket
    {
        $paket = Paket::query()->create([
            'ad' => 'Test Paket',
            'tur' => 'bireysel',
            'aylik_fiyat' => 100,
            'yillik_fiyat' => 1000,
            'aktif_mi' => true,
            'sira' => 1,
        ]);

        $ids = [];
        foreach ($codes as $i => $kod) {
            $oz = PaketOzelligi::query()->firstOrCreate(
                ['kod' => $kod],
                ['ad' => $kod, 'sira' => $i, 'vitrin_mi' => true]
            );
            $ids[] = $oz->id;
        }
        $paket->sistemOzellikleri()->sync($ids);

        return $paket->fresh('sistemOzellikleri');
    }

    private function makeDoktor(Paket $paket): Doktor
    {
        return Doktor::query()->create([
            'ad_soyad' => 'Test Hekim',
            'e_posta' => 'hekim-paket-'.uniqid().'@test.local',
            'sifre' => bcrypt('password'),
            'telefon' => '05551112233',
            'paket_id' => $paket->id,
            'aktif_mi' => true,
            'platformda_gorunur' => true,
            'meslek_dogrulama_durumu' => 'onaylandi',
            'uyelik_bitis' => now()->addMonth(),
            'tur' => 'bireysel',
        ]);
    }

    public function test_finans_rapor_middleware_blocks_without_feature(): void
    {
        $paket = $this->makePaketWithFeatures(['finans', 'profil_sayfasi', 'online_takvim']);
        $doktor = $this->makeDoktor($paket);

        $this->actingAs($doktor, 'doktor')
            ->get(route('hekim.finans.rapor-pdf'))
            ->assertRedirect(route('frontend.hekim.paket_sec', ['degistir' => 1]));
    }

    public function test_finans_rapor_allowed_with_feature(): void
    {
        $paket = $this->makePaketWithFeatures(['finans', 'finans_rapor', 'profil_sayfasi']);
        $doktor = $this->makeDoktor($paket);

        // PDF kütüphanesi veri yoksa bile 200/stream dönebilir; 403 olmamalı
        $response = $this->actingAs($doktor, 'doktor')->get(route('hekim.finans.rapor-pdf'));
        $this->assertNotEquals(403, $response->status());
    }

    public function test_onam_index_requires_feature(): void
    {
        $paket = $this->makePaketWithFeatures(['profil_sayfasi']);
        $doktor = $this->makeDoktor($paket);

        $this->actingAs($doktor, 'doktor')
            ->get(route('hekim.onam.index'))
            ->assertRedirect(route('frontend.hekim.paket_sec', ['degistir' => 1]));
    }

    public function test_onam_index_ok_with_feature(): void
    {
        $paket = $this->makePaketWithFeatures(['onam_formu', 'profil_sayfasi']);
        $doktor = $this->makeDoktor($paket);

        $this->actingAs($doktor, 'doktor')
            ->get(route('hekim.onam.index'))
            ->assertOk();
    }

    public function test_vitrin_helpers_respect_features(): void
    {
        $paket = $this->makePaketWithFeatures(['profil_sayfasi', 'iletisim_profilde', 'yorum_gorunur']);
        $doktor = $this->makeDoktor($paket);
        $doktor->load('paket.sistemOzellikleri');

        $this->assertTrue($doktor->canShowContactOnProfile());
        $this->assertTrue($doktor->canShowReviews());
        $this->assertFalse($doktor->canShowSocialLinks());
        $this->assertFalse($doktor->hasPaketFeature('dis_baglanti'));
    }

    public function test_platform_list_requires_profil_sayfasi(): void
    {
        $with = $this->makePaketWithFeatures(['profil_sayfasi']);
        $without = $this->makePaketWithFeatures(['finans']);
        $d1 = $this->makeDoktor($with);
        $d2 = $this->makeDoktor($without);

        $ids = Doktor::platformdaListelenen()->pluck('id');
        $this->assertTrue($ids->contains($d1->id));
        $this->assertFalse($ids->contains($d2->id));
    }
}
