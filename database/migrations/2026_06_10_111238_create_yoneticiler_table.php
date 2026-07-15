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
        Schema::create('yoneticiler', function (Blueprint $table) {
            $table->id();
            $table->string('ad_soyad');
            $table->string('e_posta')->unique();
            $table->string('sifre');
            $table->string('telefon')->nullable();
            $table->boolean('aktif_mi')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yoneticiler');
    }
};
