<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class HastaApiToken extends Model
{
    protected $table = 'hasta_api_tokens';

    protected $fillable = [
        'hasta_id',
        'token',
        'name',
        'device',
        'last_used_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function hasta(): BelongsTo
    {
        return $this->belongsTo(Hasta::class, 'hasta_id');
    }

    public static function issue(Hasta $hasta, ?string $device = null): self
    {
        return self::create([
            'hasta_id' => $hasta->id,
            'token' => Str::random(64),
            'name' => 'mobile',
            'device' => $device,
            'expires_at' => now()->addDays(90),
            'last_used_at' => now(),
        ]);
    }

    public function isValid(): bool
    {
        return ! $this->expires_at || $this->expires_at->isFuture();
    }
}
