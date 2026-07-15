<?php

namespace App\Http\Requests\Yonetim;

use Illuminate\Foundation\Http\FormRequest;

class DoktorUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
            'e_posta' => ['required', 'email', 'max:255', 'unique:doktorlar,e_posta,'.$id],
            'telefon' => ['nullable', 'string', 'regex:/^0\s\(5[0-9]{2}\)\s[0-9]{3}\s[0-9]{2}\s[0-9]{2}$/'],
            'tur' => ['required', 'in:bireysel'],
            'klinik_adi' => ['nullable', 'string', 'max:255'],
            'il' => ['nullable', 'string', 'max:255'],
            'ilce' => ['nullable', 'string', 'max:255'],
            'paket_id' => ['nullable', 'exists:paketler,id'],
            'odeme_periyodu' => ['nullable', 'in:aylik,yillik'],
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
            'telefon.regex' => 'Telefon numarası 0 (5xx) xxx xx xx formatında olmalıdır.',
            'tur.required' => 'Tür seçimi zorunludur.',
            'paket_id.exists' => 'Seçilen paket geçersizdir.',
            'uyelik_bitis.after_or_equal' => 'Üyelik bitiş tarihi başlangıç tarihinden önce olamaz.',
            'sifre.min' => 'Şifre en az 8 karakter olmalıdır.',
        ];
    }
}
