<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix slug unique constraint: change from global unique to composite unique (il_id, ilce_id, slug).
     * This allows same-named doctors in different cities without slug collision.
     */
    public function up(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->unique(['il_id', 'ilce_id', 'slug'], 'doktorlar_il_ilce_slug_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            $table->dropUnique('doktorlar_il_ilce_slug_unique');
            $table->unique('slug');
        });
    }
};
