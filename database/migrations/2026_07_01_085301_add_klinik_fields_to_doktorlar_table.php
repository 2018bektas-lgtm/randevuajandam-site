<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            $table->foreignId('klinik_id')->nullable()->after('paket_id')->constrained('klinikler')->nullOnDelete();
            $table->string('klinik_rolu')->nullable()->after('klinik_id');
            $table->timestamp('klinik_katilma_tarihi')->nullable()->after('klinik_rolu');
            $table->boolean('klinik_aktif_mi')->nullable()->after('klinik_katilma_tarihi');
        });
    }

    public function down(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            $table->dropConstrainedForeignId('klinik_id');
            $table->dropColumn(['klinik_rolu', 'klinik_katilma_tarihi', 'klinik_aktif_mi']);
        });
    }
};
