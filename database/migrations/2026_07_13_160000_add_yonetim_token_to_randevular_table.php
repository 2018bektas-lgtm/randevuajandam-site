<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('randevular', function (Blueprint $table) {
            if (! Schema::hasColumn('randevular', 'yonetim_token')) {
                $table->string('yonetim_token', 64)->nullable()->unique()->after('hekim_notu');
            }
        });
    }

    public function down(): void
    {
        Schema::table('randevular', function (Blueprint $table) {
            if (Schema::hasColumn('randevular', 'yonetim_token')) {
                $table->dropColumn('yonetim_token');
            }
        });
    }
};
