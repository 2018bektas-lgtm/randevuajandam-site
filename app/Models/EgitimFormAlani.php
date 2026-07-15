<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EgitimFormAlani extends Model
{
    protected $table = 'egitim_form_alanlari';

    protected $fillable = [
        'egitim_id',
        'etiket',
        'anahtar',
        'tip',
        'zorunlu_mu',
        'secenekler',
        'placeholder',
        'sira',
        'aktif_mi',
    ];

    protected function casts(): array
    {
        return [
            'zorunlu_mu' => 'boolean',
            'aktif_mi' => 'boolean',
            'secenekler' => 'array',
            'sira' => 'integer',
        ];
    }

    public function egitim(): BelongsTo
    {
        return $this->belongsTo(Egitim::class, 'egitim_id');
    }
}
