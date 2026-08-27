<?php

namespace App\Services;

use App\Models\Doktor;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

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

        if ($randevular->isNotEmpty()) {
            $first = $randevular->first();
            if (is_object($first) && method_exists($first, 'relationLoaded') && ! $first->relationLoaded('hizmet') && method_exists($randevular, 'load')) {
                $randevular->load('hizmet');
            }
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

            // 3. Check Booked Appointments (excluding cancelled ones).
            // Hizmet süresi periyottan uzunsa sonraki slotlar da dolu sayılır (site + hekim sitesi aynı kural).
            $randevu = $randevular->first(function ($item) use ($gunTarih, $slotStart, $slotEnd, $periyot) {
                if (($item->durum ?? '') === 'iptal') {
                    return false;
                }
                $itemDate = Carbon::parse($item->tarih)->toDateString();
                if ($itemDate !== $gunTarih->toDateString()) {
                    return false;
                }
                $itemStart = substr((string) $item->saat, 0, 5);
                $sure = (int) (data_get($item, 'hizmet.sure') ?: $periyot);
                if ($sure < 1) {
                    $sure = $periyot;
                }
                try {
                    $itemEnd = Carbon::createFromFormat('H:i', $itemStart)->addMinutes($sure)->format('H:i');
                } catch (\Throwable) {
                    $itemEnd = $itemStart;
                }

                return $slotStart < $itemEnd && $slotEnd > $itemStart;
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
     * Sonuç 15 dk cache'lenir — liste sayfasında 12 hekim × 45 gün taramasını engeller.
     *
     * @return array{tarih: string, saat: string, label: string}|null
     */
    public function findNextAvailable(Doktor $doktor, int $maxScanDays = 21): ?array
    {
        if (! $doktor->randevuya_acik_mi) {
            return null;
        }

        $cacheKey = 'doktor_next_slot:v2:'.$doktor->id.':'.$maxScanDays.':'.now()->format('Y-m-d-H');

        /** @var array{tarih: string, saat: string, label: string}|null|false $cached */
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached === false ? null : $cached;
        }

        $result = $this->computeNextAvailable($doktor, $maxScanDays);
        // false sentinel = "no slot" so we still skip recompute
        Cache::put($cacheKey, $result ?? false, now()->addMinutes(15));

        return $result;
    }

    /**
     * Clear next-slot cache for a doctor (call after booking / cancel / schedule change).
     */
    public function forgetNextAvailableCache(int $doktorId): void
    {
        // Hourly key variants for current and previous hour (safety)
        $hour = now()->format('Y-m-d-H');
        $prev = now()->subHour()->format('Y-m-d-H');
        foreach ([14, 21, 30, 45] as $days) {
            Cache::forget("doktor_next_slot:v2:{$doktorId}:{$days}:{$hour}");
            Cache::forget("doktor_next_slot:v2:{$doktorId}:{$days}:{$prev}");
        }
    }

    /**
     * @return array{tarih: string, saat: string, label: string}|null
     */
    protected function computeNextAvailable(Doktor $doktor, int $maxScanDays = 21): ?array
    {
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
            ->with('hizmet')
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
     * Hizmet süresi periyottan uzunsa ardışık boş slot gerekir.
     *
     * @param  array<int, array{durum?:string, saat_string?:string, saat_bitis?:string}>  $gunluk
     * @return list<array{saat: string, saat_bitis: string}>
     */
    public function bosBaslangicSlotlari(array $gunluk, int $periyot, int $hizmetSure, ?string $minSaatExclusive = null): array
    {
        $needed = max(1, (int) ceil(max(1, $hizmetSure) / max(1, $periyot)));
        $out = [];
        foreach ($gunluk as $i => $slot) {
            if (($slot['durum'] ?? '') !== 'bos') {
                continue;
            }
            $saat = (string) ($slot['saat_string'] ?? '');
            if ($saat === '') {
                continue;
            }
            if ($minSaatExclusive !== null && $saat <= $minSaatExclusive) {
                continue;
            }
            $ok = true;
            for ($k = 1; $k < $needed; $k++) {
                $next = $gunluk[$i + $k] ?? null;
                if (! $next || ($next['durum'] ?? '') !== 'bos') {
                    $ok = false;
                    break;
                }
            }
            if (! $ok) {
                continue;
            }
            $out[] = [
                'saat' => $saat,
                'saat_bitis' => (string) ($slot['saat_bitis'] ?? ''),
            ];
        }

        return $out;
    }

    /**
     * Yeni randevu, mevcut randevuların hizmet süresiyle çakışıyor mu?
     * Site paneli ve hekim sitesi aynı kuralı kullanır.
     */
    public function existsOverlappingAppointment(
        Doktor $doktor,
        string $tarih,
        string $saat,
        int $sureDakika,
        ?int $haricRandevuId = null,
        bool $lock = false,
    ): bool {
        $saat = substr($saat, 0, 5);
        $periyot = $this->getPeriyot($doktor);
        $sure = max(1, $sureDakika > 0 ? $sureDakika : $periyot);
        try {
            $bitis = Carbon::createFromFormat('H:i', $saat)->addMinutes($sure)->format('H:i');
        } catch (\Throwable) {
            $bitis = $saat;
        }

        $q = $doktor->randevular()
            ->with('hizmet')
            ->whereDate('tarih', $tarih)
            ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi']);
        if ($haricRandevuId) {
            $q->where('id', '!=', $haricRandevuId);
        }
        if ($lock) {
            $q->lockForUpdate();
        }

        foreach ($q->get() as $item) {
            $itemStart = substr((string) $item->saat, 0, 5);
            $itemSure = (int) (data_get($item, 'hizmet.sure') ?: $periyot);
            if ($itemSure < 1) {
                $itemSure = $periyot;
            }
            try {
                $itemEnd = Carbon::createFromFormat('H:i', $itemStart)->addMinutes($itemSure)->format('H:i');
            } catch (\Throwable) {
                $itemEnd = $itemStart;
            }
            if ($saat < $itemEnd && $bitis > $itemStart) {
                return true;
            }
        }

        return false;
    }

    /**
     * Belirli aralıkta en az 1 müsait slotu olan günler (takvim için).
     *
     * @return list<string> Y-m-d
     */
    public function availableDatesInRange(Doktor $doktor, Carbon $from, Carbon $to, int $hizmetSure = 0): array
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
        if ($hizmetSure < 1) {
            $hizmetSure = $periyot;
        }
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
            ->with('hizmet')
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
            $minSaat = $cursor->isToday() ? now()->format('H:i') : null;
            foreach ($this->bosBaslangicSlotlari($slots, $periyot, $hizmetSure, $minSaat) as $bos) {
                if (Carbon::parse($key.' '.$bos['saat'])->lt($enErkenZaman)) {
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
