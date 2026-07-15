<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DoktorApiToken extends Model
{
    protected $table = 'doktor_api_tokens';

    protected $fillable = [
        'doktor_id',
        'token',
        'name',
        'ip_address',
        'last_used_at',
        'expires_at',
    ];

    protected $hidden = [
        'token',
    ];

    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }

    public function isValid(): bool
    {
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Hash plain bearer for storage / lookup (SHA-256 hex).
     */
    public static function hashToken(string $plain): string
    {
        return hash('sha256', $plain);
    }

    /**
     * Resolve token by plain bearer (hashed lookup + legacy plain fallback).
     */
    public static function findByPlainToken(string $plain): ?self
    {
        $plain = trim($plain);
        if ($plain === '') {
            return null;
        }

        $hashed = self::hashToken($plain);

        $token = static::query()->where('token', $hashed)->first();
        if ($token) {
            return $token;
        }

        // Legacy: older rows stored the usable token value directly
        return static::query()->where('token', $plain)->first();
    }

    /**
     * Issue a new token. Returns model + plain token (plain shown once to client).
     *
     * @return array{model: self, plain: string}
     */
    public static function issue(Doktor $doktor, ?string $name = null, ?string $ip = null, int $days = 30): array
    {
        $plain = Str::random(64);

        $model = self::create([
            'doktor_id' => $doktor->id,
            'token' => self::hashToken($plain),
            'name' => $name ?? 'doctor-site-panel',
            'ip_address' => $ip,
            'expires_at' => now()->addDays($days),
        ]);

        return ['model' => $model, 'plain' => $plain];
    }
}
