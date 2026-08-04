<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paketler', function (Blueprint $table) {
            if (! Schema::hasColumn('paketler', 'max_hizmet_sayisi')) {
                $table->unsignedInteger('max_hizmet_sayisi')->nullable()->after('max_randevu_sayisi');
            }
            if (! Schema::hasColumn('paketler', 'max_biyografi_karakter')) {
                $table->unsignedInteger('max_biyografi_karakter')->nullable()->after('max_hizmet_sayisi');
            }
            if (! Schema::hasColumn('paketler', 'max_profil_foto')) {
                $table->unsignedInteger('max_profil_foto')->nullable()->after('max_biyografi_karakter');
            }
        });

        Schema::table('doktorlar', function (Blueprint $table) {
            if (! Schema::hasColumn('doktorlar', 'son_giris_at')) {
                $table->timestamp('son_giris_at')->nullable()->after('updated_at');
            }
            if (! Schema::hasColumn('doktorlar', 'sms_kullanim_donem')) {
                $table->string('sms_kullanim_donem', 7)->nullable()->after('son_giris_at'); // YYYY-MM
            }
            if (! Schema::hasColumn('doktorlar', 'sms_kullanim_adet')) {
                $table->unsignedInteger('sms_kullanim_adet')->default(0)->after('sms_kullanim_donem');
            }
        });

        Schema::table('klinikler', function (Blueprint $table) {
            if (! Schema::hasColumn('klinikler', 'sms_kullanim_donem')) {
                $table->string('sms_kullanim_donem', 7)->nullable();
            }
            if (! Schema::hasColumn('klinikler', 'sms_kullanim_adet')) {
                $table->unsignedInteger('sms_kullanim_adet')->default(0);
            }
        });

        // gelmedi durumu (no-show)
        try {
            DB::statement("ALTER TABLE randevular MODIFY COLUMN durum ENUM('beklemede','onaylandi','iptal','tamamlandi','gelmedi') NOT NULL DEFAULT 'beklemede'");
        } catch (\Throwable $e) {
            // SQLite veya zaten güncel
        }

        // Eski WhatsApp özelliklerini pivot'tan temizle
        if (Schema::hasTable('paket_ozellikleri')) {
            $ids = DB::table('paket_ozellikleri')
                ->whereIn('kod', ['whatsapp_bildirim', 'destek_whatsapp'])
                ->pluck('id');
            if ($ids->isNotEmpty()) {
                DB::table('paket_ozellik_pivot')->whereIn('ozellik_id', $ids)->delete();
                DB::table('paket_ozellikleri')->whereIn('id', $ids)->delete();
            }
        }
    }

    public function down(): void
    {
        Schema::table('paketler', function (Blueprint $table) {
            foreach (['max_hizmet_sayisi', 'max_biyografi_karakter', 'max_profil_foto'] as $col) {
                if (Schema::hasColumn('paketler', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::table('doktorlar', function (Blueprint $table) {
            foreach (['son_giris_at', 'sms_kullanim_donem', 'sms_kullanim_adet'] as $col) {
                if (Schema::hasColumn('doktorlar', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::table('klinikler', function (Blueprint $table) {
            foreach (['sms_kullanim_donem', 'sms_kullanim_adet'] as $col) {
                if (Schema::hasColumn('klinikler', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
