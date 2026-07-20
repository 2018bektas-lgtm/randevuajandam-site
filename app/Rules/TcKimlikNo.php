<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * T.C. Kimlik No algoritma doğrulaması (11 hane).
 */
class TcKimlikNo implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $tc = preg_replace('/\D/', '', (string) $value) ?? '';

        if (strlen($tc) !== 11) {
            $fail('T.C. kimlik numarası 11 haneli olmalıdır.');

            return;
        }

        if ($tc[0] === '0') {
            $fail('T.C. kimlik numarası 0 ile başlayamaz.');

            return;
        }

        if (! ctype_digit($tc)) {
            $fail('T.C. kimlik numarası yalnızca rakam içermelidir.');

            return;
        }

        $digits = array_map('intval', str_split($tc));
        $odd = $digits[0] + $digits[2] + $digits[4] + $digits[6] + $digits[8];
        $even = $digits[1] + $digits[3] + $digits[5] + $digits[7];
        $d10 = (($odd * 7) - $even) % 10;
        if ($d10 < 0) {
            $d10 += 10;
        }
        $d11 = array_sum(array_slice($digits, 0, 10)) % 10;

        if ($digits[9] !== $d10 || $digits[10] !== $d11) {
            $fail('Geçersiz T.C. kimlik numarası.');
        }
    }
}
