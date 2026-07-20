<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            if (! Schema::hasColumn('doktorlar', 'abonelik_yenileme_kapali')) {
                $table->boolean('abonelik_yenileme_kapali')->default(false)->after('iyzico_subscription_status');
            }
            if (! Schema::hasColumn('doktorlar', 'abonelik_iptal_at')) {
                $table->timestamp('abonelik_iptal_at')->nullable()->after('abonelik_yenileme_kapali');
            }
            if (! Schema::hasColumn('doktorlar', 'abonelik_iptal_nedeni')) {
                $table->string('abonelik_iptal_nedeni', 255)->nullable()->after('abonelik_iptal_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            foreach (['abonelik_yenileme_kapali', 'abonelik_iptal_at', 'abonelik_iptal_nedeni'] as $col) {
                if (Schema::hasColumn('doktorlar', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
