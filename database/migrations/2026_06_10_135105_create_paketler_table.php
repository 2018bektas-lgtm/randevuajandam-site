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
        Schema::create('paketler', function (Blueprint $table) {
            $table->id();
            $table->string('ad');
            $table->string('tur'); // 'bireysel' veya 'klinik'
            $table->text('aciklama')->nullable();
            $table->decimal('aylik_fiyat', 10, 2);
            $table->decimal('aylik_indirimli_fiyat', 10, 2)->nullable();
            $table->decimal('yillik_fiyat', 10, 2);
            $table->decimal('yillik_indirimli_fiyat', 10, 2)->nullable();
            $table->json('ozellikler')->nullable();
            $table->boolean('aktif_mi')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paketler');
    }
};
