<?php

namespace App\Http\Requests\Yonetim;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaketStoreRequest extends FormRequest
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
        return [
            'ad' => ['required', 'string', 'max:255'],
            'tur' => ['required', 'in:bireysel,klinik'],
            'aciklama' => ['nullable', 'string'],
            'aylik_fiyat' => ['required', 'numeric', 'min:0'],
            'aylik_indirimli_fiyat' => ['nullable', 'numeric', 'min:0'],
            'yillik_fiyat' => ['required', 'numeric', 'min:0'],
            'yillik_indirimli_fiyat' => ['nullable', 'numeric', 'min:0'],
            'ek_doktor_aylik_fiyat' => ['nullable', 'numeric', 'min:0'],
            'ek_doktor_yillik_fiyat' => ['nullable', 'numeric', 'min:0'],
            'ek_personel_aylik_fiyat' => ['nullable', 'numeric', 'min:0'],
            'ek_personel_yillik_fiyat' => ['nullable', 'numeric', 'min:0'],
            'sistem_ozellikleri' => ['nullable', 'array'],
            'sistem_ozellikleri.*' => ['string', 'max:64', Rule::exists('paket_ozellikleri', 'kod')],
            'aktif_mi' => ['nullable', 'boolean'],
            'max_doktor_sayisi' => ['nullable', 'integer', 'min:1'],
            'max_ek_doktor' => ['nullable', 'integer', 'min:0'],
            'max_personel_sayisi' => ['nullable', 'integer', 'min:0'],
            'max_hasta_sayisi' => ['nullable', 'integer', 'min:0'],
            'max_randevu_sayisi' => ['nullable', 'integer', 'min:0'],
            'max_hizmet_sayisi' => ['nullable', 'integer', 'min:0'],
            'max_biyografi_karakter' => ['nullable', 'integer', 'min:0'],
            'max_profil_foto' => ['nullable', 'integer', 'min:0'],
            'sms_aylik_kontor' => ['nullable', 'integer', 'min:0'],
            'listeleme_oncelik' => ['nullable', 'integer', 'min:0', 'max:3'],
            'merkezi_finans_mi' => ['nullable', 'boolean'],
            'toplu_randevu_mi' => ['nullable', 'boolean'],
            'raporlama_mi' => ['nullable', 'boolean'],
            'hasta_havuzu_mi' => ['nullable', 'boolean'],
            'sira' => ['nullable', 'integer'],
            'one_cikan_mi' => ['nullable', 'boolean'],
            'etiket' => ['nullable', 'string', 'max:40'],
            'etiket_stil' => ['nullable', 'in:popular,web,free,trial,custom'],
            'deneme_gun' => ['nullable', 'integer', 'min:0', 'max:90'],
            'domain_dahil_mi' => ['nullable', 'boolean'],
            'domain_dahil_yil' => ['nullable', 'integer', 'min:1', 'max:5'],
            'domain_dahil_tlds' => ['nullable', 'string', 'max:120'],
            'iyzico_plan_aylik' => ['nullable', 'string', 'max:120'],
            'iyzico_plan_yillik' => ['nullable', 'string', 'max:120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ad.required' => 'Paket adı alanı zorunludur.',
            'tur.required' => 'Paket türü seçimi zorunludur.',
            'aylik_fiyat.required' => 'Aylık fiyat alanı zorunludur.',
            'yillik_fiyat.required' => 'Yıllık fiyat alanı zorunludur.',
            'sistem_ozellikleri.*.exists' => 'Geçersiz sistem özelliği seçildi.',
        ];
    }
}
