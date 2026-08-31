<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Girişsiz yorum daveti (tek kullanımlık kısa bağlantı).
 *
 * Ham token yalnızca e-postadaki bağlantıda bulunur; burada sha256 özeti
 * saklanır. Doğrulama {@see bul()} ile yapılır.
 */
class YorumDaveti extends Model
{
    use HasFactory;

    protected $table = 'yorum_davetleri';

    protected $fillable = [
        'randevu_id',
        'hasta_id',
        'doktor_id',
        'token_hash',
        'gonderildi_at',
        'kullanildi_at',
        'gecerlilik_bitis',
    ];

    protected function casts(): array
    {
        return [
            'gonderildi_at' => 'datetime',
            'kullanildi_at' => 'datetime',
            'gecerlilik_bitis' => 'datetime',
        ];
    }

    /** Bağlantı ömrü (gün). */
    public const GECERLILIK_GUN = 30;

    /**
     * Ham token üretir. Kısa tutulur (e-postadaki bağlantı okunabilir olsun)
     * ama tahmin edilemeyecek kadar geniş: 16 karakter base62 ≈ 95 bit.
     */
    public static function tokenUret(): string
    {
        return Str::random(16);
    }

    public static function tokenOzeti(string $token): string
    {
        return hash('sha256', $token);
    }

    /** Ham token ile daveti bulur (yoksa null). */
    public static function bul(string $token): ?self
    {
        if ($token === '' || ! preg_match('/^[A-Za-z0-9]{8,64}$/', $token)) {
            return null;
        }

        return static::query()->where('token_hash', static::tokenOzeti($token))->first();
    }

    /** Kullanılmamış ve süresi dolmamış mı? */
    public function kullanilabilir(): bool
    {
        if ($this->kullanildi_at !== null) {
            return false;
        }

        return $this->gecerlilik_bitis === null || $this->gecerlilik_bitis->isFuture();
    }

    public function randevu(): BelongsTo
    {
        return $this->belongsTo(Randevu::class, 'randevu_id');
    }

    public function hasta(): BelongsTo
    {
        return $this->belongsTo(Hasta::class, 'hasta_id');
    }

    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }
}
