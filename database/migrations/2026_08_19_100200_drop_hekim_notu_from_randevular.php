<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('randevular', function (Blueprint $table) {
            if (Schema::hasColumn('randevular', 'hekim_notu')) {
                $table->dropColumn('hekim_notu');
            }
        });
    }

    public function down(): void
    {
        Schema::table('randevular', function (Blueprint $table) {
            if (! Schema::hasColumn('randevular', 'hekim_notu')) {
                $table->text('hekim_notu')->nullable();
            }
        });
    }
};
