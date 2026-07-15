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
        Schema::create('doktor_calisma_saatleri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doktor_id')->constrained('doktorlar')->cascadeOnDelete();
            $table->tinyInteger('gun'); // 1 = Pazartesi, 7 = Pazar
            $table->boolean('aktif_mi')->default(true);
            $table->time('mesai_baslangic')->default('09:00');
            $table->time('mesai_bitis')->default('17:00');
            $table->boolean('ogle_arasi_aktif_mi')->default(true);
            $table->time('ogle_baslangic')->default('12:00');
            $table->time('ogle_bitis')->default('13:00');
            $table->timestamps();

            $table->unique(['doktor_id', 'gun']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doktor_calisma_saatleri');
    }
};
