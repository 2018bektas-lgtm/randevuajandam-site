<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookEndpoint extends Model
{
    protected $table = 'webhook_endpoints';

    protected $fillable = [
        'doktor_id',
        'klinik_id',
        'url',
        'secret_key',
        'events',
        'aktif',
    ];

    protected $hidden = [
        'secret_key',
    ];

    protected $casts = [
        'events' => 'array',
        'aktif' => 'boolean',
    ];

    /**
     * Webhook'un ait olduğu doktor.
     */
    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }

    /**
     * Webhook'un ait olduğu klinik.
     */
    public function klinik(): BelongsTo
    {
        return $this->belongsTo(Klinik::class, 'klinik_id');
    }
}
