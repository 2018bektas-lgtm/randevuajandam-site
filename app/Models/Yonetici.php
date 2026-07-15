<?php

namespace App\Models;

use App\Support\HasTwoFactorAuth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Yonetici extends Authenticatable
{
    use HasFactory, HasTwoFactorAuth, Notifiable;

    protected $table = 'yoneticiler';

    protected $fillable = [
        'ad_soyad',
        'e_posta',
        'sifre',
        'telefon',
        'aktif_mi',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'sifre',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    public function routeNotificationForMail(): string
    {
        return $this->e_posta;
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword(): string
    {
        return $this->sifre;
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'sifre' => 'hashed',
            'aktif_mi' => 'boolean',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}
