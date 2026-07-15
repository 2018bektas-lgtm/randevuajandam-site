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
        Schema::create('finans_kategoriler', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doktor_id')->constrained('doktorlar')->cascadeOnDelete();
            $table->string('ad');
            $table->enum('tur', ['gelir', 'gider'])->default('gelir');
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index(['doktor_id', 'tur', 'aktif']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finans_kategoriler');
    }
};
