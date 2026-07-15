<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_ayarlari', function (Blueprint $table) {
            $table->text('recaptcha_secret_key')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('site_ayarlari', function (Blueprint $table) {
            $table->string('recaptcha_secret_key', 100)->nullable()->change();
        });
    }
};
