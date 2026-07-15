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
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doktor_id')->nullable()->constrained('doktorlar')->cascadeOnDelete();
            $table->foreignId('klinik_id')->nullable()->constrained('klinikler')->cascadeOnDelete();
            $table->string('api_key', 64)->unique();
            $table->string('secret_key', 64);
            $table->boolean('durum')->default(true);
            $table->json('yetkiler')->nullable(); // JSON list of allowed endpoints or actions
            $table->timestamp('son_kullanim')->nullable();
            $table->timestamps();

            $table->index('api_key');
            $table->index('durum');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
