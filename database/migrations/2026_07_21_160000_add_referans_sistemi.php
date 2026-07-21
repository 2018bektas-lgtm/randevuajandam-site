<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            if (! Schema::hasColumn('doktorlar', 'referans_kodu')) {
                $table->string('referans_kodu', 16)->nullable()->unique()->after('platformda_gorunur');
            }
            if (! Schema::hasColumn('doktorlar', 'davet_eden_id')) {
                $table->foreignId('davet_eden_id')->nullable()->after('referans_kodu')
                    ->constrained('doktorlar')->nullOnDelete();
            }
            if (! Schema::hasColumn('doktorlar', 'referans_kodu_kullanilan')) {
                $table->string('referans_kodu_kullanilan', 16)->nullable()->after('davet_eden_id');
            }
        });

        if (! Schema::hasTable('referans_davetler')) {
            Schema::create('referans_davetler', function (Blueprint $table) {
                $table->id();
                $table->foreignId('davet_eden_id')->constrained('doktorlar')->cascadeOnDelete();
                $table->foreignId('davet_edilen_id')->unique()->constrained('doktorlar')->cascadeOnDelete();
                $table->string('kod', 16);
                $table->string('durum', 20)->default('bekliyor'); // bekliyor, odullendirildi, iptal, reddedildi
                $table->foreignId('uyelik_odeme_id')->nullable()->constrained('uyelik_odemeleri')->nullOnDelete();
                $table->unsignedTinyInteger('indirim_yuzde_davet_edilen')->default(0);
                $table->unsignedTinyInteger('komisyon_yuzde_davet_eden')->default(0);
                $table->unsignedSmallInteger('odul_gun_davet_eden')->default(0);
                $table->decimal('odeme_tutari_brut', 12, 2)->nullable();
                $table->decimal('odeme_tutari_net', 12, 2)->nullable();
                $table->timestamp('odullendirildi_at')->nullable();
                $table->string('red_nedeni', 255)->nullable();
                $table->timestamps();

                $table->index(['davet_eden_id', 'durum']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('referans_davetler');

        Schema::table('doktorlar', function (Blueprint $table) {
            if (Schema::hasColumn('doktorlar', 'davet_eden_id')) {
                $table->dropConstrainedForeignId('davet_eden_id');
            }
            if (Schema::hasColumn('doktorlar', 'referans_kodu_kullanilan')) {
                $table->dropColumn('referans_kodu_kullanilan');
            }
            if (Schema::hasColumn('doktorlar', 'referans_kodu')) {
                $table->dropUnique(['referans_kodu']);
                $table->dropColumn('referans_kodu');
            }
        });
    }
};
