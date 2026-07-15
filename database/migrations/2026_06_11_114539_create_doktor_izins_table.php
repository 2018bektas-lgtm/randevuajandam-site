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
        Schema::create('doktor_izinleri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doktor_id')->constrained('doktorlar')->cascadeOnDelete();
            $table->dateTime('baslangic_zaman');
            $table->dateTime('bitis_zaman');
            $table->string('aciklama')->nullable();
            $table->timestamps();

            $table->index(['doktor_id', 'baslangic_zaman', 'bitis_zaman']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doktor_izinleri');
    }
};
