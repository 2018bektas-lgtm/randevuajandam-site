<?php

namespace App\Services;

use App\Models\Doktor;
use App\Models\DoktorCalismaSaati;
use App\Models\DoktorIzin;
use App\Models\Randevu;
use Carbon\Carbon;

/**
 * Validates appointment booking requests against doctor's schedule,
 * leaves, working hours, and business rules.
 */
class RandevuDogrulamaService
{
    /**
     * Validate a new appointment request. Returns null if valid, or an error message string.
     */
    public function dogrula(Doktor $doktor, string $tarih, string $saat, ?int $haricRandevuId = null): ?string
    {
        // 1. Check if doctor accepts online bookings
        if (! $doktor->randevuya_acik_mi) {
            return 'Hekimimiz online randevu alımına geçici olarak kapalıdır.';
        }

        $ayarlar = $doktor->randevuAyari;

        if ($ayarlar) {
            $randevuZamani = Carbon::parse($tarih.' '.$saat);

            // Check en_erken_randevu_saati (in hours)
            if ($ayarlar->en_erken_randevu_saati > 0) {
                $enErkenZaman = now()->addHours($ayarlar->en_erken_randevu_saati);
                if ($randevuZamani->lt($enErkenZaman)) {
                    return 'En erken '.$ayarlar->en_erken_randevu_saati.' saat sonrasına randevu alabilirsiniz.';
                }
            }

            // Check en_gec_randevu_gunu (in days)
            if ($ayarlar->en_gec_randevu_gunu > 0) {
                $enGecTarih = today()->addDays($ayarlar->en_gec_randevu_gunu);
                if ($randevuZamani->toDateTimeString() > $enGecTarih->endOfDay()->toDateTimeString()) {
                    return 'En fazla '.$ayarlar->en_gec_randevu_gunu.' gün sonrasına randevu alabilirsiniz.';
                }
            }

            // Check gunluk_maksimum_randevu
            if ($ayarlar->gunluk_maksimum_randevu > 0) {
                $gunlukRandevuSayisi = Randevu::where('doktor_id', $doktor->id)
                    ->whereDate('tarih', $tarih)
                    ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi'])
                    ->count();

                if ($gunlukRandevuSayisi >= $ayarlar->gunluk_maksimum_randevu) {
                    return 'Hekimimizin bu gün için günlük randevu limiti dolmuştur. Lütfen başka bir gün seçin.';
                }
            }
        }

        // 2. Double Booking check
        $cakismaQuery = Randevu::where('doktor_id', $doktor->id)
            ->whereDate('tarih', $tarih)
            ->where('saat', $saat)
            ->whereIn('durum', ['beklemede', 'onaylandi']);

        if ($haricRandevuId) {
            $cakismaQuery->where('id', '!=', $haricRandevuId);
        }

        if ($cakismaQuery->exists()) {
            return 'Seçtiğiniz randevu saati maalesef doludur. Lütfen başka bir saat seçin.';
        }

        // 3. Doctor leaves/blocks check
        $zamanString = $tarih.' '.$saat.':00';
        $izinli = DoktorIzin::where('doktor_id', $doktor->id)
            ->where('baslangic_zaman', '<=', $zamanString)
            ->where('bitis_zaman', '>=', $zamanString)
            ->exists();

        if ($izinli) {
            return 'Hekimimiz seçtiğiniz zaman diliminde hizmet dışıdır. Lütfen başka bir saat seçin.';
        }

        // 4. Working hours check
        $gunIndeksi = date('N', strtotime($tarih));
        $calismaSaati = DoktorCalismaSaati::where('doktor_id', $doktor->id)
            ->where('gun', $gunIndeksi)
            ->first();

        if (! $calismaSaati || ! $calismaSaati->aktif_mi) {
            return 'Hekimimiz seçtiğiniz günde hizmet vermemektedir.';
        }

        // Check time limits
        $saatVal = $saat.':00';
        if ($saatVal < $calismaSaati->mesai_baslangic || $saatVal >= $calismaSaati->mesai_bitis) {
            return 'Seçtiğiniz saat hekimimizin çalışma saatleri dışındadır.';
        }

        // Break time check
        if ($calismaSaati->ogle_arasi_aktif_mi) {
            if ($saatVal >= $calismaSaati->ogle_baslangic && $saatVal < $calismaSaati->ogle_bitis) {
                return 'Seçtiğiniz saat hekimimizin öğle molası aralığına denk gelmektedir.';
            }
        }

        return null; // Valid
    }
}
