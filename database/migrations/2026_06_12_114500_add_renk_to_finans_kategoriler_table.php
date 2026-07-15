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
        Schema::table('finans_kategoriler', function (Blueprint $table) {
            $table->string('renk', 7)->default('#C96A2B')->after('tur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('finans_kategoriler', function (Blueprint $table) {
            $table->dropColumn('renk');
        });
    }
};
