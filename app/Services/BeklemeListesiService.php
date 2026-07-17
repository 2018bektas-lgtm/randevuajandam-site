<?php

namespace App\Services;

use App\Models\BeklemeListesi;
use App\Models\Doktor;
use App\Models\Hasta;
use App\Models\Randevu;
use App\Notifications\BeklemeListesiKayitBildirimi;
use App\Notifications\BeklemeListesiSlotAcildi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;

class BeklemeListesiService
{
    /**
     * @param  array{
     *     ad: string,
     *     soyad: string,
     *     telefon: string,
     *     e_posta?: string|null,
     *     hizmet_id?: int|null,
     *     tercih_tarih?: string|null,
     *     tercih_saat?: string|null,
     *     not?: string|null,
     *     hasta?: Hasta|null,
     * }  $data
     */
    public function join(Doktor $doktor, array $data): BeklemeListesi
    {
        $telefon = app(AppointmentBookingService::class)->normalizePhone((string) $data['telefon']);
        $tarih = ! empty($data['tercih_tarih']) ? $data['tercih_tarih'] : null;

        // Aynı hekim + telefon + aynı tercih günü için mükerrer beklemede kayıt engelle
        $exists = BeklemeListesi::query()
            ->where('doktor_id', $doktor->id)
            ->where('telefon', $telefon)
            ->whereIn('durum', ['beklemede', 'bildirildi'])
            ->when($tarih, fn ($q) => $q->whereDate('tercih_tarih', $tarih), fn ($q) => $q->whereNull('tercih_tarih'))
            ->exists();

        if ($exists) {
            throw new InvalidArgumentException('Bu bilgilerle zaten aktif bir bekleme listesi kaydınız var.');
        }

        $hizmetId = isset($data['hizmet_id']) ? (int) $data['hizmet_id'] : null;
        if ($hizmetId) {
            $ok = $doktor->hizmetler()->where('id', $hizmetId)->where('aktif_mi', true)->exists();
            if (! $ok) {
                $hizmetId = null;
            }
        }

        /** @var Hasta|null $hasta */
        $hasta = $data['hasta'] ?? null;

        $kayit = BeklemeListesi::create([
            'doktor_id' => $doktor->id,
            'hasta_id' => $hasta?->id,
            'hizmet_id' => $hizmetId,
            'ad' => $data['ad'],
            'soyad' => $data['soyad'],
            'telefon' => $telefon,
            'e_posta' => $data['e_posta'] ?? $hasta?->e_posta,
            'tercih_tarih' => $tarih,
            'tercih_saat' => ! empty($data['tercih_saat']) ? substr((string) $data['tercih_saat'], 0, 5) : null,
            'not' => $data['not'] ?? null,
            'durum' => 'beklemede',
        ]);

        try {
            $doktor->notify(new BeklemeListesiKayitBildirimi($kayit));
        } catch (\Throwable $e) {
            Log::warning('Bekleme listesi doktor bildirimi hatası: '.$e->getMessage(), [
                'kayit_id' => $kayit->id,
            ]);
        }

        return $kayit;
    }

    /**
     * Randevu iptalinde aynı güne (veya esnek tercihe) bakan ilk adaylara haber ver.
     */
    public function notifyOnSlotOpened(Randevu $randevu, int $limit = 5): int
    {
        $doktor = $randevu->doktor;
        if (! $doktor) {
            return 0;
        }

        $tarih = $randevu->tarih instanceof \DateTimeInterface
            ? $randevu->tarih->format('Y-m-d')
            : substr((string) $randevu->tarih, 0, 10);

        $adaylar = BeklemeListesi::query()
            ->where('doktor_id', $doktor->id)
            ->where('durum', 'beklemede')
            ->where(function ($q) use ($tarih) {
                $q->whereNull('tercih_tarih')
                    ->orWhereDate('tercih_tarih', $tarih);
            })
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        $count = 0;
        foreach ($adaylar as $kayit) {
            $this->notifyKayit($kayit, $randevu);
            $kayit->update([
                'durum' => 'bildirildi',
                'bildirildi_at' => now(),
            ]);
            $count++;
        }

        return $count;
    }

    public function notifyKayit(BeklemeListesi $kayit, ?Randevu $slotRandevu = null): void
    {
        $doktor = $kayit->doktor;
        if (! $doktor) {
            return;
        }

        $notification = new BeklemeListesiSlotAcildi($kayit, $slotRandevu);

        if ($kayit->hasta) {
            $kayit->hasta->notify($notification);

            return;
        }

        $routes = [];
        if (! empty($kayit->e_posta) && ! str_contains((string) $kayit->e_posta, '@randevu.local')) {
            $routes['mail'] = $kayit->e_posta;
        }

        if ($routes !== []) {
            Notification::route('mail', $routes['mail'])->notify($notification);
        }
    }
}
