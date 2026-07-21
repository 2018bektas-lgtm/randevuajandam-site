<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Operasyonel veriyi sıfırlar; sabit tanımlar ve yöneticiler kalır.
 *
 * Korunan: yoneticiler, branslar, unvanlar, iller, ilceler, paketler,
 * paket_ozellikleri (+ pivot), site_ayarlari, meslek_program_eslemeleri,
 * migrations, cache/jobs iskeleti.
 */
class PurgeOperationalDataCommand extends Command
{
    protected $signature = 'db:purge-operational
        {--force : Onay sormadan çalıştır}
        {--dry-run : Sadece silinecek tabloları listele}';

    protected $description = 'Hekim/hasta/klinik vb. operasyonel veriyi siler; branş/il/ilçe/ünvan/paket/yönetici kalır';

    /**
     * Silinecek tablolar (FK sırası önemli değil — FK checks kapalı).
     *
     * @return list<string>
     */
    protected function purgeTables(): array
    {
        return [
            // Referans / davet
            'referans_davetler',

            // Eğitim
            'egitim_basvurulari',
            'egitim_form_alanlari',
            'egitimler',

            // PayTR / üyelik ödeme
            'paytr_callback_logs',
            'uyelik_odemeleri',
            'domain_orders',

            // Meslek / belge
            'doktor_mezuniyet_belgeleri',
            'edevlet_dogrulama_loglari',
            'belge_erisim_loglari',

            // Randevu / hasta
            'bekleme_listesi',
            'yorumlar',
            'odeme_kalemleri',
            'odemeler',
            'randevular',
            'klinik_hastalari',
            'hastalar',
            'hasta_api_tokens',

            // Hekim içerik
            'bloglar',
            'hizmetler',
            'faqs',
            'doktor_galerileri',
            'doktor_calisma_saatleri',
            'doktor_izinleri',
            'randevu_ayarlari',
            'doktor_brans',
            'doktor_api_tokens',
            'doktor_device_tokens',

            // Finans
            'giderler',
            'finans_kategoriler',

            // Klinik
            'klinik_duyurulari',
            'klinik_giderleri',
            'klinik_hakedisler',
            'klinik_davetiyeleri',
            'klinik_personelleri',
            'personel_api_tokens',
            'klinik_web_siteleri',
            'klinikler',

            // Web site / API
            'hekim_web_siteleri',
            'webhook_endpoints',
            'api_keys',

            // Hekimler (en sonda)
            'doktorlar',

            // Bildirim / token / log
            'notifications',
            'uygulama_geri_bildirimleri',
            'password_reset_tokens',
            'sessions',
            'failed_jobs',
            'job_batches',
            'jobs',
            'cache',
            'cache_locks',
        ];
    }

    /**
     * Asla silinmez.
     *
     * @return list<string>
     */
    protected function keepTables(): array
    {
        return [
            'migrations',
            'yoneticiler',
            'branslar',
            'unvanlar',
            'iller',
            'ilceler',
            'paketler',
            'paket_ozellikleri',
            'paket_ozellik_pivot',
            'site_ayarlari',
            'meslek_program_eslemeleri',
            'users', // Laravel iskeleti (boş kalabilir)
        ];
    }

    public function handle(): int
    {
        $purge = array_values(array_filter(
            $this->purgeTables(),
            fn (string $t) => Schema::hasTable($t)
        ));
        $missing = array_values(array_filter(
            $this->purgeTables(),
            fn (string $t) => ! Schema::hasTable($t)
        ));

        $this->info('Korunacak tablolar: '.implode(', ', $this->keepTables()));
        $this->warn('Silinecek (truncate) tablo sayısı: '.count($purge));
        foreach ($purge as $t) {
            $count = DB::table($t)->count();
            $this->line("  - {$t} ({$count} satır)");
        }
        if ($missing !== []) {
            $this->comment('Yok sayılan (tabloda yok): '.implode(', ', $missing));
        }

        if ($this->option('dry-run')) {
            $this->info('Dry-run: hiçbir şey silinmedi.');

            return self::SUCCESS;
        }

        if (! $this->option('force')) {
            if (! $this->confirm('OPERASYONEL VERİ SİLİNECEK. Yöneticiler/branş/il/paket kalır. Devam?')) {
                $this->warn('İptal.');

                return self::FAILURE;
            }
        }

        $driver = DB::getDriverName();
        $this->info("DB driver: {$driver}");

        try {
            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
            } elseif ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF');
            }

            foreach ($purge as $table) {
                if ($driver === 'mysql') {
                    DB::table($table)->truncate();
                } elseif ($driver === 'sqlite') {
                    DB::table($table)->delete();
                    try {
                        DB::statement("DELETE FROM sqlite_sequence WHERE name = '{$table}'");
                    } catch (\Throwable) {
                        // yoksa geç
                    }
                } else {
                    DB::table($table)->delete();
                }
                $this->line("  OK truncate/delete: {$table}");
            }
        } finally {
            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } elseif ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON');
            }
        }

        // Soft-delete artıkları (doktorlar vb.) truncate ile gider; double-check
        $this->newLine();
        $this->info('Özet (korunan satır sayıları):');
        foreach (['yoneticiler', 'branslar', 'unvanlar', 'iller', 'ilceler', 'paketler', 'site_ayarlari'] as $keep) {
            if (Schema::hasTable($keep)) {
                $this->line("  {$keep}: ".DB::table($keep)->count());
            }
        }
        $this->info('Özet (silinmiş olmalı):');
        foreach (['doktorlar', 'hastalar', 'randevular', 'klinikler', 'uyelik_odemeleri'] as $gone) {
            if (Schema::hasTable($gone)) {
                $this->line("  {$gone}: ".DB::table($gone)->count());
            }
        }

        $this->newLine();
        $this->info('Tamam: operasyonel veri sıfırlandı.');

        return self::SUCCESS;
    }
}
