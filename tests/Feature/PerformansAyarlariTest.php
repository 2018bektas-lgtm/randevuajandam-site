<?php

namespace Tests\Feature;

use App\Models\SiteAyari;
use App\Services\RecaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Sıcak yollardaki sorgu sayısı ve index kapsamı.
 *
 * Regresyon P2-5: `hastalar.telefon` üzerinde index yoktu; her misafir
 * randevusunda findOrCreateGuestPatient() bu sütunu sorguladığı için tablo
 * büyüdükçe tam tarama yapılıyordu.
 *
 * Regresyon P2-6: RecaptchaService her doğrulamada `SiteAyari::query()->value()`
 * ile 2-3 önbelleksiz sorgu açıyordu; oysa modelde tam bu amaç için
 * `cached()` (request-scoped + 30 dk cache) metodu var.
 */
class PerformansAyarlariTest extends TestCase
{
    use RefreshDatabase;

    public function test_hastalar_telefon_sutunu_indexli(): void
    {
        $this->assertTrue(Schema::hasColumn('hastalar', 'telefon'));

        $indexler = collect(DB::select("PRAGMA index_list('hastalar')"))
            ->pluck('name')
            ->all();

        $this->assertContains(
            'idx_hastalar_telefon',
            $indexler,
            'hastalar.telefon index\'i yok — misafir randevusu tam tarama yapar.'
        );
    }

    public function test_recaptcha_dogrulamasi_tekrarli_sorgu_acmaz(): void
    {
        SiteAyari::create([
            'recaptcha_enabled' => true,
            'recaptcha_site_key' => '',
            'recaptcha_secret_key' => '',
        ]);
        SiteAyari::forgetCache();

        config([
            'recaptcha.enabled' => true,
            'recaptcha.secret_key' => '',
            'recaptcha.soft_fail_when_unconfigured' => true,
            'recaptcha.strict_actions' => [],
        ]);

        $service = app(RecaptchaService::class);

        // Önbelleği ısıt (ilk okuma bir sorgu açabilir)
        $service->verify(null, 'test_isinma');

        $sorgular = 0;
        DB::listen(function () use (&$sorgular) {
            $sorgular++;
        });

        // Aynı istek içinde üç doğrulama daha
        $service->verify(null, 'test_1');
        $service->verify(null, 'test_2');
        $service->verify(null, 'test_3');

        $this->assertSame(
            0,
            $sorgular,
            "reCAPTCHA dogrulamasi {$sorgular} ek DB sorgusu acti; SiteAyari::cached() kullanilmali."
        );
    }

    public function test_site_ayari_cached_ayni_istek_icinde_tek_sorgu(): void
    {
        SiteAyari::create(['recaptcha_enabled' => true]);
        SiteAyari::forgetCache();

        SiteAyari::cached();

        $sorgular = 0;
        DB::listen(function () use (&$sorgular) {
            $sorgular++;
        });

        SiteAyari::cached();
        SiteAyari::cached();

        $this->assertSame(0, $sorgular);
    }
}
