<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoktorCalismaSaati extends Model
{
    protected $table = 'doktor_calisma_saatleri';

    protected $fillable = [
        'doktor_id',
        'gun',
        'aktif_mi',
        'mesai_baslangic',
        'mesai_bitis',
        'ogle_arasi_aktif_mi',
        'ogle_baslangic',
        'ogle_bitis',
    ];

    protected $casts = [
        'aktif_mi' => 'boolean',
        'ogle_arasi_aktif_mi' => 'boolean',
        'gun' => 'integer',
    ];

    protected static function booted(): void
    {
        static::updated(function (DoktorCalismaSaati $cs) {
            $doktor = $cs->doktor;
            \App\Jobs\SendWebhookJob::dispatch(
                'working_hours.updated',
                $cs->toArray(),
                $cs->doktor_id,
                $doktor ? $doktor->klinik_id : null
            );
        });
    }

    public function doktor()
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }

    public function getGunAdiAttribute(): string
    {
        $gunler = [
            1 => 'Pazartesi',
            2 => 'Salı',
            3 => 'Çarşamba',
            4 => 'Perşembe',
            5 => 'Cuma',
            6 => 'Cumartesi',
            7 => 'Pazar',
        ];

        return $gunler[$this->gun] ?? '';
    }
}
