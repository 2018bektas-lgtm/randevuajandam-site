<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bireysel hekim - hasta havuzu (klinik_hastalari pattern kopyasi).
 * Bugüne kadar bireysel hekimde hasta ilişkisi yalnızca randevu üzerinden
 * kuruluyordu; toplu import ile hasta yaratıp sonra listelemek için pivot şart.
 *
 * Data migration: mevcut randevulardaki (doktor_id, hasta_id) benzersiz ciftlerini
 * kayit_tarihi = ilk randevu tarihi ile pivot'a yansıt.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('doktor_hastalari')) {
            Schema::create('doktor_hastalari', function (Blueprint $table) {
                $table->id();
                $table->foreignId('doktor_id')->constrained('doktorlar')->cascadeOnDelete();
                $table->foreignId('hasta_id')->constrained('hastalar')->cascadeOnDelete();
                $table->date('kayit_tarihi')->nullable();
                $table->text('notlar')->nullable();
                // Nereden geldiğinin izi: 'randevu' (otomatik), 'manuel' (form), 'toplu' (excel)
                $table->string('kaynak', 20)->default('manuel');
                $table->timestamps();

                $table->unique(['doktor_id', 'hasta_id']);
                $table->index(['doktor_id', 'kayit_tarihi']);
            });
        }

        // Data migration: mevcut randevu-hasta çiftlerini pivot'a taşı (idempotent)
        try {
            DB::statement(<<<'SQL'
                INSERT INTO doktor_hastalari (doktor_id, hasta_id, kayit_tarihi, kaynak, created_at, updated_at)
                SELECT r.doktor_id,
                       r.hasta_id,
                       MIN(r.tarih) AS kayit_tarihi,
                       'randevu' AS kaynak,
                       NOW(), NOW()
                FROM randevular r
                WHERE r.hasta_id IS NOT NULL AND r.doktor_id IS NOT NULL AND r.deleted_at IS NULL
                GROUP BY r.doktor_id, r.hasta_id
                ON DUPLICATE KEY UPDATE kayit_tarihi = doktor_hastalari.kayit_tarihi
            SQL);
        } catch (\Throwable $e) {
            // SQLite testte "ON DUPLICATE KEY" desteklemez; INSERT OR IGNORE varyantı
            DB::statement(<<<'SQL'
                INSERT OR IGNORE INTO doktor_hastalari (doktor_id, hasta_id, kayit_tarihi, kaynak, created_at, updated_at)
                SELECT r.doktor_id,
                       r.hasta_id,
                       MIN(r.tarih) AS kayit_tarihi,
                       'randevu' AS kaynak,
                       CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                FROM randevular r
                WHERE r.hasta_id IS NOT NULL AND r.doktor_id IS NOT NULL AND r.deleted_at IS NULL
                GROUP BY r.doktor_id, r.hasta_id
            SQL);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('doktor_hastalari');
    }
};
