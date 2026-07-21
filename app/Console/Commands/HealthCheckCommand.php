<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Canlı / staging ortam sağlık kontrolü.
 * php artisan ra:health
 */
class HealthCheckCommand extends Command
{
    protected $signature = 'ra:health {--strict : production kurallarını localde de uygula}';

    protected $description = 'Randevu Ajandam ortam sağlık kontrolü (DB, queue, PayTR, debug)';

    public function handle(): int
    {
        $strict = $this->option('strict') || app()->environment('production');
        $ok = true;

        $this->info('Randevu Ajandam — sağlık kontrolü');
        $this->line('APP_ENV='.config('app.env').'  APP_DEBUG='.(config('app.debug') ? 'true' : 'false'));
        $this->newLine();

        // DB
        try {
            DB::connection()->getPdo();
            $this->line('[OK] Veritabanı bağlantısı');
        } catch (\Throwable $e) {
            $this->error('[FAIL] Veritabanı: '.$e->getMessage());
            $ok = false;
        }

        // Critical tables
        foreach (['doktorlar', 'randevular', 'paketler', 'api_keys'] as $table) {
            if (Schema::hasTable($table)) {
                $this->line("[OK] Tablo: {$table}");
            } else {
                $this->error("[FAIL] Tablo yok: {$table} (migrate?)");
                $ok = false;
            }
        }

        // Queue
        $queue = config('queue.default');
        if ($strict && $queue === 'sync') {
            $this->warn('[WARN] QUEUE_CONNECTION=sync — hatırlatma/webhook job’ları arka planda kuyruklanmaz. Prod’da database veya redis kullanın.');
            $ok = false;
        } else {
            $this->line("[OK] Queue driver: {$queue}");
        }

        // Debug
        if ($strict && config('app.debug')) {
            $this->error('[FAIL] APP_DEBUG=true production için tehlikeli');
            $ok = false;
        } else {
            $this->line('[OK] APP_DEBUG='.(config('app.debug') ? 'true' : 'false'));
        }

        // Ödeme: yalnızca PayTR
        $driver = (string) config('services.payment.driver', 'paytr');
        $this->line('[OK] PAYMENT_DRIVER='.$driver);
        if ((bool) config('services.iyzico.enabled', false)) {
            $this->warn('[WARN] IYZICO_ENABLED=true — ürün kararı PayTR-only; kapatın.');
        } else {
            $this->line('[OK] iyzico kapalı (PayTR-only)');
        }

        $paytrId = (string) config('services.paytr.merchant_id', '');
        $paytrTest = (bool) config('services.paytr.test_mode', true);
        if ($strict && $paytrId === '') {
            $this->error('[FAIL] PAYTR_MERCHANT_ID boş — kartlı ödeme çalışmaz');
            $ok = false;
        } else {
            $this->line('[OK] PayTR merchant '.($paytrId !== '' ? 'tanımlı' : '(boş — local OK)'));
        }
        if ($strict && $paytrTest) {
            $this->error('[FAIL] PAYTR_TEST_MODE=true production’da olmamalı');
            $ok = false;
        } else {
            $this->line('[OK] PAYTR_TEST_MODE='.($paytrTest ? 'true' : 'false'));
        }

        $sms = (string) config('services.sms.driver', env('SMS_DRIVER', 'log'));
        if ($strict && in_array($sms, ['log', 'array', ''], true)) {
            $this->warn('[WARN] SMS_DRIVER=log — OTP gerçek SMS gitmez');
        }

        // APP_URL https in production
        $url = (string) config('app.url');
        if ($strict && ! str_starts_with($url, 'https://')) {
            $this->warn('[WARN] APP_URL https değil: '.$url);
        } else {
            $this->line('[OK] APP_URL='.$url);
        }

        $this->newLine();
        if ($ok) {
            $this->info('Sonuç: temel kontroller geçti.');
            $this->line('Hatırlatma: prod’da `queue:work` + cron `schedule:run` — docs/PROJE.md operasyon checklist');

            return self::SUCCESS;
        }

        $this->error('Sonuç: sorunlar var — yukarıyı düzeltin.');

        return self::FAILURE;
    }
}
