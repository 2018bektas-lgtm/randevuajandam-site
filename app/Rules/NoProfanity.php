<?php

namespace App\Rules;

use App\Services\ProfanityFilter;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoProfanity implements ValidationRule
{
    public function __construct(
        protected ?string $message = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value) && ! is_numeric($value)) {
            return;
        }

        if (app(ProfanityFilter::class)->contains((string) $value)) {
            $fail($this->message ?? 'Metinde uygunsuz veya hakaret içeren ifadeler tespit edildi. Lütfen düzgün bir dil kullanın.');
        }
    }
}
