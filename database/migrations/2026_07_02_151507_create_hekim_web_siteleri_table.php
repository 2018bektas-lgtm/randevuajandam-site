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
        Schema::create('hekim_web_siteleri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doktor_id')->constrained('doktorlar')->onDelete('cascade');
            $table->string('domain')->unique();
            $table->string('tema'); // e.g. 'modern', 'minimalist', 'pediatrik'
            $table->string('durum')->default('beklemede'); // beklemede, kuruluyor, aktif, hata
            $table->string('hostinger_domain_id')->nullable();
            $table->text('hata_mesaji')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hekim_web_siteleri');
    }
};
