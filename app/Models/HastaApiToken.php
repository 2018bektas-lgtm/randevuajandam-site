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

    public function hasta(): BelongsTo
    {
        return $this->belongsTo(Hasta::class, 'hasta_id');
    }

    public static function hashToken(string $plain): string
    {
        return hash('sha256', $plain);
    }

    public static function findByPlainToken(string $plain): ?self
    {
        $plain = trim($plain);
        if ($plain === '') {
            return null;
        }

        return static::query()->where('token', self::hashToken($plain))->first();
    }

    /**
     * @return array{model: self, plain: string}
     */
    public static function issue(Hasta $hasta, ?string $device = null): array
    {
        $plain = Str::random(64);

        $model = self::create([
            'hasta_id'     => $hasta->id,
            'token'        => self::hashToken($plain),
            'name'         => 'mobile',
            'device'       => $device,
            'expires_at'   => now()->addDays(90),
            'last_used_at' => now(),
        ]);

        return ['model' => $model, 'plain' => $plain];
    }

    public function isValid(): bool
    {
        return ! $this->expires_at || $this->expires_at->isFuture();
    }
}
