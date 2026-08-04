<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('doktorlar')) {
            return;
        }

        Schema::table('doktorlar', function (Blueprint $table) {
            if (! Schema::hasColumn('doktorlar', 'sms_gonderici_baslik')) {
                $table->string('sms_gonderici_baslik', 20)->nullable()->after('telefon');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('doktorlar')) {
            return;
        }

        Schema::table('doktorlar', function (Blueprint $table) {
            if (Schema::hasColumn('doktorlar', 'sms_gonderici_baslik')) {
                $table->dropColumn('sms_gonderici_baslik');
            }
        });
    }
};
