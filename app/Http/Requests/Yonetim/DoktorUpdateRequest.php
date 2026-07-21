<?php

namespace App\Http\Requests\Yonetim;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DoktorUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Boş stringleri null yap (nullable + in: kuralları için)
        $nullable = ['telefon', 'paket_id', 'odeme_periyodu', 'uyelik_baslangic', 'uyelik_bitis', 'sifre', 'unvan', 'klinik_adi', 'il', 'ilce'];
        $merge = [];
        foreach ($nullable as $key) {
            if ($this->has($key) && $this->input($key) === '') {
                $merge[$key] = null;
            }
        }
        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'unvan' => ['nullable', 'string', 'max:50'],
            'ad_soyad' => ['required', 'string', 'max:255'],
            'e_posta' => ['required', 'email', 'max:255', Rule::unique('doktorlar', 'e_posta')->ignore($id)],
            // Admin paneli: esnek telefon (maske zorunlu değil)
            'telefon' => ['nullable', 'string', 'max:30'],
            'tur' => ['required', 'in:bireysel,klinik'],
            'klinik_adi' => ['nullable', 'string', 'max:255'],
            'il' => ['nullable', 'string', 'max:255'],
            'ilce' => ['nullable', 'string', 'max:255'],
            'paket_id' => ['nullable', 'exists:paketler,id'],
            'odeme_periyodu' => ['nullable', 'in:aylik,yillik,deneme'],
            'uyelik_baslangic' => ['nullable', 'date'],
            'uyelik_bitis' => ['nullable', 'date', 'after_or_equal:uyelik_baslangic'],
            'aktif_mi' => ['nullable', 'boolean'],
            'platformda_gorunur' => ['nullable', 'boolean'],
            'sifre' => ['nullable', 'string', 'min:8'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ad_soyad.required' => 'Ad soyad alanı zorunludur.',
            'e_posta.required' => 'E-posta adresi zorunludur.',
            'e_posta.email' => 'Lütfen geçerli bir e-posta adresi girin.',
            'e_posta.unique' => 'Bu e-posta adresi zaten başka bir doktor tarafından kullanılıyor.',
            'tur.required' => 'Tür seçimi zorunludur.',
            'tur.in' => 'Tür bireysel veya klinik olmalıdır.',
            'paket_id.exists' => 'Seçilen paket geçersizdir.',
            'odeme_periyodu.in' => 'Ödeme periyodu aylık, yıllık veya deneme olmalıdır.',
            'uyelik_bitis.after_or_equal' => 'Üyelik bitiş tarihi başlangıç tarihinden önce olamaz.',
            'sifre.min' => 'Şifre en az 8 karakter olmalıdır.',
        ];
    }
}
