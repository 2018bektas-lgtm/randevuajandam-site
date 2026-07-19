<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branslar', function (Blueprint $table) {
            if (! Schema::hasColumn('branslar', 'aciklama')) {
                $table->text('aciklama')->nullable()->after('slug');
            }
        });
    }

    public function down(): void
    {
        Schema::table('branslar', function (Blueprint $table) {
            if (Schema::hasColumn('branslar', 'aciklama')) {
                $table->dropColumn('aciklama');
            }
        });
    }
};
