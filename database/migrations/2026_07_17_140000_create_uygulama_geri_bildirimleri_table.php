<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('uygulama_geri_bildirimleri')) {
            return;
        }

        Schema::create('uygulama_geri_bildirimleri', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('yildiz')->nullable();
            $table->string('sebep', 2000)->nullable();
            $table->string('platform', 20)->nullable();
            $table->string('app_version', 40)->nullable();
            $table->json('onboarding_cevaplar')->nullable();
            $table->string('e_posta', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uygulama_geri_bildirimleri');
    }
};
