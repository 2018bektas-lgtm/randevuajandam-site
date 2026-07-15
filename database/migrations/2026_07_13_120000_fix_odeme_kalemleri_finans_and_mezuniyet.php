<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Idempotent repair for databases that already ran empty/incomplete migrations.
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- odeme_kalemleri: ensure required columns exist ---
        if (Schema::hasTable('odeme_kalemleri')) {
            Schema::table('odeme_kalemleri', function (Blueprint $table) {
                if (! Schema::hasColumn('odeme_kalemleri', 'odeme_id')) {
                    $table->foreignId('odeme_id')
                        ->nullable()
                        ->constrained('odemeler')
                        ->cascadeOnDelete();
                }
                if (! Schema::hasColumn('odeme_kalemleri', 'tutar')) {
                    $table->decimal('tutar', 10, 2)->default(0);
                }
                if (! Schema::hasColumn('odeme_kalemleri', 'tarih')) {
                    $table->date('tarih')->nullable();
                }
                if (! Schema::hasColumn('odeme_kalemleri', 'odeme_yontemi')) {
                    $table->enum('odeme_yontemi', ['nakit', 'kredi_karti', 'havale', 'online'])->default('nakit');
                }
                if (! Schema::hasColumn('odeme_kalemleri', 'not')) {
                    $table->string('not')->nullable();
                }
            });
        }

        // --- finans_kategori_id on odemeler / giderler ---
        if (Schema::hasTable('odemeler') && ! Schema::hasColumn('odemeler', 'finans_kategori_id')) {
            Schema::table('odemeler', function (Blueprint $table) {
                $table->foreignId('finans_kategori_id')
                    ->nullable()
                    ->constrained('finans_kategoriler')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('giderler') && ! Schema::hasColumn('giderler', 'finans_kategori_id')) {
            Schema::table('giderler', function (Blueprint $table) {
                $table->foreignId('finans_kategori_id')
                    ->nullable()
                    ->constrained('finans_kategoriler')
                    ->nullOnDelete();
            });
        }

        // --- doktorlar.mezuniyet: string -> json (if still string) ---
        if (Schema::hasTable('doktorlar') && Schema::hasColumn('doktorlar', 'mezuniyet')) {
            $driver = Schema::getConnection()->getDriverName();

            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                // Convert non-JSON strings to JSON arrays where possible
                DB::statement("UPDATE doktorlar SET mezuniyet = JSON_ARRAY(mezuniyet) WHERE mezuniyet IS NOT NULL AND JSON_VALID(mezuniyet) = 0");
                DB::statement('ALTER TABLE doktorlar MODIFY mezuniyet JSON NULL');
            } elseif ($driver === 'pgsql') {
                DB::statement('ALTER TABLE doktorlar ALTER COLUMN mezuniyet TYPE json USING CASE WHEN mezuniyet IS NULL THEN NULL ELSE to_json(mezuniyet::text) END');
            } elseif ($driver === 'sqlite') {
                // SQLite has flexible typing; no hard type change required for array cast.
            }
        }
    }

    public function down(): void
    {
        // Non-destructive reverse: leave columns in place (safe for production).
    }
};
