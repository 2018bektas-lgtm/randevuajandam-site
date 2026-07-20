<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('paytr_callback_logs')) {
            Schema::create('paytr_callback_logs', function (Blueprint $table) {
                $table->id();
                $table->string('merchant_oid', 64)->nullable()->index();
                $table->foreignId('uyelik_odeme_id')->nullable()->constrained('uyelik_odemeleri')->nullOnDelete();
                $table->string('status', 32)->nullable();
                $table->string('total_amount', 32)->nullable();
                $table->boolean('hash_ok')->default(false);
                $table->boolean('processed')->default(false);
                $table->string('error_message', 500)->nullable();
                $table->json('raw_payload')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('uyelik_odemeleri', function (Blueprint $table) {
            if (! Schema::hasColumn('uyelik_odemeleri', 'fatura_durumu')) {
                $table->string('fatura_durumu', 32)->default('bekliyor')->after('durum');
            }
            if (! Schema::hasColumn('uyelik_odemeleri', 'callback_payload')) {
                $table->json('callback_payload')->nullable()->after('kurulum_verisi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('uyelik_odemeleri', function (Blueprint $table) {
            if (Schema::hasColumn('uyelik_odemeleri', 'callback_payload')) {
                $table->dropColumn('callback_payload');
            }
            if (Schema::hasColumn('uyelik_odemeleri', 'fatura_durumu')) {
                $table->dropColumn('fatura_durumu');
            }
        });

        Schema::dropIfExists('paytr_callback_logs');
    }
};
