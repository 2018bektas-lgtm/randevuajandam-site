<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            if (! Schema::hasColumn('doktorlar', 'hasta_telefon')) {
                $table->string('hasta_telefon', 40)->nullable()->after('telefon');
            }
            if (! Schema::hasColumn('doktorlar', 'hasta_whatsapp')) {
                $table->string('hasta_whatsapp', 40)->nullable()->after('hasta_telefon');
            }
        });
    }

    public function down(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            if (Schema::hasColumn('doktorlar', 'hasta_whatsapp')) {
                $table->dropColumn('hasta_whatsapp');
            }
            if (Schema::hasColumn('doktorlar', 'hasta_telefon')) {
                $table->dropColumn('hasta_telefon');
            }
        });
    }
};
