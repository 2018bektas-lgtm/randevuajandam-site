<?php

namespace App\Policies;

use App\Models\Doktor;
use App\Models\Hizmet;

class HizmetPolicy
{
    /**
     * Determine whether the doctor can view the service.
     */
    public function view(Doktor $doktor, Hizmet $hizmet): bool
    {
        return $doktor->id === $hizmet->doktor_id;
    }

    /**
     * Determine whether the doctor can update the service.
     */
    public function update(Doktor $doktor, Hizmet $hizmet): bool
    {
        return $doktor->id === $hizmet->doktor_id;
    }

    /**
     * Determine whether the doctor can delete the service.
     */
    public function delete(Doktor $doktor, Hizmet $hizmet): bool
    {
        return $doktor->id === $hizmet->doktor_id;
    }
}
