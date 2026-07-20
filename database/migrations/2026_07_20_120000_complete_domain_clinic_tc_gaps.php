<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('domain_orders')) {
            Schema::table('domain_orders', function (Blueprint $table) {
                if (! Schema::hasColumn('domain_orders', 'dns_verified_at')) {
                    $table->timestamp('dns_verified_at')->nullable()->after('expires_at');
                }
                if (! Schema::hasColumn('domain_orders', 'dns_last_check_at')) {
                    $table->timestamp('dns_last_check_at')->nullable()->after('dns_verified_at');
                }
                if (! Schema::hasColumn('domain_orders', 'dns_check_message')) {
                    $table->string('dns_check_message', 500)->nullable()->after('dns_last_check_at');
                }
            });
        }

        if (Schema::hasTable('doktorlar') && ! Schema::hasColumn('doktorlar', 'tc_kimlik_no')) {
            Schema::table('doktorlar', function (Blueprint $table) {
                $table->string('tc_kimlik_no', 11)->nullable()->after('telefon');
            });
        }

        if (Schema::hasTable('klinikler')) {
            Schema::table('klinikler', function (Blueprint $table) {
                if (! Schema::hasColumn('klinikler', 'iyzico_subscription_reference_code')) {
                    $table->string('iyzico_subscription_reference_code')->nullable()->after('uyelik_bitis');
                }
                if (! Schema::hasColumn('klinikler', 'iyzico_subscription_status')) {
                    $table->string('iyzico_subscription_status')->nullable()->after('iyzico_subscription_reference_code');
                }
                if (! Schema::hasColumn('klinikler', 'abonelik_yenileme_kapali')) {
                    $table->boolean('abonelik_yenileme_kapali')->default(false)->after('iyzico_subscription_status');
                }
                if (! Schema::hasColumn('klinikler', 'abonelik_iptal_at')) {
                    $table->timestamp('abonelik_iptal_at')->nullable()->after('abonelik_yenileme_kapali');
                }
                if (! Schema::hasColumn('klinikler', 'abonelik_iptal_nedeni')) {
                    $table->string('abonelik_iptal_nedeni', 255)->nullable()->after('abonelik_iptal_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('domain_orders')) {
            Schema::table('domain_orders', function (Blueprint $table) {
                foreach (['dns_verified_at', 'dns_last_check_at', 'dns_check_message'] as $c) {
                    if (Schema::hasColumn('domain_orders', $c)) {
                        $table->dropColumn($c);
                    }
                }
            });
        }
        if (Schema::hasTable('doktorlar') && Schema::hasColumn('doktorlar', 'tc_kimlik_no')) {
            Schema::table('doktorlar', function (Blueprint $table) {
                $table->dropColumn('tc_kimlik_no');
            });
        }
        if (Schema::hasTable('klinikler')) {
            Schema::table('klinikler', function (Blueprint $table) {
                foreach ([
                    'iyzico_subscription_reference_code',
                    'iyzico_subscription_status',
                    'abonelik_yenileme_kapali',
                    'abonelik_iptal_at',
                    'abonelik_iptal_nedeni',
                ] as $c) {
                    if (Schema::hasColumn('klinikler', $c)) {
                        $table->dropColumn($c);
                    }
                }
            });
        }
    }
};
