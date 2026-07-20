<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaytrCallbackLog extends Model
{
    protected $table = 'paytr_callback_logs';

    protected $fillable = [
        'merchant_oid',
        'uyelik_odeme_id',
        'status',
        'total_amount',
        'hash_ok',
        'processed',
        'error_message',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'hash_ok' => 'boolean',
            'processed' => 'boolean',
            'raw_payload' => 'array',
        ];
    }

    public function odeme(): BelongsTo
    {
        return $this->belongsTo(UyelikOdeme::class, 'uyelik_odeme_id');
    }
}
