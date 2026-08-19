<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('klinikler', function (Blueprint $table) {
            if (! Schema::hasColumn('klinikler', 'whatsapp_config')) {
                // Encrypted JSON — token/phone_number_id/waba_id/display_name.
                // Model B (kliniğin kendi numarası) doldurulduğunda kullanılır;
                // NULL ise config('whatsapp.default') geçerlidir.
                $table->text('whatsapp_config')->nullable()->after('sms_ek_kontor');
            }
            if (! Schema::hasColumn('klinikler', 'whatsapp_baglandi_at')) {
                $table->timestamp('whatsapp_baglandi_at')->nullable()->after('whatsapp_config');
            }
            if (! Schema::hasColumn('klinikler', 'whatsapp_kota')) {
                $table->unsignedInteger('whatsapp_kota')->default(500)->after('whatsapp_baglandi_at');
            }
        });

        if (! Schema::hasTable('whatsapp_gonderimler')) {
            Schema::create('whatsapp_gonderimler', function (Blueprint $table) {
                $table->id();
                $table->foreignId('klinik_id')->nullable()->constrained('klinikler')->nullOnDelete();
                $table->foreignId('doktor_id')->nullable()->constrained('doktorlar')->nullOnDelete();
                $table->foreignId('hasta_id')->nullable()->constrained('hastalar')->nullOnDelete();
                $table->string('telefon', 20);
                $table->string('sablon', 100);
                $table->string('kategori', 20)->default('utility'); // utility|authentication|marketing|service
                $table->string('wamid')->nullable();                 // Meta mesaj ID
                $table->string('durum', 20)->default('kuyrukta');    // kuyrukta|gonderildi|iletildi|okundu|hata|sms_fallback
                $table->text('hata')->nullable();
                $table->timestamps();

                $table->index(['klinik_id', 'created_at']);
                $table->index(['doktor_id', 'created_at']);
                $table->index('wamid');
                $table->index(['durum', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_gonderimler');
        Schema::table('klinikler', function (Blueprint $table) {
            foreach (['whatsapp_config', 'whatsapp_baglandi_at', 'whatsapp_kota'] as $col) {
                if (Schema::hasColumn('klinikler', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
