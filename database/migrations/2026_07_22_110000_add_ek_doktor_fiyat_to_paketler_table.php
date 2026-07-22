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
            $table->decimal('ek_doktor_aylik_fiyat', 10, 2)->nullable()->default(null)->after('yillik_indirimli_fiyat');
            $table->decimal('ek_doktor_yillik_fiyat', 10, 2)->nullable()->default(null)->after('ek_doktor_aylik_fiyat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paketler', function (Blueprint $table) {
            $table->dropColumn(['ek_doktor_aylik_fiyat', 'ek_doktor_yillik_fiyat']);
        });
    }
};
