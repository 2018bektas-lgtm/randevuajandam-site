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
        Schema::table('odemeler', function (Blueprint $table) {
            if (! Schema::hasColumn('odemeler', 'finans_kategori_id')) {
                $table->foreignId('finans_kategori_id')
                    ->nullable()
                    ->after('hizmet_id')
                    ->constrained('finans_kategoriler')
                    ->nullOnDelete();
            }
        });

        Schema::table('giderler', function (Blueprint $table) {
            if (! Schema::hasColumn('giderler', 'finans_kategori_id')) {
                $table->foreignId('finans_kategori_id')
                    ->nullable()
                    ->after('doktor_id')
                    ->constrained('finans_kategoriler')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('odemeler', function (Blueprint $table) {
            if (Schema::hasColumn('odemeler', 'finans_kategori_id')) {
                $table->dropConstrainedForeignId('finans_kategori_id');
            }
        });

        Schema::table('giderler', function (Blueprint $table) {
            if (Schema::hasColumn('giderler', 'finans_kategori_id')) {
                $table->dropConstrainedForeignId('finans_kategori_id');
            }
        });
    }
};
