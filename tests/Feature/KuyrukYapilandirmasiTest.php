<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kuyruk yapilandirmasi.
 *
 * Regresyon: bildirimlerin 24'u ShouldQueue oldugu halde QUEUE_CONNECTION=sync
 * idi; bu durumda SMTP/SMS/WhatsApp cagrilari HTTP istegi icinde calisiyor ve
 * randevu formunu blokluyordu. Ayri bir daemon worker kurmak yerine mevcut
 * `schedule:run` cron'una `queue:work --stop-when-empty` baglandi.
 */
class KuyrukYapilandirmasiTest extends TestCase
{
    // jobs / failed_jobs tablolarini dogrulamak icin migration'lar gerekli
    use RefreshDatabase;

    /**
     * @return array<int, string>
     */
    private function zamanlanmisKomutlar(): array
    {
        return collect(app(Schedule::class)->events())
            ->map(fn ($event) => (string) $event->command)
            ->values()
            ->all();
    }

    public function test_kuyruk_isleyici_zamanlanmis(): void
    {
        $komutlar = $this->zamanlanmisKomutlar();

        $queueWork = collect($komutlar)->first(fn ($c) => str_contains($c, 'queue:work'));

        $this->assertNotNull($queueWork, 'Zamanlanmis queue:work komutu bulunamadi.');

        // Daemon degil: kuyruk bosalinca cikmali, aksi halde cron ust uste calisir
        $this->assertStringContainsString('--stop-when-empty', $queueWork);
        // Bir sonraki dakikaya sarkmamali
        $this->assertStringContainsString('--max-time=', $queueWork);
    }

    public function test_kuyruk_isleyici_her_dakika_ve_ust_uste_binmeden_calisir(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn ($e) => str_contains((string) $e->command, 'queue:work'));

        $this->assertNotNull($event);
        $this->assertSame('* * * * *', $event->expression);
        $this->assertTrue($event->withoutOverlapping, 'queue:work ust uste calisabilir durumda.');
    }

    public function test_basarisiz_isler_budaniyor(): void
    {
        $komutlar = $this->zamanlanmisKomutlar();

        $this->assertNotNull(
            collect($komutlar)->first(fn ($c) => str_contains($c, 'queue:prune-failed')),
            'queue:prune-failed zamanlanmamis; failed_jobs sonsuza kadar birikir.'
        );
    }

    public function test_kuyruk_tablolari_mevcut(): void
    {
        // database surucusu icin gerekli tablolar
        $this->assertTrue(\Schema::hasTable('jobs'), 'jobs tablosu yok.');
        $this->assertTrue(\Schema::hasTable('failed_jobs'), 'failed_jobs tablosu yok.');
    }

    public function test_ornek_env_sync_onermez(): void
    {
        $env = (string) file_get_contents(base_path('.env.example'));

        $this->assertMatchesRegularExpression(
            '/^QUEUE_CONNECTION=database$/m',
            $env,
            '.env.example hala sync oneriyor.'
        );
    }
}
