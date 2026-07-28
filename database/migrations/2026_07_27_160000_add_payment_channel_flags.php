<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ödeme kanalları bağımsız seçilebilir:
 * PayTR / iyzico / havale — tek tek, ikisi, üçü veya hiçbiri.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_ayarlari', function (Blueprint $table) {
            if (! Schema::hasColumn('site_ayarlari', 'paytr_aktif')) {
                $table->boolean('paytr_aktif')->default(true);
            }
            if (! Schema::hasColumn('site_ayarlari', 'iyzico_aktif')) {
                $table->boolean('iyzico_aktif')->default(false);
            }
            if (! Schema::hasColumn('site_ayarlari', 'havale_aktif')) {
                $table->boolean('havale_aktif')->default(true);
            }
        });

        // Mevcut exclusive odeme_saglayici / iyzico_enabled değerlerinden backfill
        $hasSaglayici = Schema::hasColumn('site_ayarlari', 'odeme_saglayici');
        $hasIyzicoEnabled = Schema::hasColumn('site_ayarlari', 'iyzico_enabled');

        if ($hasSaglayici || $hasIyzicoEnabled) {
            $select = ['id'];
            if ($hasSaglayici) {
                $select[] = 'odeme_saglayici';
            }
            if ($hasIyzicoEnabled) {
                $select[] = 'iyzico_enabled';
            }

            $rows = DB::table('site_ayarlari')->select($select)->get();
            foreach ($rows as $row) {
                $isIyzico = false;
                if ($hasSaglayici) {
                    $isIyzico = ($row->odeme_saglayici ?? '') === 'iyzico';
                }
                if ($hasIyzicoEnabled && ! $isIyzico) {
                    $isIyzico = (bool) ($row->iyzico_enabled ?? false);
                }

                $update = [
                    'paytr_aktif' => ! $isIyzico,
                    'iyzico_aktif' => $isIyzico,
                    'havale_aktif' => true,
                ];
                if ($hasIyzicoEnabled) {
                    $update['iyzico_enabled'] = $isIyzico;
                }

                DB::table('site_ayarlari')->where('id', $row->id)->update($update);
            }
        }
    }

    public function down(): void
    {
        Schema::table('site_ayarlari', function (Blueprint $table) {
            $cols = array_filter(
                ['paytr_aktif', 'iyzico_aktif', 'havale_aktif'],
                fn (string $c) => Schema::hasColumn('site_ayarlari', $c)
            );
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
};
