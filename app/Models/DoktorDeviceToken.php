<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoktorDeviceToken extends Model
{
    protected $table = 'doktor_device_tokens';

    protected $fillable = [
        'doktor_id',
        'token',
        'platform',
        'provider',
        'device_name',
        'app_version',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
        ];
    }

    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }

    public static function upsertToken(
        int $doktorId,
        string $token,
        ?string $platform = null,
        ?string $deviceName = null,
        ?string $appVersion = null,
        string $provider = 'expo',
    ): self {
        $row = static::query()->where('token', $token)->first();
        if ($row) {
            $row->update([
                'doktor_id' => $doktorId,
                'platform' => $platform ?? $row->platform,
                'provider' => $provider,
                'device_name' => $deviceName ?? $row->device_name,
                'app_version' => $appVersion ?? $row->app_version,
                'last_used_at' => now(),
            ]);

            return $row->fresh();
        }

        return static::create([
            'doktor_id' => $doktorId,
            'token' => $token,
            'platform' => $platform,
            'provider' => $provider,
            'device_name' => $deviceName,
            'app_version' => $appVersion,
            'last_used_at' => now(),
        ]);
    }
}
