<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ApiKey extends Model
{
    protected $table = 'api_keys';

    protected $fillable = [
        'doktor_id',
        'klinik_id',
        'api_key',
        'secret_key',
        'durum',
        'yetkiler',
        'son_kullanim',
    ];

    protected $hidden = [
        'secret_key',
    ];

    protected function casts(): array
    {
        return [
            'durum' => 'boolean',
            'yetkiler' => 'array',
            'son_kullanim' => 'datetime',
        ];
    }

    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }

    public function klinik(): BelongsTo
    {
        return $this->belongsTo(Klinik::class, 'klinik_id');
    }

    public function isActive(): bool
    {
        return (bool) $this->durum;
    }

    public function touchUsage(): void
    {
        $this->forceFill(['son_kullanim' => now()])->saveQuietly();
    }

    /**
     * Store bcrypt hash of plain secret (never store plain in api_keys).
     */
    public static function hashSecret(string $plain): string
    {
        return Hash::make($plain);
    }

    /**
     * Verify provided secret against stored value.
     * Supports legacy plain secrets (hash_equals) and bcrypt hashes.
     */
    public function verifySecret(?string $provided): bool
    {
        if (! is_string($provided) || $provided === '') {
            return false;
        }

        $stored = (string) ($this->secret_key ?? '');
        // Empty secret is never accepted (middleware also enforces non-empty secret)
        if ($stored === '') {
            return false;
        }

        // bcrypt / argon hashes
        if (str_starts_with($stored, '$2y$')
            || str_starts_with($stored, '$2a$')
            || str_starts_with($stored, '$2b$')
            || str_starts_with($stored, '$argon')) {
            return Hash::check($provided, $stored);
        }

        // Eski (hash'siz) secret: dogruysa ANINDA hash'e tasi.
        //
        // Veritabani sizarsa duz metin secret dogrudan kullanilabilir oldugu
        // icin bu kayitlarin kalmasi risk. Dogrulama basarili oldugunda ayni
        // degeri hash'leyip yaziyoruz: hekimin sitesi ayni secret'i gondermeye
        // devam ettigi icin kesinti olmaz, kayit ise artik hash'li olur.
        if (! hash_equals($stored, $provided)) {
            return false;
        }

        try {
            $this->forceFill(['secret_key' => self::hashSecret($provided)])->saveQuietly();
            Log::info('ApiKey: eski duz metin secret hash e tasindi', ['api_key_id' => $this->id]);
        } catch (\Throwable $e) {
            // Tasima basarisiz olsa bile dogrulama gecerli — erisimi kesme
            Log::warning('ApiKey: secret hash e tasinamadi', [
                'api_key_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
        }

        return true;
    }

    /**
     * Hala hash'lenmemis (duz metin) secret tasiyan kayit sayisi.
     */
    public static function legacyPlainSecretCount(): int
    {
        return static::query()
            ->whereNotNull('secret_key')
            ->where('secret_key', '!=', '')
            ->where('secret_key', 'not like', '$2y$%')
            ->where('secret_key', 'not like', '$2a$%')
            ->where('secret_key', 'not like', '$2b$%')
            ->where('secret_key', 'not like', '$argon%')
            ->count();
    }

    /**
     * Whether stored secret is already hashed (cannot be shown again).
     */
    public function secretIsHashed(): bool
    {
        $stored = (string) ($this->secret_key ?? '');

        return str_starts_with($stored, '$2y$')
            || str_starts_with($stored, '$2a$')
            || str_starts_with($stored, '$2b$')
            || str_starts_with($stored, '$argon');
    }

    /**
     * Create or update key with hashed secret. Returns plain secret once.
     *
     * @param  array{doktor_id?: int|null, klinik_id?: int|null, api_key: string, durum?: bool, yetkiler?: mixed}  $attrs
     * @return array{model: self, plain_secret: string}
     */
    public static function issue(array $attrs, ?string $plainSecret = null): array
    {
        $plain = $plainSecret ?: strtolower(\Illuminate\Support\Str::random(60));
        $match = [];
        if (! empty($attrs['doktor_id'])) {
            $match = ['doktor_id' => $attrs['doktor_id']];
        } elseif (! empty($attrs['klinik_id'])) {
            $match = ['klinik_id' => $attrs['klinik_id']];
        }

        $payload = array_merge($attrs, [
            'secret_key' => self::hashSecret($plain),
            'durum' => $attrs['durum'] ?? true,
        ]);

        $model = static::query()->updateOrCreate($match, $payload);

        return ['model' => $model, 'plain_secret' => $plain];
    }
}
