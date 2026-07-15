<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('klinik_web_siteleri')) {
            return;
        }

        Schema::create('klinik_web_siteleri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klinik_id')->constrained('klinikler')->cascadeOnDelete();
            $table->string('domain', 150)->unique();
            $table->string('tema', 50)->default('custom');
            $table->string('durum', 30)->default('aktif'); // aktif, beklemede, hata
            $table->string('hostinger_domain_id')->nullable();
            $table->text('hata_mesaji')->nullable();
            $table->timestamps();

            $table->unique('klinik_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klinik_web_siteleri');
    }
};
