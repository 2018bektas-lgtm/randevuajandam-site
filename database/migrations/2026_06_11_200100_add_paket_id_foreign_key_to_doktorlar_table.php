<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add proper foreign key constraint for paket_id on doktorlar table.
     */
    public function up(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            $table->foreign('paket_id')->references('id')->on('paketler')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            $table->dropForeign(['paket_id']);
        });
    }
};
