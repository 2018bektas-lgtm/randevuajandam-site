<?php

namespace App\Policies;

use App\Models\Doktor;
use App\Models\Klinik;

class KlinikPolicy
{
    /**
     * Determine whether the doctor can view the clinic.
     */
    public function goruntuleyebilir(Doktor $doktor, Klinik $klinik): bool
    {
        return $doktor->klinik_id === $klinik->id;
    }

    /**
     * Determine whether the doctor can update the clinic.
     */
    public function duzenleyebilir(Doktor $doktor, Klinik $klinik): bool
    {
        return $doktor->klinik_id === $klinik->id && $doktor->klinikSahibiMi();
    }

    /**
     * Determine whether the doctor can invite other doctors.
     */
    public function doktorEkleyebilir(Doktor $doktor, Klinik $klinik): bool
    {
        return $doktor->klinik_id === $klinik->id && $doktor->klinikSahibiMi() && ! $klinik->doktorLimitiDolduMu();
    }

    /**
     * Determine whether the doctor can manage staff members.
     */
    public function personelYonetebilir(Doktor $doktor, Klinik $klinik): bool
    {
        return $doktor->klinik_id === $klinik->id && $doktor->klinikSahibiMi();
    }

    /**
     * Determine whether the doctor can view financial reports.
     */
    public function finansGorebilir(Doktor $doktor, Klinik $klinik): bool
    {
        return $doktor->klinik_id === $klinik->id && $doktor->klinikSahibiMi() && (bool) $klinik->paket?->merkezi_finans_mi;
    }

    /**
     * Determine whether the doctor can delete the clinic.
     */
    public function silebilir(Doktor $doktor, Klinik $klinik): bool
    {
        return $doktor->klinik_id === $klinik->id && $doktor->klinikSahibiMi();
    }
}
