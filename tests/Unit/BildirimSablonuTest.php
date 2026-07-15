<?php

namespace Tests\Unit;

use App\Support\BildirimSablonu;
use PHPUnit\Framework\TestCase;

class BildirimSablonuTest extends TestCase
{
    public function test_render_replaces_placeholders(): void
    {
        $out = BildirimSablonu::render('Merhaba {hasta}, {tarih} {saat}', [
            'hasta' => 'Ayşe Yılmaz',
            'tarih' => '14 Temmuz 2026',
            'saat' => '10:30',
        ]);

        $this->assertSame('Merhaba Ayşe Yılmaz, 14 Temmuz 2026 10:30', $out);
    }

    public function test_clear_turkish_for_sms(): void
    {
        $this->assertSame(
            'Saglikli gunler dileriz.',
            BildirimSablonu::clearTurkish('Sağlıklı günler dileriz.')
        );
    }

    public function test_config_templates_exist(): void
    {
        // Unit test without full Laravel app boot: file must define keys
        $path = dirname(__DIR__, 2).'/config/bildirim_sablonlari.php';
        $this->assertFileExists($path);
        $cfg = require $path;
        $this->assertArrayHasKey('randevu_onaylandi', $cfg);
        $this->assertArrayHasKey('sms', $cfg['randevu_onaylandi']);
        $this->assertStringContainsString('{doktor}', $cfg['randevu_onaylandi']['sms']);
    }
}
