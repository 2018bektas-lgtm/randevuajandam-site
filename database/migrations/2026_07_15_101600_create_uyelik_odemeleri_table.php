<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uyelik_odemeleri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doktor_id')->constrained('doktorlar');
            $table->foreignId('paket_id')->constrained('paketler');
            $table->string('odeme_yontemi', 20);
            $table->string('odeme_periyodu', 10);
            $table->decimal('tutar', 10, 2);
            $table->string('durum', 20)->default('beklemede');
            $table->string('havale_referans', 100)->nullable();
            $table->json('kurulum_verisi')->nullable();
            $table->timestamp('onaylandi_at')->nullable();
            $table->foreignId('onaylayan_yonetici_id')->nullable()->constrained('yoneticiler');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uyelik_odemeleri');
    }
};
