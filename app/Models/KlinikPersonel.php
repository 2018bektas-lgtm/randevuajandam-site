<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class KlinikPersonel extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'klinik_personelleri';

    protected $fillable = [
        'klinik_id',
        'ad_soyad',
        'e_posta',
        'sifre',
        'telefon',
        'rol',
        'yetkiler',
        'sifre_degistirildi_mi',
        'aktif_mi',
    ];

    protected $hidden = [
        'sifre',
        'remember_token',
    ];

    /**
     * Get the password for the staff member.
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
            'yetkiler' => 'array',
            'aktif_mi' => 'boolean',
            'sifre_degistirildi_mi' => 'boolean',
        ];
    }

    /**
     * Get the clinic.
     */
    public function klinik(): BelongsTo
    {
        return $this->belongsTo(Klinik::class, 'klinik_id');
    }

    /**
     * Check if the staff member has permission for a module.
     */
    public function yetkisiVarMi(string $modul): bool
    {
        return $this->yetkiler[$modul] ?? false;
    }
}
