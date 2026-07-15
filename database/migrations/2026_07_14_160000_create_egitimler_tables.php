<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('egitimler', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doktor_id')->constrained('doktorlar')->cascadeOnDelete();
            $table->string('baslik');
            $table->string('slug');
            $table->text('ozet')->nullable();
            $table->longText('icerik')->nullable();
            $table->string('kapak')->nullable();
            $table->string('tip', 20)->default('yuz_yuze'); // yuz_yuze|online|hibrit
            $table->timestamp('baslangic_at')->nullable();
            $table->timestamp('bitis_at')->nullable();
            $table->string('mekan')->nullable();
            $table->string('online_url')->nullable();
            $table->decimal('fiyat', 12, 2)->nullable();
            $table->string('odeme_notu')->nullable();
            $table->unsignedInteger('kontenjan')->nullable();
            $table->boolean('basvuru_acik_mi')->default(true);
            $table->timestamp('basvuru_bitis_at')->nullable();
            $table->string('durum', 20)->default('taslak'); // taslak|yayinda|arsiv
            $table->string('meta_baslik')->nullable();
            $table->string('meta_aciklama')->nullable();
            $table->string('meta_anahtar_kelimeler')->nullable();
            $table->unsignedInteger('sira')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['doktor_id', 'slug']);
            $table->index(['doktor_id', 'durum']);
        });

        Schema::create('egitim_form_alanlari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('egitim_id')->constrained('egitimler')->cascadeOnDelete();
            $table->string('etiket');
            $table->string('anahtar', 80);
            $table->string('tip', 30)->default('text');
            $table->boolean('zorunlu_mu')->default(false);
            $table->json('secenekler')->nullable();
            $table->string('placeholder')->nullable();
            $table->unsignedInteger('sira')->default(0);
            $table->boolean('aktif_mi')->default(true);
            $table->timestamps();

            $table->index(['egitim_id', 'sira']);
        });

        Schema::create('egitim_basvurulari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('egitim_id')->constrained('egitimler')->cascadeOnDelete();
            $table->foreignId('doktor_id')->constrained('doktorlar')->cascadeOnDelete();
            $table->foreignId('hasta_id')->nullable()->constrained('hastalar')->nullOnDelete();
            $table->string('ad', 100);
            $table->string('soyad', 100);
            $table->string('telefon', 40);
            $table->string('e_posta')->nullable();
            $table->json('cevaplar')->nullable();
            $table->string('durum', 30)->default('beklemede'); // beklemede|onaylandi|reddedildi|iptal
            $table->string('ucret_durumu', 30)->default('beklemede'); // yok|beklemede|odendi|kismi|iptal
            $table->decimal('ucret_tutari', 12, 2)->nullable();
            $table->decimal('odenen_tutar', 12, 2)->default(0);
            $table->string('odeme_yontemi', 80)->nullable();
            $table->foreignId('odeme_id')->nullable()->constrained('odemeler')->nullOnDelete();
            $table->text('hekim_notu')->nullable();
            $table->boolean('kvkk_onay')->default(false);
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index(['doktor_id', 'durum']);
            $table->index(['egitim_id', 'durum']);
        });

        Schema::table('odemeler', function (Blueprint $table) {
            if (! Schema::hasColumn('odemeler', 'egitim_basvuru_id')) {
                $table->foreignId('egitim_basvuru_id')
                    ->nullable()
                    ->after('randevu_id')
                    ->constrained('egitim_basvurulari')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('odemeler', function (Blueprint $table) {
            if (Schema::hasColumn('odemeler', 'egitim_basvuru_id')) {
                $table->dropConstrainedForeignId('egitim_basvuru_id');
            }
        });
        Schema::dropIfExists('egitim_basvurulari');
        Schema::dropIfExists('egitim_form_alanlari');
        Schema::dropIfExists('egitimler');
    }
};
