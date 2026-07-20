<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Otomatik YÖK mezun belgesi doğrulama:
 * - doktor_mezuniyet_belgeleri
 * - meslek_program_eslemeleri
 * - edevlet_dogrulama_loglari
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('meslek_program_eslemeleri')) {
            Schema::create('meslek_program_eslemeleri', function (Blueprint $table) {
                $table->id();
                $table->string('program_anahtar', 120);
                $table->string('unvan_ad', 80)->nullable();
                $table->string('brans_ad', 120)->nullable();
                $table->unsignedSmallInteger('oncelik')->default(100);
                $table->boolean('auto_onay')->default(true);
                $table->boolean('aktif')->default(true);
                $table->timestamps();

                $table->index(['aktif', 'oncelik']);
            });
        }

        if (! Schema::hasTable('edevlet_dogrulama_loglari')) {
            Schema::create('edevlet_dogrulama_loglari', function (Blueprint $table) {
                $table->id();
                $table->string('barkod', 64)->nullable()->index();
                $table->string('tc_maskeli', 20)->nullable();
                $table->string('durum', 32)->nullable();
                $table->unsignedInteger('sure_ms')->nullable();
                $table->string('hata', 500)->nullable();
                $table->string('ip', 45)->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('doktor_mezuniyet_belgeleri')) {
            Schema::create('doktor_mezuniyet_belgeleri', function (Blueprint $table) {
                $table->id();
                $table->foreignId('doktor_id')->nullable()->constrained('doktorlar')->nullOnDelete();
                $table->string('barkod', 64)->nullable()->index();
                $table->string('tc_kimlik_no', 11)->nullable();
                $table->string('ad_soyad_belge', 255)->nullable();
                $table->string('program', 500)->nullable();
                $table->string('universite', 255)->nullable();
                $table->string('fakulte', 255)->nullable();
                $table->string('bolum', 255)->nullable();
                $table->string('diploma_no', 64)->nullable();
                $table->string('diploma_notu', 32)->nullable();
                $table->date('mezuniyet_tarihi')->nullable();
                $table->string('dogrulama_durumu', 32)->default('bekliyor');
                $table->decimal('eslesme_skoru', 5, 4)->nullable();
                $table->json('eslesme_detay')->nullable();
                $table->string('dosya_yolu', 500)->nullable();
                $table->json('ham_parse')->nullable();
                $table->unsignedBigInteger('edevlet_log_id')->nullable();
                $table->boolean('auto_onay_uygun')->default(false);
                $table->string('onerilen_unvan', 80)->nullable();
                $table->string('onerilen_brans', 120)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('doktor_mezuniyet_belgeleri');
        Schema::dropIfExists('edevlet_dogrulama_loglari');
        Schema::dropIfExists('meslek_program_eslemeleri');
    }
};
