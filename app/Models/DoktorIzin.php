<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoktorIzin extends Model
{
    protected $table = 'doktor_izinleri';

    protected $fillable = [
        'doktor_id',
        'baslangic_zaman',
        'bitis_zaman',
        'aciklama',
    ];

    protected $casts = [
        'baslangic_zaman' => 'datetime',
        'bitis_zaman' => 'datetime',
    ];

    public function doktor()
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }
}
