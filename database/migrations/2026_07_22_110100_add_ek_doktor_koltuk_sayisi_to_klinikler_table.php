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
        Schema::table('klinikler', function (Blueprint $table) {
            $table->unsignedInteger('ek_doktor_koltuk_sayisi')->default(0)->after('max_doktor_sayisi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('klinikler', function (Blueprint $table) {
            $table->dropColumn('ek_doktor_koltuk_sayisi');
        });
    }
};
