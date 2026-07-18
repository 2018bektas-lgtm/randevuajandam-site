<?php

namespace App\Http\Requests\Frontend;

use App\Rules\NoProfanity;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class YorumKaydetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('hasta')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'randevu_id' => ['required', 'integer', 'exists:randevular,id'],
            'puan' => ['required', 'integer', 'min:1', 'max:5'],
            'yorum' => ['required', 'string', 'min:10', 'max:1000', new NoProfanity],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'randevu_id.required' => 'Randevu bilgisi zorunludur.',
            'randevu_id.exists' => 'Geçersiz randevu bilgisi.',
            'puan.required' => 'Lütfen bir puan verin.',
            'puan.min' => 'Puan en az 1 olmalıdır.',
            'puan.max' => 'Puan en fazla 5 olabilir.',
            'yorum.required' => 'Yorum alanı zorunludur.',
            'yorum.min' => 'Yorum en az 10 karakter olmalıdır.',
            'yorum.max' => 'Yorum en fazla 1000 karakter olabilir.',
        ];
    }
}
