<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ek_urun_odemeleri')) {
            Schema::create('ek_urun_odemeleri', function (Blueprint $table) {
                $table->id();
                $table->string('tip', 32); // sms_kontor | personel_koltuk
                $table->foreignId('doktor_id')->nullable()->constrained('doktorlar')->nullOnDelete();
                $table->foreignId('klinik_id')->nullable()->constrained('klinikler')->nullOnDelete();
                $table->unsignedInteger('adet')->default(1);
                $table->decimal('birim_fiyat', 10, 2)->default(0);
                $table->decimal('tutar', 10, 2);
                $table->string('periyot', 16)->nullable(); // aylik|yillik|tek_sefer
                $table->decimal('kist_orani', 5, 4)->nullable();
                $table->string('durum', 20)->default('beklemede'); // beklemede|odendi|reddedildi|iptal
                $table->string('merchant_oid', 64)->unique();
                $table->string('paytr_token')->nullable();
                $table->json('meta')->nullable();
                $table->json('callback_payload')->nullable();
                $table->timestamp('onaylandi_at')->nullable();
                $table->timestamps();
                $table->index(['tip', 'durum']);
            });
        }

        Schema::table('doktorlar', function (Blueprint $table) {
            if (! Schema::hasColumn('doktorlar', 'garanti_aylik_fiyat')) {
                $table->decimal('garanti_aylik_fiyat', 10, 2)->nullable()->after('uyelik_bitis');
            }
            if (! Schema::hasColumn('doktorlar', 'garanti_yillik_fiyat')) {
                $table->decimal('garanti_yillik_fiyat', 10, 2)->nullable()->after('garanti_aylik_fiyat');
            }
            if (! Schema::hasColumn('doktorlar', 'garanti_bitis')) {
                $table->timestamp('garanti_bitis')->nullable()->after('garanti_yillik_fiyat');
            }
            if (! Schema::hasColumn('doktorlar', 'sms_ek_kontor')) {
                $table->unsignedInteger('sms_ek_kontor')->default(0)->after('sms_kullanim_adet');
            }
        });

        Schema::table('klinikler', function (Blueprint $table) {
            if (! Schema::hasColumn('klinikler', 'garanti_aylik_fiyat')) {
                $table->decimal('garanti_aylik_fiyat', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('klinikler', 'garanti_yillik_fiyat')) {
                $table->decimal('garanti_yillik_fiyat', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('klinikler', 'garanti_bitis')) {
                $table->timestamp('garanti_bitis')->nullable();
            }
            if (! Schema::hasColumn('klinikler', 'ek_personel_koltuk_sayisi')) {
                $table->unsignedInteger('ek_personel_koltuk_sayisi')->default(0);
            }
            if (! Schema::hasColumn('klinikler', 'sms_ek_kontor')) {
                $table->unsignedInteger('sms_ek_kontor')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ek_urun_odemeleri');
        Schema::table('doktorlar', function (Blueprint $table) {
            foreach (['garanti_aylik_fiyat', 'garanti_yillik_fiyat', 'garanti_bitis', 'sms_ek_kontor'] as $c) {
                if (Schema::hasColumn('doktorlar', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
        Schema::table('klinikler', function (Blueprint $table) {
            foreach (['garanti_aylik_fiyat', 'garanti_yillik_fiyat', 'garanti_bitis', 'ek_personel_koltuk_sayisi', 'sms_ek_kontor'] as $c) {
                if (Schema::hasColumn('klinikler', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
