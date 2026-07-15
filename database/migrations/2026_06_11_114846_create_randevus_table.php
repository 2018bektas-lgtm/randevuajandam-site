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
        Schema::create('randevular', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doktor_id')->constrained('doktorlar')->cascadeOnDelete();
            $table->foreignId('hizmet_id')->constrained('hizmetler')->cascadeOnDelete();
            $table->foreignId('hasta_id')->constrained('hastalar')->cascadeOnDelete();
            $table->string('ad');
            $table->string('soyad');
            $table->string('telefon');
            $table->string('e_posta')->nullable();
            $table->date('tarih');
            $table->time('saat');
            $table->text('not')->nullable();
            $table->enum('durum', ['beklemede', 'onaylandi', 'iptal', 'tamamlandi'])->default('beklemede');
            $table->text('hekim_notu')->nullable();
            $table->timestamps();

            $table->index(['doktor_id', 'tarih', 'saat']);
            $table->index('durum');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('randevular');
    }
};
