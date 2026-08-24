<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            if (! Schema::hasColumn('doktorlar', 'eski_slug')) {
                $table->string('eski_slug')->nullable()->after('slug')->index();
            }
        });

        Schema::table('klinikler', function (Blueprint $table) {
            if (! Schema::hasColumn('klinikler', 'eski_slug')) {
                $table->string('eski_slug')->nullable()->after('slug')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            if (Schema::hasColumn('doktorlar', 'eski_slug')) {
                $table->dropIndex(['eski_slug']);
                $table->dropColumn('eski_slug');
            }
        });

        Schema::table('klinikler', function (Blueprint $table) {
            if (Schema::hasColumn('klinikler', 'eski_slug')) {
                $table->dropIndex(['eski_slug']);
                $table->dropColumn('eski_slug');
            }
        });
    }
};
