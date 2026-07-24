<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hastalar', function (Blueprint $table) {
            $table->boolean('bildirim_email')->default(true)->after('aktif_mi');
            $table->boolean('bildirim_sms')->default(true)->after('bildirim_email');
            $table->timestamp('silme_talep_at')->nullable()->after('bildirim_sms');
        });
    }

    public function down(): void
    {
        Schema::table('hastalar', function (Blueprint $table) {
            $table->dropColumn(['bildirim_email', 'bildirim_sms', 'silme_talep_at']);
        });
    }
};
