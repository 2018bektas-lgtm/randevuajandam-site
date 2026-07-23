<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('randevular', function (Blueprint $table) {
            if (Schema::hasColumn('randevular', 'durum')) {
                $table->index(['doktor_id', 'tarih', 'durum'], 'idx_randevular_doktor_tarih_durum');
            } else {
                $table->index(['doktor_id', 'tarih'], 'idx_randevular_doktor_tarih_durum');
            }
            $table->index(['hasta_id', 'tarih'], 'idx_randevular_hasta_tarih');
        });

        Schema::table('klinikler', function (Blueprint $table) {
            $table->index(['aktif_mi', 'paket_id'], 'idx_klinikler_aktif_paket');
        });

        if (Schema::hasColumn('doktorlar', 'durum')) {
            Schema::table('doktorlar', function (Blueprint $table) {
                $table->index(['klinik_id', 'durum'], 'idx_doktorlar_klinik_durum');
            });
        } else {
            Schema::table('doktorlar', function (Blueprint $table) {
                $table->index(['klinik_id'], 'idx_doktorlar_klinik_durum');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('randevular', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_randevular_doktor_tarih_durum');
            $table->dropIndexIfExists('idx_randevular_hasta_tarih');
        });

        Schema::table('klinikler', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_klinikler_aktif_paket');
        });

        Schema::table('doktorlar', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_doktorlar_klinik_durum');
        });
    }
};
