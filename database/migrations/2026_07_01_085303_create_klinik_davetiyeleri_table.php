<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('klinik_davetiyeleri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klinik_id')->constrained('klinikler')->cascadeOnDelete();
            $table->foreignId('davet_eden_id')->constrained('doktorlar')->cascadeOnDelete();
            $table->string('davet_edilen_eposta');
            $table->foreignId('davet_edilen_doktor_id')->nullable()->constrained('doktorlar')->nullOnDelete();
            $table->string('token')->unique();
            $table->string('durum')->default('beklemede');
            $table->timestamp('son_kullanma_tarihi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klinik_davetiyeleri');
    }
};
