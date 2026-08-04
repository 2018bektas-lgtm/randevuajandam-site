<?php

namespace App\Console\Commands;

use App\Models\Doktor;
use Illuminate\Console\Command;

/**
 * Excel: 90 gün giriş yapılmayan ücretsiz (Vitrin) profil aramada gizlenir, silinmez.
 */
class DoktorVitrinTemizlikCommand extends Command
{
    protected $signature = 'doktor:vitrin-temizlik {--gun=90 : Kaç gündür giriş yok}';

    protected $description = '90+ gündür giriş yapmayan ücretsiz vitrin profillerini aramadan gizler';

    public function handle(): int
    {
        $gun = max(1, (int) $this->option('gun'));
        $esik = now()->subDays($gun);

        $q = Doktor::query()
            ->where('aktif_mi', true)
            ->where(function ($w) {
                $w->where('platformda_gorunur', true)->orWhereNull('platformda_gorunur');
            })
            ->whereNull('klinik_id')
            ->whereHas('paket', function ($p) {
                $p->where(function ($x) {
                    $x->where('aylik_fiyat', '<=', 0)
                        ->where(function ($y) {
                            $y->whereNull('aylik_indirimli_fiyat')
                                ->orWhere('aylik_indirimli_fiyat', '<=', 0);
                        });
                });
            })
            ->where(function ($w) use ($esik) {
                $w->where(function ($a) use ($esik) {
                    $a->whereNotNull('son_giris_at')->where('son_giris_at', '<', $esik);
                })->orWhere(function ($b) use ($esik) {
                    $b->whereNull('son_giris_at')->where('updated_at', '<', $esik);
                });
            });

        $count = 0;
        $q->chunkById(100, function ($doktorlar) use (&$count) {
            foreach ($doktorlar as $d) {
                $d->platformda_gorunur = false;
                $d->save();
                $count++;
            }
        });

        $this->info("{$count} ücretsiz vitrin profili gizlendi ({$gun} gün hareketsiz).");

        return self::SUCCESS;
    }
}
