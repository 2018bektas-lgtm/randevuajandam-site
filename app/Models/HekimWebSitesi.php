<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HekimWebSitesi extends Model
{
    use HasFactory;

    protected $table = 'hekim_web_siteleri';

    protected $fillable = [
        'doktor_id',
        'domain',
        'tema',
        'durum',
        'hostinger_domain_id',
        'hata_mesaji',
    ];

    /**
     * Get the doctor that owns this website.
     */
    public function doktor()
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }
}
