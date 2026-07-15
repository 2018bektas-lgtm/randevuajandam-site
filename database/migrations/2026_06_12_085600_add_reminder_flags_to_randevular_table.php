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
            $table->boolean('hatirlatma_1gun_gonderildi')->default(false)->after('hekim_notu');
            $table->boolean('hatirlatma_2saat_gonderildi')->default(false)->after('hatirlatma_1gun_gonderildi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('randevular', function (Blueprint $table) {
            $table->dropColumn(['hatirlatma_1gun_gonderildi', 'hatirlatma_2saat_gonderildi']);
        });
    }
};
