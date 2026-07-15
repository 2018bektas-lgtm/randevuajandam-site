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
        Schema::create('randevu_ayarlari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doktor_id')->constrained('doktorlar')->cascadeOnDelete();
            $table->enum('randevu_onay_tipi', ['otomatik', 'manuel'])->default('manuel');
            $table->integer('en_erken_randevu_saati')->default(2); // en az x saat sonrasına alınabilir
            $table->integer('en_gec_randevu_gunu')->default(30); // en fazla x gün sonrasına alınabilir
            $table->integer('randevu_periyodu')->default(30); // randevu süresi (dakika)
            $table->boolean('randevu_iptal_aktif_mi')->default(true); // hastalar randevu iptal edebilir mi?
            $table->integer('iptal_saat_limiti')->default(24); // randevudan en az x saat öncesine kadar iptal edilebilir
            $table->integer('gunluk_maksimum_randevu')->default(0); // gün başına maksimum randevu sayısı (0 = sınırsız)
            $table->boolean('email_bildirimleri')->default(true); // hekime e-posta bildirimi gitsin mi?
            $table->boolean('aktif_mi')->default(true); // hekimin online randevu sistemi açık mı?
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('randevu_ayarlari');
    }
};
