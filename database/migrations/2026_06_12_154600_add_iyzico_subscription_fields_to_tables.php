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
            $table->string('iyzico_plan_aylik')->nullable()->after('ozellikler');
            $table->string('iyzico_plan_yillik')->nullable()->after('iyzico_plan_aylik');
        });

        Schema::table('doktorlar', function (Blueprint $table) {
            $table->string('iyzico_subscription_reference_code')->nullable()->after('uyelik_bitis');
            $table->string('iyzico_subscription_status')->nullable()->after('iyzico_subscription_reference_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paketler', function (Blueprint $table) {
            $table->dropColumn(['iyzico_plan_aylik', 'iyzico_plan_yillik']);
        });

        Schema::table('doktorlar', function (Blueprint $table) {
            $table->dropColumn(['iyzico_subscription_reference_code', 'iyzico_subscription_status']);
        });
    }
};
