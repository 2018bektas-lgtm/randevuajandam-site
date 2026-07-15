<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('klinik_giderleri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klinik_id')->constrained('klinikler')->cascadeOnDelete();
            $table->string('kategori');
            $table->string('baslik');
            $table->decimal('tutar', 10, 2);
            $table->date('tarih');
            $table->text('aciklama')->nullable();
            $table->string('belge_yolu')->nullable();
            $table->boolean('tekrarli_mi')->default(false);
            $table->string('tekrar_periyodu')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klinik_giderleri');
    }
};
