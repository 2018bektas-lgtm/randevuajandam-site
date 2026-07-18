<?php

namespace App\Services;

use App\Rules\TurkishMobilePhone;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use InvalidArgumentException;

/**
 * SMS OTP for guest phone verification (randevu + hasta kaydı).
 */
class PhoneOtpService
{
    public function __construct(
        protected SmsService $sms,
    ) {}

    public function isRequired(): bool
    {
        return (bool) config('randevu.otp_required', true);
    }

    /**
     * @param  string  $purpose  "randevu" | "kayit"
     */
    public function send(string $telefon, string $ip, string $purpose = 'randevu', ?int $doktorId = null): void
    {
        $phone = $this->assertPhone($telefon);
        $digits = $phone;
        $purpose = $this->normalizePurpose($purpose);

        $ipKey = 'otp-send-ip:'.$ip;
        $phoneKey = 'otp-send-phone:'.$purpose.':'.$digits;

        if (RateLimiter::tooManyAttempts($ipKey, 12) || RateLimiter::tooManyAttempts($phoneKey, 5)) {
            throw new InvalidArgumentException('Çok fazla doğrulama kodu istendi. Lütfen daha sonra tekrar deneyin.');
        }

        $code = (string) random_int(100000, 999999);
        $cacheKey = $this->cacheKey($digits, $purpose, $doktorId);

        Cache::put($cacheKey, [
            'code_hash' => hash_hmac('sha256', $code, (string) config('app.key')),
            'attempts' => 0,
            'phone' => $phone,
        ], now()->addMinutes(5));

        $label = $purpose === 'kayit' ? 'Kayit' : 'Randevu';
        $message = "{$label} dogrulama kodunuz: {$code}. 5 dakika gecerlidir. Randevu Ajandam";
        $sent = $this->sms->send($phone, $message);

        if (! $sent && app()->environment('production')) {
            Cache::forget($cacheKey);
            throw new InvalidArgumentException('SMS gönderilemedi. Lütfen daha sonra tekrar deneyin.');
        }

        RateLimiter::hit($ipKey, 3600);
        RateLimiter::hit($phoneKey, 3600);

        Log::info('Phone OTP gönderildi', [
            'purpose' => $purpose,
            'doktor_id' => $doktorId,
            'phone' => substr($digits, 0, 3).'****'.substr($digits, -2),
        ]);
    }

    public function verify(string $telefon, string $code, string $purpose = 'randevu', ?int $doktorId = null): bool
    {
        $phone = $this->assertPhone($telefon);
        $digits = $phone;
        $purpose = $this->normalizePurpose($purpose);
        $cacheKey = $this->cacheKey($digits, $purpose, $doktorId);
        $payload = Cache::get($cacheKey);

        $expectedHash = $payload['code_hash'] ?? null;
        $legacyPlain = is_array($payload) ? ($payload['code'] ?? null) : null;

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

        Cache::put($this->verifiedKey($digits, $purpose, $doktorId), true, now()->addMinutes(20));
        Cache::forget($cacheKey);

        return true;
    }

    public function assertVerifiedIfRequired(string $telefon, string $purpose = 'randevu', ?int $doktorId = null): void
    {
        if (! $this->isRequired()) {
            return;
        }

        $phone = $this->assertPhone($telefon);
        $purpose = $this->normalizePurpose($purpose);

        if (! Cache::get($this->verifiedKey($phone, $purpose, $doktorId))) {
            throw new InvalidArgumentException('Telefon doğrulaması gerekli. Lütfen SMS kodunu doğrulayın.');
        }
    }

    public function clearVerified(string $telefon, string $purpose = 'randevu', ?int $doktorId = null): void
    {
        try {
            $phone = $this->assertPhone($telefon);
        } catch (InvalidArgumentException) {
            return;
        }
        Cache::forget($this->verifiedKey($phone, $this->normalizePurpose($purpose), $doktorId));
    }

    public function assertPhone(string $telefon): string
    {
        $phone = TurkishMobilePhone::normalize($telefon);
        if (! preg_match('/^05[0-9]{9}$/', $phone)) {
            throw new InvalidArgumentException('Telefon numarası 05 ile başlamalı ve 11 haneli olmalıdır.');
        }

        return $phone;
    }

    protected function normalizePurpose(string $purpose): string
    {
        return in_array($purpose, ['randevu', 'kayit'], true) ? $purpose : 'randevu';
    }

    protected function cacheKey(string $digits, string $purpose, ?int $doktorId): string
    {
        $scope = $doktorId ? (string) $doktorId : '0';

        return "phone-otp:{$purpose}:{$scope}:{$digits}";
    }

    protected function verifiedKey(string $digits, string $purpose, ?int $doktorId): string
    {
        $scope = $doktorId ? (string) $doktorId : '0';

        return "phone-otp-ok:{$purpose}:{$scope}:{$digits}";
    }
}
