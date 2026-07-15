<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('klinik_personelleri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klinik_id')->constrained('klinikler')->cascadeOnDelete();
            $table->string('ad_soyad');
            $table->string('e_posta')->unique();
            $table->string('sifre');
            $table->string('telefon')->nullable();
            $table->string('rol')->default('sekreter');
            $table->json('yetkiler')->nullable();
            $table->boolean('sifre_degistirildi_mi')->default(false);
            $table->boolean('aktif_mi')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klinik_personelleri');
    }
};
