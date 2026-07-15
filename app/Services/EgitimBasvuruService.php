<?php

namespace App\Services;

use App\Models\Egitim;
use App\Models\EgitimBasvuru;
use App\Models\FinansKategori;
use App\Models\Hasta;
use App\Models\Odeme;
use App\Notifications\EgitimBasvuruAlindi;
use App\Notifications\EgitimYeniBasvuru;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EgitimBasvuruService
{
    /**
     * @param  array{
     *   ad: string,
     *   soyad: string,
     *   telefon: string,
     *   e_posta?: string|null,
     *   cevaplar?: array,
     *   kvkk_onay: bool,
     *   ip?: string|null,
     *   user_agent?: string|null,
     * }  $data
     */
    public function basvur(Egitim $egitim, array $data): EgitimBasvuru
    {
        if (! $egitim->basvuruAlinabilirMi()) {
            throw new InvalidArgumentException('Bu eğitime şu an başvuru alınmıyor.');
        }

        $ucret = $egitim->fiyat;
        $ucretDurumu = ($ucret === null || (float) $ucret <= 0) ? 'yok' : 'beklemede';

        $hasta = Auth::guard('hasta')->user();

        $basvuru = EgitimBasvuru::create([
            'egitim_id' => $egitim->id,
            'doktor_id' => $egitim->doktor_id,
            'hasta_id' => $hasta?->id,
            'ad' => $data['ad'],
            'soyad' => $data['soyad'],
            'telefon' => $data['telefon'],
            'e_posta' => $data['e_posta'] ?? null,
            'cevaplar' => $data['cevaplar'] ?? [],
            'durum' => 'beklemede',
            'ucret_durumu' => $ucretDurumu,
            'ucret_tutari' => $ucretDurumu === 'yok' ? null : $ucret,
            'odenen_tutar' => 0,
            'kvkk_onay' => (bool) ($data['kvkk_onay'] ?? false),
            'ip' => $data['ip'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
        ]);

        $doktor = $egitim->doktor;
        if ($doktor) {
            try {
                $doktor->notify(new EgitimYeniBasvuru($basvuru));
            } catch (\Throwable) {
                // mail config may be missing in local
            }
        }

        if (! empty($basvuru->e_posta) && ! str_contains((string) $basvuru->e_posta, '@randevu.local')) {
            try {
                \Illuminate\Support\Facades\Notification::route('mail', $basvuru->e_posta)
                    ->notify(new EgitimBasvuruAlindi($basvuru));
            } catch (\Throwable) {
            }
        }

        return $basvuru;
    }

    /**
     * Hekim “ödeme alındı” → finans geliri + kategori.
     */
    public function odemeAlindi(EgitimBasvuru $basvuru, float $tutar, ?string $yontem = null): Odeme
    {
        if ($basvuru->ucret_durumu === 'yok') {
            throw new InvalidArgumentException('Bu başvuru ücretsiz; finans kaydı oluşturulmaz.');
        }

        return DB::transaction(function () use ($basvuru, $tutar, $yontem) {
            $basvuru = EgitimBasvuru::query()->lockForUpdate()->findOrFail($basvuru->id);
            $egitim = $basvuru->egitim;
            $hedef = (float) ($basvuru->ucret_tutari ?? $egitim?->fiyat ?? $tutar);
            if ($hedef <= 0) {
                $hedef = $tutar;
            }

            $kategori = FinansKategori::firstOrCreate(
                [
                    'doktor_id' => $basvuru->doktor_id,
                    'ad' => 'Eğitim',
                    'tur' => 'gelir',
                ],
                [
                    'renk' => '#C96A2B',
                    'aktif' => true,
                ]
            );

            $aciklama = 'Eğitim: '.($egitim?->baslik ?? '#').' — başvuru #'.$basvuru->id.' ('.$basvuru->ad_soyad.')';

            $odeme = $basvuru->odeme_id ? Odeme::find($basvuru->odeme_id) : null;

            if ($odeme) {
                $odeme->update([
                    'tutar' => $hedef,
                    'odenen_tutar' => $tutar,
                    'odeme_yontemi' => $yontem ?? $odeme->odeme_yontemi,
                    'durum' => $tutar + 0.001 >= $hedef ? 'odendi' : 'kismi_odeme',
                    'aciklama' => $aciklama,
                    'finans_kategori_id' => $kategori->id,
                    'egitim_basvuru_id' => $basvuru->id,
                    'odeme_tarihi' => now()->toDateString(),
                ]);
            } else {
                $odeme = Odeme::create([
                    'doktor_id' => $basvuru->doktor_id,
                    'hasta_id' => $basvuru->hasta_id,
                    'egitim_basvuru_id' => $basvuru->id,
                    'finans_kategori_id' => $kategori->id,
                    'tutar' => $hedef,
                    'odenen_tutar' => $tutar,
                    'odeme_yontemi' => $yontem ?? 'manuel',
                    'durum' => $tutar + 0.001 >= $hedef ? 'odendi' : 'kismi_odeme',
                    'aciklama' => $aciklama,
                    'odeme_tarihi' => now()->toDateString(),
                ]);
            }

            $basvuru->update([
                'odeme_id' => $odeme->id,
                'odenen_tutar' => $tutar,
                'odeme_yontemi' => $yontem ?? $basvuru->odeme_yontemi,
                'ucret_durumu' => $tutar + 0.001 >= $hedef ? 'odendi' : 'kismi',
                'durum' => $basvuru->durum === 'beklemede' ? 'onaylandi' : $basvuru->durum,
            ]);

            return $odeme->fresh();
        });
    }
}
