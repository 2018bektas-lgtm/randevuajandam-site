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

    protected $description = 'Randevu Ajandam ortam sağlık kontrolü (DB, queue, iyzico, debug)';

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

        // iyzico mock
        $mock = (bool) config('services.iyzico.allow_mock', false);
        if ($strict && $mock) {
            $this->error('[FAIL] IYZICO_ALLOW_MOCK=true production’da olmamalı');
            $ok = false;
        } else {
            $this->line('[OK] IYZICO_ALLOW_MOCK='.($mock ? 'true' : 'false'));
        }

        $iyzicoKey = (string) config('services.iyzico.api_key', '');
        if ($strict && $iyzicoKey === '') {
            $this->warn('[WARN] IYZICO_API_KEY boş');
        } else {
            $this->line('[OK] IYZICO_API_KEY '.($iyzicoKey !== '' ? 'tanımlı' : '(boş — local OK)'));
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
            $this->line('Hatırlatma: prod’da `queue:work` + cron `schedule:run` çalışmalı → CANLI_CIKIS_CHECKLIST.md');

            return self::SUCCESS;
        }

        $this->error('Sonuç: sorunlar var — yukarıyı düzeltin.');

        return self::FAILURE;
    }
}
