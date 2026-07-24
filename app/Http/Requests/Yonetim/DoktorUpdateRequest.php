<?php

namespace App\Http\Requests\Yonetim;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DoktorUpdateRequest extends FormRequest
{
    public const KLINIK_YETKI_ANAHTARLARI = [
        'yonetim_paneli',
        'klinik_ayarlari',
        'hekim_yonetimi',
        'personel_yonetimi',
        'finans_yonetimi',
        'hakedis_yonetimi',
        'ortak_hasta_havuzu',
        'duyuru_yonetimi',
    ];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Boş stringleri null yap (nullable + in: kuralları için)
        $nullable = [
            'telefon',
            'paket_id',
            'odeme_periyodu',
            'uyelik_baslangic',
            'uyelik_bitis',
            'sifre',
            'unvan',
            'klinik_adi',
            'il',
            'ilce',
            'klinik_id',
            'klinik_rolu',
            'komisyon_orani',
        ];
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
        $klinikSecili = filled($this->input('klinik_id'));

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

            // Klinik üyelik (yönetim paneli tam kontrol)
            'klinik_id' => ['nullable', 'exists:klinikler,id'],
            'klinik_rolu' => [
                Rule::requiredIf($klinikSecili),
                'nullable',
                'in:doktor,ortak,sahip',
            ],
            'klinik_aktif_mi' => ['nullable', 'boolean'],
            'komisyon_orani' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'klinik_yetkileri' => ['nullable', 'array'],
            'klinik_yetkileri.*' => ['nullable'],
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
            'klinik_id.exists' => 'Seçilen klinik geçersizdir.',
            'klinik_rolu.required' => 'Kliniğe bağlarken klinik rolü seçmelisiniz.',
            'klinik_rolu.in' => 'Klinik rolü hekim, ortak veya sahip olmalıdır.',
            'komisyon_orani.numeric' => 'Komisyon oranı sayısal olmalıdır.',
            'komisyon_orani.min' => 'Komisyon oranı 0\'dan küçük olamaz.',
            'komisyon_orani.max' => 'Komisyon oranı 100\'den büyük olamaz.',
        ];
    }
}
