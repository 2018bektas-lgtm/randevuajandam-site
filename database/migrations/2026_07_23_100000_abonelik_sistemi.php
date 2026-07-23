<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // site_ayarlari: ödeme sağlayıcı seçimi
        Schema::table('site_ayarlari', function (Blueprint $table) {
            $table->string('odeme_saglayici', 20)->default('paytr')->after('paytr_test_mode');
            $table->boolean('iyzico_enabled')->default(false)->after('odeme_saglayici');
        });

        // uyelik_odemeleri: PayTR recurring token + yenileme zinciri
        Schema::table('uyelik_odemeleri', function (Blueprint $table) {
            $table->string('paytr_recurring_id', 128)->nullable()->after('merchant_oid');
            $table->unsignedBigInteger('yenileme_kaynak_odeme_id')->nullable()->after('paytr_recurring_id');
            $table->boolean('otomatik_yenileme')->default(false)->after('yenileme_kaynak_odeme_id');
        });

        // doktorlar: PayTR recurring token (abonelik yenileme için)
        Schema::table('doktorlar', function (Blueprint $table) {
            $table->string('paytr_recurring_id', 128)->nullable()->after('iyzico_subscription_status');
        });

        // klinikler: PayTR recurring token
        Schema::table('klinikler', function (Blueprint $table) {
            $table->string('paytr_recurring_id', 128)->nullable()->after('iyzico_subscription_status');
        });
    }

    public function down(): void
    {
        Schema::table('site_ayarlari', function (Blueprint $table) {
            $table->dropColumn(['odeme_saglayici', 'iyzico_enabled']);
        });
        Schema::table('uyelik_odemeleri', function (Blueprint $table) {
            $table->dropColumn(['paytr_recurring_id', 'yenileme_kaynak_odeme_id', 'otomatik_yenileme']);
        });
        Schema::table('doktorlar', function (Blueprint $table) {
            $table->dropColumn('paytr_recurring_id');
        });
        Schema::table('klinikler', function (Blueprint $table) {
            $table->dropColumn('paytr_recurring_id');
        });
    }
};
