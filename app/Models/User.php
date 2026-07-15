<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'telefon',
        'tur',
        'paket_id',
        'odeme_periyodu',
        'uyelik_baslangic',
        'uyelik_bitis',
        'aktif_mi',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'uyelik_baslangic' => 'datetime',
            'uyelik_bitis' => 'datetime',
            'aktif_mi' => 'boolean',
        ];
    }

    /**
     * Get the subscription package of the user.
     */
    public function paket()
    {
        return $this->belongsTo(Paket::class, 'paket_id');
    }
}
