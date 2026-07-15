<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_ayarlari', function (Blueprint $table) {
            $table->text('iyzico_api_key')->nullable()->after('recaptcha_enabled');
            $table->text('iyzico_secret_key')->nullable()->after('iyzico_api_key');
            $table->string('iyzico_base_url', 255)->nullable()->after('iyzico_secret_key');
            $table->string('banka_adi', 150)->nullable()->after('iyzico_base_url');
            $table->string('banka_hesap_sahibi', 150)->nullable()->after('banka_adi');
            $table->string('banka_iban', 34)->nullable()->after('banka_hesap_sahibi');
            $table->text('banka_aciklama')->nullable()->after('banka_iban');
        });
    }

    public function down(): void
    {
        Schema::table('site_ayarlari', function (Blueprint $table) {
            $table->dropColumn([
                'iyzico_api_key',
                'iyzico_secret_key',
                'iyzico_base_url',
                'banka_adi',
                'banka_hesap_sahibi',
                'banka_iban',
                'banka_aciklama',
            ]);
        });
    }
};
