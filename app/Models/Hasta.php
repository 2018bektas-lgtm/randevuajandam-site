<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Hasta extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'hastalar';

    protected $fillable = [
        'ad',
        'soyad',
        'e_posta',
        'sifre',
        'telefon',
        'aktif_mi',
    ];

    protected $hidden = [
        'sifre',
        'remember_token',
    ];

    public function getAuthPassword(): string
    {
        return $this->sifre;
    }

    protected function casts(): array
    {
        return [
            'sifre' => 'hashed',
            'aktif_mi' => 'boolean',
        ];
    }

    public function getAdSoyadAttribute(): string
    {
        return $this->ad.' '.$this->soyad;
    }

    public function randevular(): HasMany
    {
        return $this->hasMany(Randevu::class, 'hasta_id');
    }

    /**
     * Get the reviews written by the patient.
     */
    public function yorumlar(): HasMany
    {
        return $this->hasMany(Yorum::class, 'hasta_id');
    }

    /**
     * Get the payments of the patient.
     */
    public function odemeler(): HasMany
    {
        return $this->hasMany(Odeme::class, 'hasta_id');
    }

    /**
     * Get the clinics the patient is registered with.
     */
    public function klinikler(): BelongsToMany
    {
        return $this->belongsToMany(Klinik::class, 'klinik_hastalari', 'hasta_id', 'klinik_id')
            ->withPivot('kayit_tarihi', 'notlar')
            ->withTimestamps();
    }
}
