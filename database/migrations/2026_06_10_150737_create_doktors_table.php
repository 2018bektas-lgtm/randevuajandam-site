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
        Schema::create('doktorlar', function (Blueprint $table) {
            $table->id();
            $table->string('ad_soyad');
            $table->string('slug')->unique()->nullable();
            $table->string('e_posta')->unique();
            $table->string('sifre');
            $table->string('telefon')->nullable();
            $table->foreignId('il_id')->nullable()->constrained('iller')->nullOnDelete();
            $table->foreignId('ilce_id')->nullable()->constrained('ilceler')->nullOnDelete();
            $table->string('tur')->default('bireysel'); // 'bireysel' veya 'klinik'
            $table->string('klinik_adi')->nullable(); // Klinik türü için opsiyonel
            $table->unsignedBigInteger('paket_id')->nullable();
            $table->string('odeme_periyodu')->nullable(); // 'aylik' veya 'yillik'
            $table->timestamp('uyelik_baslangic')->nullable();
            $table->timestamp('uyelik_bitis')->nullable();
            $table->boolean('aktif_mi')->default(true);
            $table->string('unvan')->nullable(); // Unvan: Prof. Dr., Doç. Dr., Uzm. Dr. vb.
            $table->string('uzmanlik_alani')->nullable(); // Uzmanlık Alanı / Branş
            $table->string('mezuniyet')->nullable(); // Mezun olunan üniversite/okul
            $table->text('biyografi')->nullable(); // Kısa özgeçmiş
            $table->text('adres')->nullable(); // Klinik/Muayenehane adresi
            $table->decimal('enlem', 10, 8)->nullable(); // Latitude
            $table->decimal('boylam', 11, 8)->nullable(); // Longitude
            $table->string('profil_resmi')->nullable(); // Profil Resmi dosya yolu
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doktorlar');
    }
};
