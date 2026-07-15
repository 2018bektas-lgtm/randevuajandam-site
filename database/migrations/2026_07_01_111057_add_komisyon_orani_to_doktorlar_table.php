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
        Schema::table('doktorlar', function (Blueprint $blueprint) {
            $blueprint->decimal('komisyon_orani', 5, 2)->default(0)->after('klinik_aktif_mi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doktorlar', function (Blueprint $blueprint) {
            $blueprint->dropColumn('komisyon_orani');
        });
    }
};
