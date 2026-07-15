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
        Schema::create('hizmetler', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doktor_id')->constrained('doktorlar')->cascadeOnDelete();
            $table->string('ad');
            $table->string('slug');
            $table->text('aciklama')->nullable();
            $table->string('resim')->nullable();
            $table->integer('sure'); // Hizmet süresi (dakika)
            $table->decimal('fiyat', 10, 2)->nullable(); // Hizmet fiyatı (Gizli tutulacak)
            $table->boolean('aktif_mi')->default(true);
            $table->string('meta_baslik')->nullable();
            $table->text('meta_aciklama')->nullable();
            $table->string('meta_anahtar_kelimeler')->nullable();
            $table->timestamps();

            $table->unique(['doktor_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hizmetler');
    }
};
