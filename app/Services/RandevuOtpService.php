<?php

namespace App\Services;

/**
 * @deprecated Use PhoneOtpService. Kept as thin adapter for any legacy callers.
 */
class RandevuOtpService
{
    public function __construct(
        protected PhoneOtpService $otp,
    ) {}

    public function isRequired(): bool
    {
        return $this->otp->isRequired();
    }

    public function send(string $telefon, int $doktorId, string $ip): void
    {
        $this->otp->send($telefon, $ip, 'randevu', $doktorId);
    }

    public function verify(string $telefon, int $doktorId, string $code): bool
    {
        return $this->otp->verify($telefon, $code, 'randevu', $doktorId);
    }

    public function assertVerifiedIfRequired(string $telefon, int $doktorId): void
    {
        $this->otp->assertVerifiedIfRequired($telefon, 'randevu', $doktorId);
    }

    public function clearVerified(string $telefon, int $doktorId): void
    {
        $this->otp->clearVerified($telefon, 'randevu', $doktorId);
    }
}
