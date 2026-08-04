<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('hasta_dosyalar')) {
            Schema::create('hasta_dosyalar', function (Blueprint $table) {
                $table->id();
                $table->foreignId('doktor_id')->constrained('doktorlar')->cascadeOnDelete();
                $table->foreignId('hasta_id')->constrained('hastalar')->cascadeOnDelete();
                $table->string('baslik')->nullable();
                $table->string('dosya_yolu');
                $table->string('orijinal_ad')->nullable();
                $table->string('mime', 120)->nullable();
                $table->unsignedBigInteger('boyut')->nullable();
                $table->text('not')->nullable();
                $table->timestamps();
                $table->index(['doktor_id', 'hasta_id']);
            });
        }

        if (! Schema::hasTable('onam_formlari')) {
            Schema::create('onam_formlari', function (Blueprint $table) {
                $table->id();
                $table->foreignId('doktor_id')->constrained('doktorlar')->cascadeOnDelete();
                $table->string('baslik');
                $table->longText('icerik');
                $table->boolean('aktif_mi')->default(true);
                $table->unsignedInteger('sira')->default(0);
                $table->timestamps();
                $table->index(['doktor_id', 'aktif_mi']);
            });
        }

        if (! Schema::hasTable('onam_imzalar')) {
            Schema::create('onam_imzalar', function (Blueprint $table) {
                $table->id();
                $table->foreignId('onam_form_id')->constrained('onam_formlari')->cascadeOnDelete();
                $table->foreignId('doktor_id')->constrained('doktorlar')->cascadeOnDelete();
                $table->foreignId('hasta_id')->constrained('hastalar')->cascadeOnDelete();
                $table->string('hasta_ad_soyad');
                $table->string('ip', 45)->nullable();
                $table->timestamp('imzalandi_at');
                $table->text('not')->nullable();
                $table->timestamps();
                $table->index(['doktor_id', 'hasta_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('onam_imzalar');
        Schema::dropIfExists('onam_formlari');
        Schema::dropIfExists('hasta_dosyalar');
    }
};
