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
        Schema::create('klinik_ek_koltuk_odemeleri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klinik_id')->constrained('klinikler')->cascadeOnDelete();
            $table->foreignId('doktor_id')->constrained('doktorlar')->cascadeOnDelete();
            $table->unsignedInteger('adet');
            $table->string('periyot');
            $table->decimal('birim_fiyat', 10, 2);
            $table->decimal('tutar', 10, 2);
            $table->string('durum')->default('beklemede');
            $table->string('merchant_oid')->unique()->nullable();
            $table->text('paytr_token')->nullable();
            $table->json('callback_payload')->nullable();
            $table->timestamp('uyelik_bitis_hizasi')->nullable();
            $table->timestamp('onaylandi_at')->nullable();
            $table->timestamp('okudum_anladim_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('klinik_ek_koltuk_odemeleri');
    }
};
