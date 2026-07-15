<?php

namespace App\Policies;

use App\Models\Doktor;
use App\Models\Hasta;
use App\Models\Randevu;

class RandevuPolicy
{
    /**
     * Determine whether the doctor can view the appointment.
     */
    public function doktorGoruntuleyebilir(Doktor $doktor, Randevu $randevu): bool
    {
        return $doktor->id === $randevu->doktor_id;
    }

    /**
     * Determine whether the doctor can update the appointment.
     */
    public function doktorGuncelleyebilir(Doktor $doktor, Randevu $randevu): bool
    {
        return $doktor->id === $randevu->doktor_id;
    }

    /**
     * Determine whether the doctor can delete the appointment.
     */
    public function doktorSilebilir(Doktor $doktor, Randevu $randevu): bool
    {
        return $doktor->id === $randevu->doktor_id;
    }

    /**
     * Determine whether the patient can cancel the appointment.
     */
    public function hastaIptalEdebilir(Hasta $hasta, Randevu $randevu): bool
    {
        return $hasta->id === $randevu->hasta_id;
    }
}
