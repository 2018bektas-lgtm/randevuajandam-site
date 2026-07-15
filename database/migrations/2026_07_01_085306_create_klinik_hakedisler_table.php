<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('klinik_hakedisler', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klinik_id')->constrained('klinikler')->cascadeOnDelete();
            $table->foreignId('doktor_id')->constrained('doktorlar')->cascadeOnDelete();
            $table->date('donem_baslangic');
            $table->date('donem_bitis');
            $table->decimal('toplam_gelir', 10, 2)->default(0);
            $table->decimal('komisyon_orani', 5, 2)->default(0);
            $table->decimal('komisyon_tutari', 10, 2)->default(0);
            $table->decimal('net_hakedis', 10, 2)->default(0);
            $table->string('durum')->default('hesaplandi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klinik_hakedisler');
    }
};
