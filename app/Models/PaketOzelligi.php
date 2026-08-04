<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaketOzelligi extends Model
{
    protected $table = 'paket_ozellikleri';

    protected $fillable = [
        'kod',
        'ad',
        'aciklama',
        'grup',
        'sira',
        'vitrin_mi',
    ];

    protected function casts(): array
    {
        return [
            'sira' => 'integer',
            'vitrin_mi' => 'boolean',
        ];
    }

    /**
     * Packages that have this feature.
     */
    public function paketler()
    {
        return $this->belongsToMany(Paket::class, 'paket_ozellik_pivot', 'ozellik_id', 'paket_id');
    }
}
