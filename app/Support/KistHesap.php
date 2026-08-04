<?php

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * Ay ortası ek koltuk: kalan güne göre oran (Excel kıst).
 * Bitiş yoksa 1.0 (tam dönem).
 */
class KistHesap
{
    public static function oran(?CarbonInterface $bitis): float
    {
        if (! $bitis) {
            return 1.0;
        }

        $kalan = max(0, now()->diffInDays($bitis, false));
        if ($kalan <= 0) {
            return 1.0;
        }

        // Dönem tahmini: bitişe kalan + geçen ay günü ~ 30
        $donemGun = 30;
        if ($bitis->greaterThan(now())) {
            // Yaklaşık dönem uzunluğu: min(365, max(kalan, 30))
            $donemGun = min(365, max(30, (int) $kalan + 1));
        }

        return min(1.0, round($kalan / $donemGun, 4));
    }

    public static function tutar(float $tamTutar, ?CarbonInterface $bitis, bool $tekSefer = false): array
    {
        if ($tekSefer) {
            return ['oran' => 1.0, 'tutar' => round($tamTutar, 2)];
        }

        $oran = self::oran($bitis);
        // En az %40 kıst (çok kısa dönemlerde aşırı ucuz olmasın)
        $oran = max(0.40, $oran);

        return [
            'oran' => $oran,
            'tutar' => round($tamTutar * $oran, 2),
        ];
    }
}
