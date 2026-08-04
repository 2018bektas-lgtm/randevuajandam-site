<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paket_ozellikleri', function (Blueprint $table) {
            if (! Schema::hasColumn('paket_ozellikleri', 'grup')) {
                $table->string('grup', 64)->nullable()->after('aciklama');
            }
            if (! Schema::hasColumn('paket_ozellikleri', 'sira')) {
                $table->unsignedInteger('sira')->default(0)->after('grup');
            }
            if (! Schema::hasColumn('paket_ozellikleri', 'vitrin_mi')) {
                $table->boolean('vitrin_mi')->default(true)->after('sira');
            }
        });

        Schema::table('paketler', function (Blueprint $table) {
            if (! Schema::hasColumn('paketler', 'sms_aylik_kontor')) {
                $table->unsignedInteger('sms_aylik_kontor')->nullable()->after('max_randevu_sayisi');
            }
            if (! Schema::hasColumn('paketler', 'ek_personel_aylik_fiyat')) {
                $table->decimal('ek_personel_aylik_fiyat', 10, 2)->nullable()->after('ek_doktor_yillik_fiyat');
            }
            if (! Schema::hasColumn('paketler', 'ek_personel_yillik_fiyat')) {
                $table->decimal('ek_personel_yillik_fiyat', 10, 2)->nullable()->after('ek_personel_aylik_fiyat');
            }
            if (! Schema::hasColumn('paketler', 'max_ek_doktor')) {
                $table->unsignedInteger('max_ek_doktor')->nullable()->after('max_doktor_sayisi');
            }
            if (! Schema::hasColumn('paketler', 'listeleme_oncelik')) {
                $table->unsignedTinyInteger('listeleme_oncelik')->default(1)->after('sira')
                    ->comment('0=alt, 1=standart, 2=öncelikli, 3=en üst');
            }
        });
    }

    public function down(): void
    {
        Schema::table('paket_ozellikleri', function (Blueprint $table) {
            foreach (['grup', 'sira', 'vitrin_mi'] as $col) {
                if (Schema::hasColumn('paket_ozellikleri', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('paketler', function (Blueprint $table) {
            foreach (['sms_aylik_kontor', 'ek_personel_aylik_fiyat', 'ek_personel_yillik_fiyat', 'max_ek_doktor', 'listeleme_oncelik'] as $col) {
                if (Schema::hasColumn('paketler', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
