<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paketler', function (Blueprint $table) {
            if (! Schema::hasColumn('paketler', 'deneme_gun')) {
                $table->unsignedSmallInteger('deneme_gun')->nullable()->after('aktif_mi');
            }
        });

        Schema::table('doktorlar', function (Blueprint $table) {
            if (! Schema::hasColumn('doktorlar', 'deneme_kullanildi')) {
                $table->boolean('deneme_kullanildi')->default(false)->after('uyelik_bitis');
            }
        });
    }

    public function down(): void
    {
        Schema::table('paketler', function (Blueprint $table) {
            if (Schema::hasColumn('paketler', 'deneme_gun')) {
                $table->dropColumn('deneme_gun');
            }
        });

        Schema::table('doktorlar', function (Blueprint $table) {
            if (Schema::hasColumn('doktorlar', 'deneme_kullanildi')) {
                $table->dropColumn('deneme_kullanildi');
            }
        });
    }
};
