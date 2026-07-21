<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('klinikler')) {
            return;
        }
        Schema::table('klinikler', function (Blueprint $table) {
            if (! Schema::hasColumn('klinikler', 'platformda_gorunur')) {
                $table->boolean('platformda_gorunur')->default(true)->after('aktif_mi');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('klinikler') && Schema::hasColumn('klinikler', 'platformda_gorunur')) {
            Schema::table('klinikler', function (Blueprint $table) {
                $table->dropColumn('platformda_gorunur');
            });
        }
    }
};
