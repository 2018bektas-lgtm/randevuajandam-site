<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoktorMezuniyetBelgesi extends Model
{
    protected $table = 'doktor_mezuniyet_belgeleri';

    protected $fillable = [
        'doktor_id',
        'barkod',
        'tc_kimlik_no',
        'ad_soyad_belge',
        'program',
        'universite',
        'fakulte',
        'bolum',
        'diploma_no',
        'diploma_notu',
        'mezuniyet_tarihi',
        'dogrulama_durumu',
        'eslesme_skoru',
        'eslesme_detay',
        'dosya_yolu',
        'ham_parse',
        'edevlet_log_id',
        'auto_onay_uygun',
        'onerilen_unvan',
        'onerilen_brans',
    ];

    protected function casts(): array
    {
        return [
            'mezuniyet_tarihi' => 'date',
            'eslesme_skoru' => 'decimal:4',
            'eslesme_detay' => 'array',
            'ham_parse' => 'array',
            'auto_onay_uygun' => 'boolean',
        ];
    }

    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class);
    }

    public function isBasarili(): bool
    {
        return ($this->dogrulama_durumu ?? '') === 'basarili';
    }

    /** Kayıt formunda gösterilecek özet satır. */
    public function ozetSatir(): string
    {
        $parts = array_filter([
            $this->universite,
            $this->fakulte,
            $this->bolum ?: $this->program,
            $this->mezuniyet_tarihi?->format('d.m.Y'),
            $this->diploma_no ? 'Dip: '.$this->diploma_no : null,
        ]);

        return implode(' · ', $parts);
    }
}
