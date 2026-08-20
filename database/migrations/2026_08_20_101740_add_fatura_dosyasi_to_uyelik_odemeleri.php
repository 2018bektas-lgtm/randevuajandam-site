<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Yönetici tarafından kesilen faturanın PDF/link kaydını tut.
 * Hekim bu alanı /hekim/faturalarim üzerinden görüp indirebilir.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uyelik_odemeleri', function (Blueprint $table) {
            if (! Schema::hasColumn('uyelik_odemeleri', 'fatura_url')) {
                $table->string('fatura_url', 500)->nullable()->after('fatura_bilgisi');
            }
            if (! Schema::hasColumn('uyelik_odemeleri', 'fatura_no')) {
                $table->string('fatura_no', 100)->nullable()->after('fatura_url');
            }
            if (! Schema::hasColumn('uyelik_odemeleri', 'fatura_kesildi_at')) {
                $table->timestamp('fatura_kesildi_at')->nullable()->after('fatura_no');
            }
        });
    }

    public function down(): void
    {
        Schema::table('uyelik_odemeleri', function (Blueprint $table) {
            foreach (['fatura_url', 'fatura_no', 'fatura_kesildi_at'] as $col) {
                if (Schema::hasColumn('uyelik_odemeleri', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
