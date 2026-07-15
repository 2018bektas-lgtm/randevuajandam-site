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
        Schema::table('paketler', function (Blueprint $table) {
            $table->integer('max_hasta_sayisi')->nullable()->after('max_personel_sayisi');
            $table->integer('max_randevu_sayisi')->nullable()->after('max_hasta_sayisi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paketler', function (Blueprint $table) {
            $table->dropColumn(['max_hasta_sayisi', 'max_randevu_sayisi']);
        });
    }
};
