<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kayıt sırasında seçilen paket niyeti (ödeme öncesi).
 * Akış: paket seç → kayıt → meslek onay → ödeme (aynı paket).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            if (! Schema::hasColumn('doktorlar', 'kayit_paket_id')) {
                $table->foreignId('kayit_paket_id')
                    ->nullable()
                    ->after('paket_id')
                    ->constrained('paketler')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('doktorlar', 'kayit_periyot')) {
                $table->string('kayit_periyot', 10)->nullable()->after('kayit_paket_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            if (Schema::hasColumn('doktorlar', 'kayit_paket_id')) {
                $table->dropConstrainedForeignId('kayit_paket_id');
            }
            if (Schema::hasColumn('doktorlar', 'kayit_periyot')) {
                $table->dropColumn('kayit_periyot');
            }
        });
    }
};
