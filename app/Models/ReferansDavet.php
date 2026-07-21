<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferansDavet extends Model
{
    protected $table = 'referans_davetler';

    protected $fillable = [
        'davet_eden_id',
        'davet_edilen_id',
        'kod',
        'durum',
        'uyelik_odeme_id',
        'indirim_yuzde_davet_edilen',
        'komisyon_yuzde_davet_eden',
        'odul_gun_davet_eden',
        'odeme_tutari_brut',
        'odeme_tutari_net',
        'odullendirildi_at',
        'red_nedeni',
    ];

    protected function casts(): array
    {
        return [
            'odeme_tutari_brut' => 'decimal:2',
            'odeme_tutari_net' => 'decimal:2',
            'odullendirildi_at' => 'datetime',
            'indirim_yuzde_davet_edilen' => 'integer',
            'komisyon_yuzde_davet_eden' => 'integer',
            'odul_gun_davet_eden' => 'integer',
        ];
    }

    public function davetEden(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'davet_eden_id');
    }

    public function davetEdilen(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'davet_edilen_id');
    }

    public function uyelikOdeme(): BelongsTo
    {
        return $this->belongsTo(UyelikOdeme::class, 'uyelik_odeme_id');
    }
}
