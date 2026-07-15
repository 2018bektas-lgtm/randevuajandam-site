<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RandevuKaydetRequest extends FormRequest
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
            'doktor_id' => ['required', 'exists:doktorlar,id'],
            'hizmet_id' => [
                'required',
                Rule::exists('hizmetler', 'id')
                    ->where(fn ($query) => $query
                        ->where('doktor_id', $this->input('doktor_id'))
                        ->where('aktif_mi', true)),
            ],
            'tarih' => ['required', 'date', 'after_or_equal:today'],
            'saat' => ['required', 'date_format:H:i'],
            'not' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'doktor_id.required' => 'Doktor seçimi zorunludur.',
            'doktor_id.exists' => 'Seçilen doktor geçersizdir.',
            'hizmet_id.required' => 'Hizmet seçimi zorunludur.',
            'hizmet_id.exists' => 'Seçilen hizmet bu hekime ait değil veya geçersizdir.',
            'tarih.required' => 'Tarih seçimi zorunludur.',
            'tarih.after_or_equal' => 'Geçmiş bir tarihe randevu alamazsınız.',
            'saat.required' => 'Saat seçimi zorunludur.',
            'saat.date_format' => 'Lütfen geçerli bir saat dilimi seçin.',
        ];
    }
}
