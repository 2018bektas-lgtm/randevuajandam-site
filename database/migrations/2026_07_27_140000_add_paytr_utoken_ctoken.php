<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PayTR Kart Saklama: utoken (kullanıcı) + ctoken (kart).
 * @see https://dev.paytr.com/direkt-api/kart-saklama-api/yeni-kart-ekleme
 * @see https://dev.paytr.com/direkt-api/kart-saklama-api/kayitli-kart-tekrarlayan-odeme
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('doktorlar')) {
            Schema::table('doktorlar', function (Blueprint $table) {
                if (! Schema::hasColumn('doktorlar', 'paytr_recurring_id')) {
                    $table->string('paytr_recurring_id', 128)->nullable();
                }
                if (! Schema::hasColumn('doktorlar', 'paytr_utoken')) {
                    $table->text('paytr_utoken')->nullable();
                }
                if (! Schema::hasColumn('doktorlar', 'paytr_ctoken')) {
                    $table->text('paytr_ctoken')->nullable();
                }
            });
        }

        if (Schema::hasTable('klinikler')) {
            Schema::table('klinikler', function (Blueprint $table) {
                if (! Schema::hasColumn('klinikler', 'paytr_recurring_id')) {
                    $table->string('paytr_recurring_id', 128)->nullable();
                }
                if (! Schema::hasColumn('klinikler', 'paytr_utoken')) {
                    $table->text('paytr_utoken')->nullable();
                }
                if (! Schema::hasColumn('klinikler', 'paytr_ctoken')) {
                    $table->text('paytr_ctoken')->nullable();
                }
            });
        }

        if (Schema::hasTable('uyelik_odemeleri')) {
            Schema::table('uyelik_odemeleri', function (Blueprint $table) {
                if (! Schema::hasColumn('uyelik_odemeleri', 'paytr_recurring_id')) {
                    $table->string('paytr_recurring_id', 128)->nullable();
                }
                if (! Schema::hasColumn('uyelik_odemeleri', 'paytr_utoken')) {
                    $table->text('paytr_utoken')->nullable();
                }
                if (! Schema::hasColumn('uyelik_odemeleri', 'paytr_ctoken')) {
                    $table->text('paytr_ctoken')->nullable();
                }
                if (! Schema::hasColumn('uyelik_odemeleri', 'otomatik_yenileme')) {
                    $table->boolean('otomatik_yenileme')->default(false);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('doktorlar')) {
            Schema::table('doktorlar', function (Blueprint $table) {
                foreach (['paytr_utoken', 'paytr_ctoken'] as $col) {
                    if (Schema::hasColumn('doktorlar', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
        if (Schema::hasTable('klinikler')) {
            Schema::table('klinikler', function (Blueprint $table) {
                foreach (['paytr_utoken', 'paytr_ctoken'] as $col) {
                    if (Schema::hasColumn('klinikler', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
        if (Schema::hasTable('uyelik_odemeleri')) {
            Schema::table('uyelik_odemeleri', function (Blueprint $table) {
                foreach (['paytr_utoken', 'paytr_ctoken'] as $col) {
                    if (Schema::hasColumn('uyelik_odemeleri', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
