<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Odeme extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'odemeler';

    protected $fillable = [
        'doktor_id',
        'randevu_id',
        'egitim_basvuru_id',
        'hasta_id',
        'hizmet_id',
        'finans_kategori_id',
        'tutar',
        'odenen_tutar',
        'odeme_yontemi',
        'durum',
        'aciklama',
        'odeme_tarihi',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'tutar' => 'decimal:2',
            'odenen_tutar' => 'decimal:2',
            'odeme_tarihi' => 'date',
        ];
    }

    /**
     * Get the doctor.
     */
    public function doktor(): BelongsTo
    {
        return $this->belongsTo(Doktor::class, 'doktor_id');
    }

    /**
     * Get the appointment.
     */
    public function randevu(): BelongsTo
    {
        return $this->belongsTo(Randevu::class, 'randevu_id');
    }

    /**
     * Get the patient.
     */
    public function hasta(): BelongsTo
    {
        return $this->belongsTo(Hasta::class, 'hasta_id');
    }

    /**
     * Get the service.
     */
    public function hizmet(): BelongsTo
    {
        return $this->belongsTo(Hizmet::class, 'hizmet_id');
    }

    /**
     * Get the income category.
     */
    public function finansKategori(): BelongsTo
    {
        return $this->belongsTo(FinansKategori::class, 'finans_kategori_id');
    }

    /**
     * Get the payment installments.
     */
    public function kalemler(): HasMany
    {
        return $this->hasMany(OdemeKalemi::class, 'odeme_id')->orderBy('tarih');
    }

    /**
     * Recalculate odenen_tutar from kalemler and update durum.
     */
    public function odenenTutariGuncelle(): void
    {
        $toplam = $this->kalemler()->sum('tutar');
        $durum = 'beklemede';

        if ($toplam >= $this->tutar) {
            $durum = 'odendi';
        } elseif ($toplam > 0) {
            $durum = 'kismi_odeme';
        }

        $this->update(['odenen_tutar' => $toplam, 'durum' => $durum]);
    }

    /**
     * Scope: Pending payments.
     */
    public function scopeBeklemede($query)
    {
        return $query->where('durum', 'beklemede');
    }

    /**
     * Scope: Completed payments.
     */
    public function scopeOdendi($query)
    {
        return $query->where('durum', 'odendi');
    }

    /**
     * Scope: Partially paid payments.
     */
    public function scopeKismiOdeme($query)
    {
        return $query->where('durum', 'kismi_odeme');
    }

    /**
     * Scope: Cancelled payments.
     */
    public function scopeIptal($query)
    {
        return $query->where('durum', 'iptal');
    }
}
