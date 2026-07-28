<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ödeme adımında girilen fatura bilgileri:
 * - doktorlar: sonraki ödemelerde prefiller
 * - uyelik_odemeleri.fatura_bilgisi: ödeme anı snapshot (fatura kesimi için)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            if (! Schema::hasColumn('doktorlar', 'fatura_tipi')) {
                $table->string('fatura_tipi', 20)->nullable()->after('adres'); // bireysel | kurumsal
            }
            if (! Schema::hasColumn('doktorlar', 'fatura_unvan')) {
                $table->string('fatura_unvan', 255)->nullable()->after('fatura_tipi'); // ad soyad veya ticari unvan
            }
            if (! Schema::hasColumn('doktorlar', 'fatura_tc_vkn')) {
                $table->string('fatura_tc_vkn', 11)->nullable()->after('fatura_unvan'); // 11 TC veya 10 VKN
            }
            if (! Schema::hasColumn('doktorlar', 'fatura_vergi_dairesi')) {
                $table->string('fatura_vergi_dairesi', 120)->nullable()->after('fatura_tc_vkn');
            }
            if (! Schema::hasColumn('doktorlar', 'fatura_adres')) {
                $table->text('fatura_adres')->nullable()->after('fatura_vergi_dairesi');
            }
            if (! Schema::hasColumn('doktorlar', 'fatura_il')) {
                $table->string('fatura_il', 80)->nullable()->after('fatura_adres');
            }
            if (! Schema::hasColumn('doktorlar', 'fatura_ilce')) {
                $table->string('fatura_ilce', 80)->nullable()->after('fatura_il');
            }
            if (! Schema::hasColumn('doktorlar', 'fatura_posta_kodu')) {
                $table->string('fatura_posta_kodu', 10)->nullable()->after('fatura_ilce');
            }
            if (! Schema::hasColumn('doktorlar', 'fatura_email')) {
                $table->string('fatura_email', 190)->nullable()->after('fatura_posta_kodu');
            }
            if (! Schema::hasColumn('doktorlar', 'fatura_telefon')) {
                $table->string('fatura_telefon', 40)->nullable()->after('fatura_email');
            }
        });

        Schema::table('uyelik_odemeleri', function (Blueprint $table) {
            if (! Schema::hasColumn('uyelik_odemeleri', 'fatura_bilgisi')) {
                $table->json('fatura_bilgisi')->nullable()->after('kurulum_verisi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('doktorlar', function (Blueprint $table) {
            foreach ([
                'fatura_tipi', 'fatura_unvan', 'fatura_tc_vkn', 'fatura_vergi_dairesi',
                'fatura_adres', 'fatura_il', 'fatura_ilce', 'fatura_posta_kodu',
                'fatura_email', 'fatura_telefon',
            ] as $col) {
                if (Schema::hasColumn('doktorlar', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('uyelik_odemeleri', function (Blueprint $table) {
            if (Schema::hasColumn('uyelik_odemeleri', 'fatura_bilgisi')) {
                $table->dropColumn('fatura_bilgisi');
            }
        });
    }
};
