<?php

namespace App\Support;

trait HasTwoFactorAuth
{
    public function hasTwoFactorEnabled(): bool
    {
        return ! empty($this->two_factor_secret) && $this->two_factor_confirmed_at !== null;
    }

    /**
     * @return list<string>
     */
    public function twoFactorRecoveryCodes(): array
    {
        $codes = $this->two_factor_recovery_codes;

        return is_array($codes) ? array_values($codes) : [];
    }
}
