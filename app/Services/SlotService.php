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
        $cs = $doktor->relationLoaded('calismaSaatleri')
            ? $doktor->calismaSaatleri->firstWhere('gun', $gunIndeksi)
            : $doktor->calismaSaatleri()->where('gun', $gunIndeksi)->first();

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

    /**
     * En yakın müsait randevu slotu (liste kartları için).
     *
     * @return array{tarih: string, saat: string, label: string}|null
     */
    public function findNextAvailable(Doktor $doktor, int $maxScanDays = 45): ?array
    {
        if (! $doktor->randevuya_acik_mi) {
            return null;
        }

        $ayarlar = $doktor->randevuAyari;
        $periyot = $this->getPeriyot($doktor);
        $maxDays = (int) ($ayarlar->en_gec_randevu_gunu ?? 30);
        if ($maxDays <= 0) {
            $maxDays = 30;
        }
        $maxDays = min($maxDays, max(1, $maxScanDays));

        $enErkenSaat = (int) ($ayarlar->en_erken_randevu_saati ?? 0);
        $enErkenZaman = now()->addHours(max(0, $enErkenSaat));

        $izinler = method_exists($doktor, 'izinler')
            ? $doktor->izinler()->get()
            : collect();

        $start = today();
        $end = today()->copy()->addDays($maxDays);

        $randevularByDate = $doktor->randevular()
            ->whereDate('tarih', '>=', $start->toDateString())
            ->whereDate('tarih', '<=', $end->toDateString())
            ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi'])
            ->get()
            ->groupBy(function ($r) {
                return Carbon::parse($r->tarih)->toDateString();
            });

        for ($d = 0; $d <= $maxDays; $d++) {
            $gun = today()->copy()->addDays($d);
            $key = $gun->toDateString();
            /** @var Collection $dayRandevular */
            $dayRandevular = $randevularByDate->get($key, collect());

            $slots = $this->generateGunlukSlotlar($doktor, $gun, $dayRandevular, $izinler, $periyot);
            foreach ($slots as $slot) {
                if (($slot['durum'] ?? '') !== 'bos') {
                    continue;
                }
                $saat = (string) ($slot['saat_string'] ?? '');
                if ($saat === '') {
                    continue;
                }
                $slotDt = Carbon::parse($key.' '.$saat);
                if ($slotDt->lt($enErkenZaman)) {
                    continue;
                }

                $label = $gun->locale('tr')->translatedFormat('d M Y').' · '.$saat;

                return [
                    'tarih' => $key,
                    'saat' => $saat,
                    'label' => $label,
                ];
            }
        }

        return null;
    }

    /**
     * Belirli aralıkta en az 1 müsait slotu olan günler (takvim için).
     *
     * @return list<string> Y-m-d
     */
    public function availableDatesInRange(Doktor $doktor, Carbon $from, Carbon $to): array
    {
        if (! $doktor->randevuya_acik_mi) {
            return [];
        }

        $from = $from->copy()->startOfDay();
        $to = $to->copy()->startOfDay();
        if ($to->lt($from)) {
            return [];
        }

        $ayarlar = $doktor->randevuAyari;
        $periyot = $this->getPeriyot($doktor);
        $enErkenSaat = (int) ($ayarlar->en_erken_randevu_saati ?? 0);
        $enErkenZaman = now()->addHours(max(0, $enErkenSaat));

        $maxDaysSetting = (int) ($ayarlar->en_gec_randevu_gunu ?? 0);
        if ($maxDaysSetting > 0) {
            $hardEnd = today()->copy()->addDays($maxDaysSetting);
            if ($to->gt($hardEnd)) {
                $to = $hardEnd;
            }
        }

        $izinler = method_exists($doktor, 'izinler')
            ? $doktor->izinler()->get()
            : collect();

        $randevularByDate = $doktor->randevular()
            ->whereDate('tarih', '>=', $from->toDateString())
            ->whereDate('tarih', '<=', $to->toDateString())
            ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi'])
            ->get()
            ->groupBy(fn ($r) => Carbon::parse($r->tarih)->toDateString());

        $available = [];
        $cursor = $from->copy();
        while ($cursor->lte($to)) {
            $key = $cursor->toDateString();
            $dayRandevular = $randevularByDate->get($key, collect());
            $slots = $this->generateGunlukSlotlar($doktor, $cursor, $dayRandevular, $izinler, $periyot);
            foreach ($slots as $slot) {
                if (($slot['durum'] ?? '') !== 'bos') {
                    continue;
                }
                $saat = (string) ($slot['saat_string'] ?? '');
                if ($saat === '') {
                    continue;
                }
                if (Carbon::parse($key.' '.$saat)->lt($enErkenZaman)) {
                    continue;
                }
                $available[] = $key;
                break;
            }
            $cursor->addDay();
        }

        return $available;
    }

    /**
     * Slot "bos" olsa bile en_erken kuralına uymuyorsa seçilemez.
     */
    public function isSlotSelectable(Doktor $doktor, string $tarih, string $saat): bool
    {
        $ayarlar = $doktor->randevuAyari;
        $enErkenSaat = (int) ($ayarlar->en_erken_randevu_saati ?? 0);
        if ($enErkenSaat <= 0) {
            return true;
        }

        return Carbon::parse($tarih.' '.$saat)->gte(now()->addHours($enErkenSaat));
    }
}
