<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add soft deletes to critical tables to prevent permanent data loss.
     */
    public function up(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('hastalar', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('randevular', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('bloglar', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('hizmetler', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('hastalar', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('randevular', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('bloglar', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('hizmetler', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
