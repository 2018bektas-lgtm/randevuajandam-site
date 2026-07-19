<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DomainOrder extends Model
{
    protected $table = 'domain_orders';

    public const KAYNAK_INCLUDED = 'included';

    public const KAYNAK_BYOD = 'byod';

    public const DURUM_DRAFT = 'draft';

    public const DURUM_PURCHASING = 'purchasing';

    public const DURUM_ACTIVE = 'active';

    public const DURUM_FAILED = 'failed';

    public const DURUM_DNS_PENDING = 'dns_pending';

    protected $fillable = [
        'owner_type',
        'owner_id',
        'paket_id',
        'domain',
        'tld',
        'kaynak',
        'durum',
        'hostinger_item_id',
        'hostinger_order_id',
        'hostinger_cost_cents',
        'currency',
        'error_message',
        'registered_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
            'expires_at' => 'datetime',
            'hostinger_cost_cents' => 'integer',
        ];
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class, 'paket_id');
    }

    public function isActive(): bool
    {
        return $this->durum === self::DURUM_ACTIVE;
    }
}
