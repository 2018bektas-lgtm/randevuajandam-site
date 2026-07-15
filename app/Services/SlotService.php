<?php

namespace App\Services;

use App\Models\Doktor;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Generates and checks time slots for a doctor based on working hours,
 * lunch breaks, leaves, and existing appointments.
 */
class SlotService
{
    /**
     * Get the appointment period (in minutes) for a doctor.
     */
    public function getPeriyot(Doktor $doktor): int
    {
        $ayarlar = $doktor->randevuAyari;
        $periyot = $ayarlar ? (int) $ayarlar->randevu_periyodu : 30;

        return $periyot > 0 ? $periyot : 30;
    }

    /**
     * Generate time slots for a single day.
     *
     * @return array<int, array{saat_baslangic: string, saat_bitis: string, saat_string: string, durum: string, randevu: mixed, izin_aciklama: string}>
     */
    public function generateGunlukSlotlar(
        Doktor $doktor,
        Carbon $gunTarih,
        Collection $randevular,
        Collection $izinler,
        int $periyot,
    ): array {
        $gunIndeksi = (int) $gunTarih->format('N');
        $cs = $doktor->calismaSaatleri()->where('gun', $gunIndeksi)->first();

        if (! $cs || ! $cs->aktif_mi) {
            return [];
        }

        $slots = [];
        $current = Carbon::parse($cs->mesai_baslangic);
        $end = Carbon::parse($cs->mesai_bitis);

        while ($current->lt($end)) {
            $slotStart = $current->format('H:i');
            $current = $current->addMinutes($periyot);
            $slotEnd = $current->format('H:i');

            if ($current->gt($end)) {
                break;
            }

            $slotTimeString = $slotStart;

            // 1. Check Lunch Break
            $isLunch = $this->isOgleArasi($cs, $slotTimeString);

            // 2. Check Leaves
            $izinSonuc = $this->checkIzin($izinler, $gunTarih, $slotTimeString);

            // 3. Check Booked Appointments (excluding cancelled ones)
            $randevu = $randevular->first(function ($item) use ($gunTarih, $slotTimeString) {
                $itemDate = Carbon::parse($item->tarih)->toDateString();
                $itemTime = substr($item->saat, 0, 5);

                return $itemDate === $gunTarih->toDateString() && $itemTime === $slotTimeString && $item->durum !== 'iptal';
            });

            $slots[] = [
                'saat_baslangic' => $slotStart,
                'saat_bitis' => $slotEnd,
                'saat_string' => $slotTimeString,
                'durum' => $isLunch ? 'ogle' : ($izinSonuc['izinli'] ? 'izin' : ($randevu ? 'dolu' : 'bos')),
                'randevu' => $randevu,
                'izin_aciklama' => $izinSonuc['aciklama'],
            ];
        }

        return $slots;
    }

    /**
     * Check if a time string falls within the lunch break.
     */
    public function isOgleArasi(mixed $calismaSaati, string $saat): bool
    {
        if (! $calismaSaati->ogle_arasi_aktif_mi || ! $calismaSaati->ogle_baslangic || ! $calismaSaati->ogle_bitis) {
            return false;
        }

        $lunchStart = Carbon::parse($calismaSaati->ogle_baslangic)->format('H:i');
        $lunchEnd = Carbon::parse($calismaSaati->ogle_bitis)->format('H:i');

        return $saat >= $lunchStart && $saat < $lunchEnd;
    }

    /**
     * Check if a slot overlaps with any leave period.
     *
     * @return array{izinli: bool, aciklama: string}
     */
    public function checkIzin(Collection $izinler, Carbon $gunTarih, string $saat): array
    {
        $slotDateTimeStr = $gunTarih->toDateString().' '.$saat.':00';

        foreach ($izinler as $izin) {
            if ($slotDateTimeStr >= $izin->baslangic_zaman->toDateTimeString() &&
                $slotDateTimeStr < $izin->bitis_zaman->toDateTimeString()) {
                return ['izinli' => true, 'aciklama' => $izin->aciklama ?? 'İzinli'];
            }
        }

        return ['izinli' => false, 'aciklama' => ''];
    }
}
