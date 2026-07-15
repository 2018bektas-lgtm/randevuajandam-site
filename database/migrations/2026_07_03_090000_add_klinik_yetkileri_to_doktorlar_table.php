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
        Schema::table('doktorlar', function (Blueprint $table) {
            $table->json('klinik_yetkileri')->nullable()->after('klinik_rolu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            $table->dropColumn('klinik_yetkileri');
        });
    }
};
