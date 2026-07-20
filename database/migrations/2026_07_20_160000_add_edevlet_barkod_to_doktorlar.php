<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            if (! Schema::hasColumn('doktorlar', 'edevlet_barkod')) {
                $table->string('edevlet_barkod', 64)->nullable()->after('diploma_no');
            }
        });
    }

    public function down(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            if (Schema::hasColumn('doktorlar', 'edevlet_barkod')) {
                $table->dropColumn('edevlet_barkod');
            }
        });
    }
};
