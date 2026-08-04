<?php

namespace App\Support;

use App\Models\Doktor;
use App\Models\Klinik;
use App\Models\Paket;
use Carbon\CarbonInterface;

/**
 * Excel: mevcut abonelere en az 1 dönem fiyat garantisi.
 * Ödeme anında liste fiyatı kilitlenir; yenileme bu tutarı kullanır (bitişe kadar).
 * Liste fiyatı düşerse min(kilit, liste) uygulanır.
 */
class GarantiFiyat
{
    public static function kilitle(Doktor|Klinik $owner, Paket $paket, string $periyot, ?CarbonInterface $bitis = null): void
    {
        $aylik = (float) ($paket->aylik_indirimli_fiyat ?: $paket->aylik_fiyat);
        $yillik = (float) ($paket->yillik_indirimli_fiyat ?: $paket->yillik_fiyat);

        $owner->garanti_aylik_fiyat = $aylik;
        $owner->garanti_yillik_fiyat = $yillik;
        $owner->garanti_bitis = $bitis ?? ($periyot === 'yillik' ? now()->addYear() : now()->addMonth());
        $owner->save();
    }

    public static function yenilemeTutari(Doktor|Klinik $owner, Paket $paket, string $periyot): float
    {
        $listeAylik = (float) ($paket->aylik_indirimli_fiyat ?: $paket->aylik_fiyat);
        $listeYillik = (float) ($paket->yillik_indirimli_fiyat ?: $paket->yillik_fiyat);

        $garantiAktif = $owner->garanti_bitis
            && $owner->garanti_bitis->isFuture()
            && ($owner->garanti_aylik_fiyat !== null || $owner->garanti_yillik_fiyat !== null);

        if ($periyot === 'yillik') {
            if ($garantiAktif && $owner->garanti_yillik_fiyat !== null) {
                // Düşük olan kazanır (indirim müşteriye yansısın)
                return min((float) $owner->garanti_yillik_fiyat, $listeYillik);
            }

            return $listeYillik;
        }

        if ($garantiAktif && $owner->garanti_aylik_fiyat !== null) {
            return min((float) $owner->garanti_aylik_fiyat, $listeAylik);
        }

        return $listeAylik;
    }
}
