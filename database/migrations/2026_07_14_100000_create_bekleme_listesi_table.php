<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bekleme_listesi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doktor_id')->constrained('doktorlar')->cascadeOnDelete();
            $table->foreignId('hasta_id')->nullable()->constrained('hastalar')->nullOnDelete();
            $table->foreignId('hizmet_id')->nullable()->constrained('hizmetler')->nullOnDelete();
            $table->string('ad', 100);
            $table->string('soyad', 100);
            $table->string('telefon', 30);
            $table->string('e_posta', 255)->nullable();
            $table->date('tercih_tarih')->nullable();
            $table->string('tercih_saat', 8)->nullable();
            $table->text('not')->nullable();
            $table->string('durum', 32)->default('beklemede'); // beklemede|bildirildi|randevu_alindi|iptal
            $table->timestamp('bildirildi_at')->nullable();
            $table->timestamps();

            $table->index(['doktor_id', 'durum']);
            $table->index(['doktor_id', 'tercih_tarih']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bekleme_listesi');
    }
};
