<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('belge_erisim_loglari')) {
            Schema::create('belge_erisim_loglari', function (Blueprint $table) {
                $table->id();
                $table->foreignId('doktor_id')->nullable()->constrained('doktorlar')->nullOnDelete();
                $table->unsignedBigInteger('yonetici_id')->nullable()->index();
                $table->string('aktor', 32)->default('yonetici'); // yonetici|doktor
                $table->string('belge_tipi', 64)->default('meslek_belgesi');
                $table->string('ip', 45)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->timestamps();
            });
        }

        Schema::table('doktorlar', function (Blueprint $table) {
            if (! Schema::hasColumn('doktorlar', 'uyelik_hatirlat_7_at')) {
                $table->timestamp('uyelik_hatirlat_7_at')->nullable()->after('uyelik_bitis');
            }
            if (! Schema::hasColumn('doktorlar', 'uyelik_hatirlat_3_at')) {
                $table->timestamp('uyelik_hatirlat_3_at')->nullable()->after('uyelik_hatirlat_7_at');
            }
            if (! Schema::hasColumn('doktorlar', 'uyelik_hatirlat_1_at')) {
                $table->timestamp('uyelik_hatirlat_1_at')->nullable()->after('uyelik_hatirlat_3_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            foreach (['uyelik_hatirlat_7_at', 'uyelik_hatirlat_3_at', 'uyelik_hatirlat_1_at'] as $col) {
                if (Schema::hasColumn('doktorlar', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::dropIfExists('belge_erisim_loglari');
    }
};
