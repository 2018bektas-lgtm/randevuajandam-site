<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use InvalidArgumentException;

/**
 * SMS OTP for guest booking phone verification.
 * Uses cache store; works with SMS_DRIVER=log in local.
 */
class RandevuOtpService
{
    public function __construct(
        protected SmsService $sms,
        protected AppointmentBookingService $booking,
    ) {}

    public function isRequired(): bool
    {
        return (bool) config('randevu.otp_required', false);
    }

    public function send(string $telefon, int $doktorId, string $ip): void
    {
        $phone = $this->booking->normalizePhone($telefon);
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        $ipKey = 'otp-send-ip:'.$ip;
        $phoneKey = 'otp-send-phone:'.$digits;

        if (RateLimiter::tooManyAttempts($ipKey, 10) || RateLimiter::tooManyAttempts($phoneKey, 5)) {
            throw new InvalidArgumentException('Çok fazla doğrulama kodu istendi. Lütfen daha sonra tekrar deneyin.');
        }

        $code = (string) random_int(100000, 999999);
        $cacheKey = $this->cacheKey($digits, $doktorId);

        // Store only hash of code (plain goes only via SMS)
        Cache::put($cacheKey, [
            'code_hash' => hash_hmac('sha256', $code, (string) config('app.key')),
            'attempts' => 0,
            'phone' => $phone,
        ], now()->addMinutes(5));

        $message = "Randevu dogrulama kodunuz: {$code}. 5 dakika gecerlidir.";
        $this->sms->send($phone, $message);

        RateLimiter::hit($ipKey, 3600);
        RateLimiter::hit($phoneKey, 3600);

        Log::info('Randevu OTP gönderildi', [
            'doktor_id' => $doktorId,
            'phone' => substr($digits, 0, 3).'****'.substr($digits, -2),
        ]);
    }

    public function verify(string $telefon, int $doktorId, string $code): bool
    {
        $phone = $this->booking->normalizePhone($telefon);
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        $cacheKey = $this->cacheKey($digits, $doktorId);
        $payload = Cache::get($cacheKey);

        $expectedHash = $payload['code_hash'] ?? null;
        // Legacy plain code support (in-flight OTPs before deploy)
        $legacyPlain = $payload['code'] ?? null;
        if (! is_array($payload) || (empty($expectedHash) && empty($legacyPlain))) {
            throw new InvalidArgumentException('Doğrulama kodunun süresi dolmuş. Lütfen yeni kod isteyin.');
        }

        $attempts = (int) ($payload['attempts'] ?? 0);
        if ($attempts >= 5) {
            Cache::forget($cacheKey);
            throw new InvalidArgumentException('Çok fazla hatalı deneme. Yeni kod isteyin.');
        }

        $provided = trim($code);
        $ok = false;
        if (is_string($expectedHash) && $expectedHash !== '') {
            $ok = hash_equals($expectedHash, hash_hmac('sha256', $provided, (string) config('app.key')));
        } elseif (is_string($legacyPlain) && $legacyPlain !== '') {
            $ok = hash_equals($legacyPlain, $provided);
        }

        if (! $ok) {
            $payload['attempts'] = $attempts + 1;
            Cache::put($cacheKey, $payload, now()->addMinutes(5));
            throw new InvalidArgumentException('Doğrulama kodu hatalı.');
        }

        // Mark verified for 15 minutes (used at booking)
        Cache::put($this->verifiedKey($digits, $doktorId), true, now()->addMinutes(15));
        Cache::forget($cacheKey);

        return true;
    }

    public function assertVerifiedIfRequired(string $telefon, int $doktorId): void
    {
        if (! $this->isRequired()) {
            return;
        }

        $phone = $this->booking->normalizePhone($telefon);
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (! Cache::get($this->verifiedKey($digits, $doktorId))) {
            throw new InvalidArgumentException('Randevu için telefon doğrulaması gerekli. Lütfen SMS kodunu doğrulayın.');
        }
    }

    public function clearVerified(string $telefon, int $doktorId): void
    {
        $phone = $this->booking->normalizePhone($telefon);
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        Cache::forget($this->verifiedKey($digits, $doktorId));
    }

    protected function cacheKey(string $digits, int $doktorId): string
    {
        return "randevu-otp:{$doktorId}:{$digits}";
    }

    protected function verifiedKey(string $digits, int $doktorId): string
    {
        return "randevu-otp-ok:{$doktorId}:{$digits}";
    }
}
