<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_ayarlari', function (Blueprint $table) {
            if (! Schema::hasColumn('site_ayarlari', 'paytr_merchant_id')) {
                $table->string('paytr_merchant_id', 64)->nullable()->after('iyzico_base_url');
            }
            if (! Schema::hasColumn('site_ayarlari', 'paytr_merchant_key')) {
                $table->text('paytr_merchant_key')->nullable()->after('paytr_merchant_id');
            }
            if (! Schema::hasColumn('site_ayarlari', 'paytr_merchant_salt')) {
                $table->text('paytr_merchant_salt')->nullable()->after('paytr_merchant_key');
            }
            if (! Schema::hasColumn('site_ayarlari', 'paytr_test_mode')) {
                $table->boolean('paytr_test_mode')->default(true)->after('paytr_merchant_salt');
            }
        });

        Schema::table('uyelik_odemeleri', function (Blueprint $table) {
            if (! Schema::hasColumn('uyelik_odemeleri', 'merchant_oid')) {
                $table->string('merchant_oid', 64)->nullable()->unique()->after('havale_referans');
            }
            if (! Schema::hasColumn('uyelik_odemeleri', 'provider')) {
                $table->string('provider', 32)->nullable()->after('odeme_yontemi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_ayarlari', function (Blueprint $table) {
            foreach (['paytr_merchant_id', 'paytr_merchant_key', 'paytr_merchant_salt', 'paytr_test_mode'] as $col) {
                if (Schema::hasColumn('site_ayarlari', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('uyelik_odemeleri', function (Blueprint $table) {
            if (Schema::hasColumn('uyelik_odemeleri', 'merchant_oid')) {
                $table->dropColumn('merchant_oid');
            }
            if (Schema::hasColumn('uyelik_odemeleri', 'provider')) {
                $table->dropColumn('provider');
            }
        });
    }
};
