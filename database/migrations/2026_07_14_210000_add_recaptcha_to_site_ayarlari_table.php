<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_ayarlari', function (Blueprint $table) {
            if (! Schema::hasColumn('site_ayarlari', 'recaptcha_site_key')) {
                $table->string('recaptcha_site_key', 100)->nullable()->after('google_ads_id');
            }
            if (! Schema::hasColumn('site_ayarlari', 'recaptcha_secret_key')) {
                $table->text('recaptcha_secret_key')->nullable()->after('recaptcha_site_key');
            }
            if (! Schema::hasColumn('site_ayarlari', 'recaptcha_enabled')) {
                $table->boolean('recaptcha_enabled')->default(true)->after('recaptcha_secret_key');
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_ayarlari', function (Blueprint $table) {
            foreach (['recaptcha_site_key', 'recaptcha_secret_key', 'recaptcha_enabled'] as $col) {
                if (Schema::hasColumn('site_ayarlari', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
