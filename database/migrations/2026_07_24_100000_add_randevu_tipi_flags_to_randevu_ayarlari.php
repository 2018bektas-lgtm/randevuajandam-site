<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('randevu_ayarlari', function (Blueprint $table) {
            $table->boolean('online_randevu_aktif')->default(true)->after('aktif_mi');
            $table->boolean('yuzyuze_randevu_aktif')->default(true)->after('online_randevu_aktif');
        });
    }

    public function down(): void
    {
        Schema::table('randevu_ayarlari', function (Blueprint $table) {
            $table->dropColumn(['online_randevu_aktif', 'yuzyuze_randevu_aktif']);
        });
    }
};
