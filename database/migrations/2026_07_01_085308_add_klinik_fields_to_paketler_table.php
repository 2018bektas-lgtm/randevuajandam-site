<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paketler', function (Blueprint $table) {
            $table->unsignedInteger('max_doktor_sayisi')->nullable()->after('aktif_mi');
            $table->unsignedInteger('max_personel_sayisi')->nullable()->after('max_doktor_sayisi');
            $table->boolean('merkezi_finans_mi')->default(false)->after('max_personel_sayisi');
            $table->boolean('toplu_randevu_mi')->default(false)->after('merkezi_finans_mi');
            $table->boolean('raporlama_mi')->default(false)->after('toplu_randevu_mi');
            $table->boolean('hasta_havuzu_mi')->default(false)->after('raporlama_mi');
            $table->unsignedInteger('sira')->default(0)->after('hasta_havuzu_mi');
        });
    }

    public function down(): void
    {
        Schema::table('paketler', function (Blueprint $table) {
            $table->dropColumn([
                'max_doktor_sayisi',
                'max_personel_sayisi',
                'merkezi_finans_mi',
                'toplu_randevu_mi',
                'raporlama_mi',
                'hasta_havuzu_mi',
                'sira',
            ]);
        });
    }
};
