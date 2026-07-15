<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_ayarlari', function (Blueprint $table) {
            if (! Schema::hasColumn('site_ayarlari', 'gtm_container_id')) {
                $table->string('gtm_container_id', 40)->nullable()->after('meta_yazar');
            }
            if (! Schema::hasColumn('site_ayarlari', 'ga4_measurement_id')) {
                $table->string('ga4_measurement_id', 40)->nullable()->after('gtm_container_id');
            }
            if (! Schema::hasColumn('site_ayarlari', 'meta_pixel_id')) {
                $table->string('meta_pixel_id', 40)->nullable()->after('ga4_measurement_id');
            }
            if (! Schema::hasColumn('site_ayarlari', 'google_ads_id')) {
                $table->string('google_ads_id', 40)->nullable()->after('meta_pixel_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_ayarlari', function (Blueprint $table) {
            foreach (['gtm_container_id', 'ga4_measurement_id', 'meta_pixel_id', 'google_ads_id'] as $col) {
                if (Schema::hasColumn('site_ayarlari', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
