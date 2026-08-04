<?php

namespace App\Services;

use App\Models\Doktor;
use App\Models\Klinik;
use App\Models\Paket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Paket bazlı aylık SMS kontörü (Excel: sms_aylik_kontor).
 * Klinik hekiminde klinik paketi kontörü kullanılır.
 */
class SmsKontorService
{
    public function donem(): string
    {
        return now()->format('Y-m');
    }

    public function paketForDoktor(Doktor $doktor): ?Paket
    {
        return $doktor->aktifPaket();
    }

    /**
     * SMS gönderebilir mi? (özellik + kontör)
     */
    public function doktorGonderebilir(Doktor $doktor): bool
    {
        $paket = $this->paketForDoktor($doktor);
        if (! $paket || ! $paket->hasFeature('sms_hatirlatma')) {
            return false;
        }

        return $this->kalan($doktor) === null || $this->kalan($doktor) > 0;
    }

    public function kullanilan(Doktor $doktor): int
    {
        $klinik = $doktor->klinik_id ? $doktor->klinik : null;
        if ($klinik) {
            $this->resetDonemIfNeeded($klinik);

            return (int) $klinik->sms_kullanim_adet;
        }

        $this->resetDonemIfNeeded($doktor);

        return (int) $doktor->sms_kullanim_adet;
    }

    public function ekKontor(Doktor $doktor): int
    {
        $klinik = $doktor->klinik_id ? $doktor->klinik : null;
        if ($klinik) {
            return (int) ($klinik->sms_ek_kontor ?? 0);
        }

        return (int) ($doktor->sms_ek_kontor ?? 0);
    }

    public function kalan(Doktor $doktor): ?int
    {
        $paket = $this->paketForDoktor($doktor);
        if (! $paket || ! $paket->hasFeature('sms_hatirlatma')) {
            return 0;
        }
        if ($paket->sms_aylik_kontor === null && $this->ekKontor($doktor) <= 0) {
            // Sınırsız paket kotası
            return null;
        }

        $paketKota = (int) ($paket->sms_aylik_kontor ?? 0);
        $toplam = $paketKota + $this->ekKontor($doktor);

        return max(0, $toplam - $this->kullanilan($doktor));
    }

    /**
     * Başarılı SMS sonrası 1 kontör düş (önce paket kotası, sonra ek kontör).
     */
    public function tuket(Doktor $doktor, int $adet = 1): bool
    {
        if (! $this->doktorGonderebilir($doktor)) {
            Log::info('SMS kontör yetersiz veya özellik yok', ['doktor_id' => $doktor->id]);

            return false;
        }

        $klinik = $doktor->klinik_id ? $doktor->klinik : null;
        $owner = $klinik ?: $doktor;
        $this->resetDonemIfNeeded($owner);

        $paket = $this->paketForDoktor($doktor);
        $paketKota = (int) ($paket?->sms_aylik_kontor ?? 0);
        $kullanilan = (int) $owner->sms_kullanim_adet;
        $kalanPaket = max(0, $paketKota - $kullanilan);

        if ($kalanPaket >= $adet || $paket?->sms_aylik_kontor === null) {
            $owner->sms_kullanim_adet = $kullanilan + $adet;
            $owner->save();

            return true;
        }

        // Paket bitti → ek kontörden düş
        $gerekliEk = $adet - $kalanPaket;
        $ek = (int) ($owner->sms_ek_kontor ?? 0);
        if ($ek < $gerekliEk) {
            return false;
        }
        $owner->sms_kullanim_adet = $kullanilan + $kalanPaket;
        $owner->sms_ek_kontor = $ek - $gerekliEk;
        $owner->save();

        return true;
    }

    public function ekKontorEkle(Model $owner, int $adet): void
    {
        $owner->sms_ek_kontor = (int) ($owner->sms_ek_kontor ?? 0) + max(0, $adet);
        $owner->save();
    }

    protected function resetDonemIfNeeded(Doktor|Klinik $owner): void
    {
        $donem = $this->donem();
        if (($owner->sms_kullanim_donem ?? null) !== $donem) {
            $owner->sms_kullanim_donem = $donem;
            $owner->sms_kullanim_adet = 0;
            $owner->save();
        }
    }
}
