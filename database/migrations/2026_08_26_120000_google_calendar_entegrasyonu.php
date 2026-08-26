<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Google Calendar entegrasyonu:
 *  - doktorlar + klinikler: OAuth token/config (encrypted).
 *  - randevular: Google'daki karsilik event id + son senkron zamani.
 *  - doktor_google_bloklari: Google'dan cekilen "mesgul" araliklar. Randevular
 *    tablosuna asla yazmiyoruz (SBYS scope disi kalma karari); bu tablo salt
 *    slot bloklama amacli.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            if (! Schema::hasColumn('doktorlar', 'google_calendar_config')) {
                $table->text('google_calendar_config')->nullable()->after('whatsapp_kota');
            }
            if (! Schema::hasColumn('doktorlar', 'google_calendar_baglandi_at')) {
                $table->timestamp('google_calendar_baglandi_at')->nullable()->after('google_calendar_config');
            }
        });

        Schema::table('klinikler', function (Blueprint $table) {
            if (! Schema::hasColumn('klinikler', 'google_calendar_config')) {
                $table->text('google_calendar_config')->nullable()->after('whatsapp_kota');
            }
            if (! Schema::hasColumn('klinikler', 'google_calendar_baglandi_at')) {
                $table->timestamp('google_calendar_baglandi_at')->nullable()->after('google_calendar_config');
            }
        });

        Schema::table('randevular', function (Blueprint $table) {
            if (! Schema::hasColumn('randevular', 'google_event_id')) {
                $table->string('google_event_id', 255)->nullable()->after('yonetim_token');
                $table->index('google_event_id', 'randevular_google_event_id_index');
            }
            if (! Schema::hasColumn('randevular', 'google_synced_at')) {
                $table->timestamp('google_synced_at')->nullable()->after('google_event_id');
            }
        });

        if (! Schema::hasTable('doktor_google_bloklari')) {
            Schema::create('doktor_google_bloklari', function (Blueprint $table) {
                $table->id();
                $table->foreignId('doktor_id')->constrained('doktorlar')->cascadeOnDelete();
                // Google'daki event id (calendars/{id}/events/{eventId}); unique degil
                // cunku Google recurring event'lerinde ayni id + farkli tarih olabilir.
                $table->string('google_event_id', 255);
                $table->dateTime('baslangic_at');
                $table->dateTime('bitis_at');
                $table->string('baslik', 255)->nullable();
                $table->boolean('hepsi_gun_mu')->default(false);
                $table->timestamps();

                $table->unique(['doktor_id', 'google_event_id', 'baslangic_at'], 'dgb_doktor_event_baslangic_unique');
                $table->index(['doktor_id', 'baslangic_at', 'bitis_at'], 'dgb_doktor_zaman_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('doktor_google_bloklari');

        Schema::table('randevular', function (Blueprint $table) {
            if (Schema::hasColumn('randevular', 'google_synced_at')) {
                $table->dropColumn('google_synced_at');
            }
            if (Schema::hasColumn('randevular', 'google_event_id')) {
                $table->dropIndex('randevular_google_event_id_index');
                $table->dropColumn('google_event_id');
            }
        });

        Schema::table('klinikler', function (Blueprint $table) {
            foreach (['google_calendar_config', 'google_calendar_baglandi_at'] as $col) {
                if (Schema::hasColumn('klinikler', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('doktorlar', function (Blueprint $table) {
            foreach (['google_calendar_config', 'google_calendar_baglandi_at'] as $col) {
                if (Schema::hasColumn('doktorlar', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
