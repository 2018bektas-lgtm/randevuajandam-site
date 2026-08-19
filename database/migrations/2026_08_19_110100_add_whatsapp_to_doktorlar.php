<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            if (! Schema::hasColumn('doktorlar', 'whatsapp_config')) {
                // Encrypted JSON — bireysel hekim kendi numarasini bagladiginda
                // (Model B); yoksa varsa klinigi, o da yoksa config('whatsapp.default').
                $table->text('whatsapp_config')->nullable()->after('sms_ek_kontor');
            }
            if (! Schema::hasColumn('doktorlar', 'whatsapp_baglandi_at')) {
                $table->timestamp('whatsapp_baglandi_at')->nullable()->after('whatsapp_config');
            }
            if (! Schema::hasColumn('doktorlar', 'whatsapp_kota')) {
                $table->unsignedInteger('whatsapp_kota')->default(500)->after('whatsapp_baglandi_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            foreach (['whatsapp_config', 'whatsapp_baglandi_at', 'whatsapp_kota'] as $col) {
                if (Schema::hasColumn('doktorlar', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
