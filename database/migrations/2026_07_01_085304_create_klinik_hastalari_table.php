<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('klinik_hastalari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klinik_id')->constrained('klinikler')->cascadeOnDelete();
            $table->foreignId('hasta_id')->constrained('hastalar')->cascadeOnDelete();
            $table->date('kayit_tarihi')->nullable();
            $table->text('notlar')->nullable();
            $table->timestamps();

            $table->unique(['klinik_id', 'hasta_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klinik_hastalari');
    }
};
