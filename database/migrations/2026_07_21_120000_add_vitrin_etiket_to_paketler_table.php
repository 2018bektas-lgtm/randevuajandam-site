<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paketler', function (Blueprint $table) {
            if (! Schema::hasColumn('paketler', 'one_cikan_mi')) {
                $table->boolean('one_cikan_mi')->default(false)->after('aktif_mi');
            }
            if (! Schema::hasColumn('paketler', 'etiket')) {
                $table->string('etiket', 40)->nullable()->after('one_cikan_mi');
            }
            if (! Schema::hasColumn('paketler', 'etiket_stil')) {
                $table->string('etiket_stil', 20)->nullable()->after('etiket');
            }
        });

        // Mevcut paketlere makul varsayılanlar (site paket_sec ile uyumlu)
        $rows = DB::table('paketler')->orderBy('sira')->orderBy('id')->get();
        $paidNonWeb = 0;
        $clinicIdx = 0;
        foreach ($rows as $p) {
            $isFree = (float) ($p->aylik_fiyat ?? 0) <= 0 && (float) ($p->yillik_fiyat ?? 0) <= 0;
            $ad = mb_strtolower((string) $p->ad);
            $isWeb = str_contains($ad, 'web sitesi') || str_contains($ad, 'kurumsal')
                || (bool) ($p->domain_dahil_mi ?? false);

            $etiket = null;
            $stil = null;
            $one = false;

            if (($p->tur ?? '') === 'klinik') {
                $clinicIdx++;
                if ($clinicIdx === 2) {
                    $etiket = 'Önerilen';
                    $stil = 'popular';
                    $one = true;
                } elseif ($isWeb) {
                    $etiket = 'Web sitesi dahil';
                    $stil = 'web';
                }
            } else {
                if ($isFree) {
                    $etiket = 'Ücretsiz';
                    $stil = 'free';
                } elseif ($isWeb) {
                    $etiket = 'Web sitesi';
                    $stil = 'web';
                    $one = true;
                } else {
                    $paidNonWeb++;
                    if ($paidNonWeb === 2) {
                        $etiket = 'Popüler';
                        $stil = 'popular';
                        $one = true;
                    }
                }
            }

            DB::table('paketler')->where('id', $p->id)->update([
                'one_cikan_mi' => $one,
                'etiket' => $etiket,
                'etiket_stil' => $stil,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('paketler', function (Blueprint $table) {
            if (Schema::hasColumn('paketler', 'etiket_stil')) {
                $table->dropColumn('etiket_stil');
            }
            if (Schema::hasColumn('paketler', 'etiket')) {
                $table->dropColumn('etiket');
            }
            if (Schema::hasColumn('paketler', 'one_cikan_mi')) {
                $table->dropColumn('one_cikan_mi');
            }
        });
    }
};
