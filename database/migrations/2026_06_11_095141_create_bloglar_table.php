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
        Schema::create('bloglar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doktor_id')->constrained('doktorlar')->cascadeOnDelete();
            $table->string('baslik');
            $table->string('slug')->unique();
            $table->text('icerik');
            $table->string('resim')->nullable();
            $table->string('meta_baslik')->nullable();
            $table->text('meta_aciklama')->nullable();
            $table->string('meta_anahtar_kelimeler')->nullable();
            $table->boolean('aktif_mi')->default(true);
            $table->integer('okunma_sayisi')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bloglar');
    }
};
