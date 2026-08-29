<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * hastalar.telefon üzerine index.
 *
 * NEDEN: Her misafir randevusunda AppointmentBookingService::
 * findOrCreateGuestPatient() hastayı telefona göre arıyor
 * (`where telefon = ? or telefon = ? or telefon like '%…'`). Bu sütunda
 * hiç index yoktu; tablo büyüdükçe her randevu oluşturma tam tarama
 * yapıyordu. Aynı sütun BeklemeListesiService ve HastaImportService
 * tarafından da eşitlik ile sorgulanıyor.
 *
 * Not: Sorgudaki `like '%...'` (baştan joker) bu index'i kullanamaz; index
 * yalnızca eşitlik dallarını hızlandırır. Sondaki 10 haneye göre arama
 * gerçekten sıcak bir yol hâline gelirse normalize edilmiş bir
 * `telefon_rakamlar` sütunu + index doğru çözüm olur.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('hastalar') || ! Schema::hasColumn('hastalar', 'telefon')) {
            return;
        }

        Schema::table('hastalar', function (Blueprint $table) {
            $table->index('telefon', 'idx_hastalar_telefon');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('hastalar')) {
            return;
        }

        Schema::table('hastalar', function (Blueprint $table) {
            $table->dropIndex('idx_hastalar_telefon');
        });
    }
};
