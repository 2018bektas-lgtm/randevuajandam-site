<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            if (! Schema::hasColumn('doktorlar', 'diploma_no')) {
                $table->string('diploma_no', 64)->nullable()->after('tc_kimlik_no');
            }
            if (! Schema::hasColumn('doktorlar', 'meslek_belge_yolu')) {
                $table->string('meslek_belge_yolu', 500)->nullable()->after('diploma_no');
            }
            if (! Schema::hasColumn('doktorlar', 'meslek_dogrulama_durumu')) {
                $table->string('meslek_dogrulama_durumu', 20)->default('beklemede')->after('meslek_belge_yolu');
            }
            if (! Schema::hasColumn('doktorlar', 'meslek_dogrulama_notu')) {
                $table->string('meslek_dogrulama_notu', 500)->nullable()->after('meslek_dogrulama_durumu');
            }
            if (! Schema::hasColumn('doktorlar', 'meslek_dogrulandi_at')) {
                $table->timestamp('meslek_dogrulandi_at')->nullable()->after('meslek_dogrulama_notu');
            }
            if (! Schema::hasColumn('doktorlar', 'meslek_dogrulayan_yonetici_id')) {
                $table->unsignedBigInteger('meslek_dogrulayan_yonetici_id')->nullable()->after('meslek_dogrulandi_at');
            }
        });

        // Mevcut hekimler ödeme kilidine takılmasın
        if (Schema::hasColumn('doktorlar', 'meslek_dogrulama_durumu')) {
            DB::table('doktorlar')->update([
                'meslek_dogrulama_durumu' => 'onaylandi',
                'meslek_dogrulandi_at' => now(),
            ]);
        }

        // TC unique (null hariç — MySQL multiple null OK)
        try {
            Schema::table('doktorlar', function (Blueprint $table) {
                $table->unique('tc_kimlik_no', 'doktorlar_tc_kimlik_no_unique');
            });
        } catch (\Throwable) {
            // index zaten varsa
        }
    }

    public function down(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            try {
                $table->dropUnique('doktorlar_tc_kimlik_no_unique');
            } catch (\Throwable) {
            }
            foreach ([
                'diploma_no',
                'meslek_belge_yolu',
                'meslek_dogrulama_durumu',
                'meslek_dogrulama_notu',
                'meslek_dogrulandi_at',
                'meslek_dogrulayan_yonetici_id',
            ] as $col) {
                if (Schema::hasColumn('doktorlar', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
