<?php

namespace App\Http\Requests\Frontend;

use App\Rules\TurkishMobilePhone;
use Illuminate\Foundation\Http\FormRequest;

class HastaKayitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('telefon')) {
            $this->merge([
                'telefon' => TurkishMobilePhone::normalize($this->input('telefon')),
            ]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'ad' => ['required', 'string', 'max:255'],
            'soyad' => ['required', 'string', 'max:255'],
            'e_posta' => ['required', 'email', 'unique:hastalar,e_posta'],
            'telefon' => ['required', 'string', new TurkishMobilePhone],
            'sifre' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ad.required' => 'Ad alanı zorunludur.',
            'soyad.required' => 'Soyad alanı zorunludur.',
            'e_posta.required' => 'E-posta adresi zorunludur.',
            'e_posta.email' => 'Geçerli bir e-posta adresi giriniz.',
            'e_posta.unique' => 'Bu e-posta adresi zaten kullanımda.',
            'telefon.required' => 'Telefon numarası zorunludur.',
            'sifre.required' => 'Şifre alanı zorunludur.',
            'sifre.min' => 'Şifre en az 8 karakter olmalıdır.',
            'sifre.confirmed' => 'Şifreler uyuşmuyor.',
        ];
    }
}
