<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PersonelApiToken extends Model
{
    protected $table = 'personel_api_tokens';

    protected $fillable = [
        'personel_id',
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

    public function personel(): BelongsTo
    {
        return $this->belongsTo(KlinikPersonel::class, 'personel_id');
    }

    public function isValid(): bool
    {
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
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

        $hashed = self::hashToken($plain);
        $token = static::query()->where('token', $hashed)->first();
        if ($token) {
            return $token;
        }

        return static::query()->where('token', $plain)->first();
    }

    /**
     * @return array{model: self, plain: string}
     */
    public static function issue(KlinikPersonel $personel, ?string $name = null, ?string $ip = null, int $days = 30): array
    {
        $plain = Str::random(64);

        $model = self::create([
            'personel_id' => $personel->id,
            'token' => self::hashToken($plain),
            'name' => $name ?? 'staff-mobile',
            'ip_address' => $ip,
            'expires_at' => now()->addDays($days),
        ]);

        return ['model' => $model, 'plain' => $plain];
    }
}
