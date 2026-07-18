<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Türkiye cep telefonu: yalnızca rakam, 05 ile başlar, toplam 11 hane (05xxxxxxxxx).
 */
class TurkishMobilePhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = preg_replace('/\D+/', '', (string) $value) ?? '';

        if ($digits === '' || ! preg_match('/^05[0-9]{9}$/', $digits)) {
            $fail('Telefon numarası 05 ile başlamalı ve 11 haneli olmalıdır (ör. 05xxxxxxxxx). Yalnızca rakam giriniz.');
        }
    }

    /**
     * Normalize to 05xxxxxxxxx or empty string if invalid.
     */
    public static function normalize(mixed $value): string
    {
        $digits = preg_replace('/\D+/', '', (string) $value) ?? '';

        if (str_starts_with($digits, '90') && strlen($digits) === 12) {
            $digits = '0'.substr($digits, 2);
        } elseif (strlen($digits) === 10 && str_starts_with($digits, '5')) {
            $digits = '0'.$digits;
        }

        return preg_match('/^05[0-9]{9}$/', $digits) ? $digits : $digits;
    }

    public static function isValid(mixed $value): bool
    {
        $digits = self::normalize($value);

        return (bool) preg_match('/^05[0-9]{9}$/', $digits);
    }
}
