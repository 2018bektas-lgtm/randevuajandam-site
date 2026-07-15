<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('klinikler', function (Blueprint $table) {
            $table->id();
            $table->string('ad');
            $table->string('slug')->unique();
            $table->foreignId('sahip_doktor_id')->constrained('doktorlar')->cascadeOnDelete();
            $table->foreignId('paket_id')->nullable()->constrained('paketler')->nullOnDelete();
            $table->string('logo')->nullable();
            $table->string('telefon')->nullable();
            $table->string('e_posta')->nullable();
            $table->text('adres')->nullable();
            $table->foreignId('il_id')->nullable()->constrained('iller')->nullOnDelete();
            $table->foreignId('ilce_id')->nullable()->constrained('ilceler')->nullOnDelete();
            $table->float('enlem')->nullable();
            $table->float('boylam')->nullable();
            $table->string('web_sitesi')->nullable();
            $table->string('vergi_no')->nullable();
            $table->string('vergi_dairesi')->nullable();
            $table->text('aciklama')->nullable();
            $table->json('calisma_saatleri')->nullable();
            $table->json('sosyal_medya')->nullable();
            $table->string('odeme_periyodu')->nullable();
            $table->timestamp('uyelik_baslangic')->nullable();
            $table->timestamp('uyelik_bitis')->nullable();
            $table->unsignedInteger('max_doktor_sayisi')->default(3);
            $table->boolean('aktif_mi')->default(true);
            $table->string('meta_baslik')->nullable();
            $table->text('meta_aciklama')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klinikler');
    }
};
