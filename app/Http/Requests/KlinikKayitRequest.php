<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KlinikKayitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Klinik Bilgileri
            'klinik_adi' => 'required|string|max:255',
            'telefon' => 'required|string',
            'e_posta' => 'nullable|email|max:255',
            'adres' => 'required|string',
            'il_id' => 'required|exists:iller,id',
            'ilce_id' => 'required|string|max:255|exists:ilceler,ad',

            // Doktor (Sahip) Bilgileri
            'ad_soyad' => 'required|string|max:255',
            'doktor_eposta' => 'required|email|max:255|unique:doktorlar,e_posta',
            'sifre' => [
                'required',
                'string',
                'min:8',
                'regex:~^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>_\-#\[\]\\\/]).+$~',
                'confirmed',
            ],
            'doktor_telefon' => ['required', 'string', 'regex:/^0\s\(5[0-9]{2}\)\s[0-9]{3}\s[0-9]{2}\s[0-9]{2}$/'],
            'unvan' => 'required|string|exists:unvanlar,ad',
            'branslar' => 'required|array|min:1',
            'branslar.*' => 'exists:branslar,id',

            // Paket (kartlı ödeme PayTR iframe — bu formda kart yok)
            'paket_id' => 'required|exists:paketler,id',
            'odeme_periyodu' => 'required|in:aylik,yillik',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'klinik_adi.required' => 'Klinik adı alanı zorunludur.',
            'telefon.required' => 'Klinik telefon numarası zorunludur.',
            'adres.required' => 'Klinik adresi zorunludur.',
            'il_id.required' => 'Hizmet verilen il seçimi zorunludur.',
            'ilce_id.required' => 'Hizmet verilen ilçe seçimi zorunludur.',
            'ad_soyad.required' => 'Ad Soyad alanı zorunludur.',
            'doktor_eposta.required' => 'Doktor e-posta adresi zorunludur.',
            'doktor_eposta.email' => 'Lütfen geçerli bir e-posta adresi girin.',
            'doktor_eposta.unique' => 'Bu e-posta adresi zaten sisteme kayıtlı.',
            'sifre.required' => 'Şifre alanı zorunludur.',
            'sifre.min' => 'Şifre en az 8 karakter olmalıdır.',
            'sifre.regex' => 'Şifreniz en az bir büyük harf, bir küçük harf, bir sayı ve bir özel karakter içermelidir.',
            'sifre.confirmed' => 'Şifre tekrarı uyuşmuyor.',
            'doktor_telefon.required' => 'Doktor telefon numarası zorunludur.',
            'doktor_telefon.regex' => 'Telefon numarası 0 (5xx) xxx xx xx formatında olmalıdır.',
            'unvan.required' => 'Mesleki unvan seçimi zorunludur.',
            'branslar.required' => 'En az bir uzmanlık alanı / branş seçmelisiniz.',
            'paket_id.exists' => 'Lütfen geçerli bir üyelik paketi seçin.',
            'odeme_periyodu.in' => 'Ödeme periyodu aylık veya yıllık olmalıdır.',
        ];
    }
}
