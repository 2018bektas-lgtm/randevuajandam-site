<?php

namespace App\Services;

use App\Events\RandevuOlusturuldu;
use App\Models\Doktor;
use App\Models\Hasta;
use App\Models\Hizmet;
use App\Models\Randevu;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Central appointment booking with validation, hizmet ownership check,
 * and slot-level locking to reduce double-booking races.
 */
class AppointmentBookingService
{
    public function __construct(
        protected RandevuDogrulamaService $dogrulamaService,
    ) {}

    /**
     * Guest / soft-account booking (no prior login required).
     *
     * @param  array{
     *     ad: string,
     *     soyad: string,
     *     telefon: string,
     *     e_posta?: string|null,
     *     hizmet_id: int,
     *     tarih: string,
     *     saat: string,
     *     not?: string|null,
     *     durum?: string,
     *     gorusme_tipi?: string,
     *     skip_schedule_validation?: bool,
     * }  $payload
     */
    public function createFromGuest(Doktor $doktor, array $payload): Randevu
    {
        $this->assertPackageAppointmentLimit($doktor);

        $gorusmeTipi = ($payload['gorusme_tipi'] ?? 'yuz_yuze') === 'online' ? 'online' : 'yuz_yuze';
        if ($gorusmeTipi === 'online') {
            $paket = $doktor->aktifPaket();
            if (! $paket || ! $paket->hasFeature('online_gorusme')) {
                throw new InvalidArgumentException('Online görüşme bu hekimin paketinde yer almıyor.');
            }
        }

        $hasta = $this->findOrCreateGuestPatient([
            'ad' => $payload['ad'],
            'soyad' => $payload['soyad'],
            'telefon' => $payload['telefon'],
            'e_posta' => $payload['e_posta'] ?? null,
        ]);

        $durum = $payload['durum'] ?? $this->resolveDefaultStatus($doktor);

        return $this->create([
            'doktor' => $doktor,
            'hasta' => $hasta,
            'hizmet_id' => (int) $payload['hizmet_id'],
            'tarih' => $payload['tarih'],
            'saat' => $payload['saat'],
            'not' => $payload['not'] ?? null,
            'ad' => $payload['ad'],
            'soyad' => $payload['soyad'],
            'telefon' => $this->normalizePhone($payload['telefon']),
            'e_posta' => $payload['e_posta'] ?? $hasta->e_posta,
            'durum' => $durum,
            'gorusme_tipi' => $gorusmeTipi,
            'skip_schedule_validation' => (bool) ($payload['skip_schedule_validation'] ?? false),
        ]);
    }

    /**
     * Find patient by phone/email or create a soft account (random password).
     *
     * @param  array{ad: string, soyad: string, telefon: string, e_posta?: string|null}  $data
     */
    public function findOrCreateGuestPatient(array $data): Hasta
    {
        $phone = $this->normalizePhone($data['telefon']);
        $digits = preg_replace('/\D+/', '', $phone) ?: '';

        $hasta = Hasta::query()
            ->where(function ($q) use ($phone, $digits, $data) {
                $q->where('telefon', $phone)
                    ->orWhere('telefon', $data['telefon']);
                if ($digits !== '') {
                    $q->orWhere('telefon', 'like', '%'.substr($digits, -10));
                }
            })
            ->first();

        if ($hasta) {
            $updates = [];
            if (empty($hasta->ad) && ! empty($data['ad'])) {
                $updates['ad'] = $data['ad'];
            }
            if (empty($hasta->soyad) && ! empty($data['soyad'])) {
                $updates['soyad'] = $data['soyad'];
            }
            if (! empty($data['e_posta']) && empty($hasta->e_posta)) {
                $updates['e_posta'] = $data['e_posta'];
            }
            if ($updates !== []) {
                $hasta->update($updates);
            }

            return $hasta->fresh();
        }

        $email = trim((string) ($data['e_posta'] ?? ''));
        if ($email !== '') {
            $byEmail = Hasta::where('e_posta', $email)->first();
            if ($byEmail) {
                if (empty($byEmail->telefon)) {
                    $byEmail->update(['telefon' => $phone]);
                }

                return $byEmail;
            }
        } else {
            $suffix = $digits !== '' ? $digits : Str::lower(Str::random(8));
            $email = 'misafir+'.$suffix.'@randevu.local';
            if (Hasta::where('e_posta', $email)->exists()) {
                $email = 'misafir+'.$suffix.'.'.Str::lower(Str::random(4)).'@randevu.local';
            }
        }

        return Hasta::create([
            'ad' => $data['ad'],
            'soyad' => $data['soyad'],
            'e_posta' => $email,
            'telefon' => $phone,
            'sifre' => Str::password(16),
            'aktif_mi' => true,
        ]);
    }

    public function normalizePhone(string $telefon): string
    {
        $digits = preg_replace('/\D+/', '', $telefon) ?? '';

        if (str_starts_with($digits, '90') && strlen($digits) >= 12) {
            $digits = substr($digits, 2);
        }
        if (strlen($digits) === 10 && str_starts_with($digits, '5')) {
            return '0'.$digits;
        }
        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return $digits;
        }

        return $telefon;
    }

    public function resolveDefaultStatus(Doktor $doktor): string
    {
        $ayarlar = $doktor->randevuAyari;

        return ($ayarlar && $ayarlar->randevu_onay_tipi === 'otomatik')
            ? 'onaylandi'
            : 'beklemede';
    }

    public function assertPackageAppointmentLimit(Doktor $doktor): void
    {
        $paket = $doktor->aktifPaket();
        if ($paket && ! is_null($paket->max_randevu_sayisi)) {
            $count = $doktor->randevular()->count();
            if ($count >= $paket->max_randevu_sayisi) {
                // Doktoru günde bir kez uyar (spam engeli)
                try {
                    $cacheKey = 'doktor-paket-limit-notify:'.$doktor->id.':'.now()->toDateString();
                    if (! \Illuminate\Support\Facades\Cache::has($cacheKey)) {
                        $doktor->notify(new \App\Notifications\PaketLimitBildirimi(
                            (int) $paket->max_randevu_sayisi,
                            (int) $count
                        ));
                        \Illuminate\Support\Facades\Cache::put($cacheKey, 1, now()->endOfDay());
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Paket limit bildirimi: '.$e->getMessage());
                }

                throw new InvalidArgumentException('Bu hekimin randevu limiti dolmuştur. Lütfen hekimle iletişime geçin.');
            }
        }
    }

    /**
     * Cancel by public management token (guest-friendly).
     */
    public function cancelByToken(string $token): Randevu
    {
        $randevu = Randevu::where('yonetim_token', $token)->first();
        if (! $randevu) {
            throw new InvalidArgumentException('Geçersiz veya süresi dolmuş randevu bağlantısı.');
        }

        if (! in_array($randevu->durum, ['beklemede', 'onaylandi'], true)) {
            throw new InvalidArgumentException('Bu randevu iptal edilemez durumdadır.');
        }

        $doktor = $randevu->doktor;
        $ayarlar = $doktor?->randevuAyari;

        if ($ayarlar && ! $ayarlar->randevu_iptal_aktif_mi) {
            throw new InvalidArgumentException('Bu hekim için online iptal kapalıdır. Lütfen klinik ile iletişime geçin.');
        }

        if ($ayarlar && $ayarlar->iptal_saat_limiti > 0) {
            $tarihStr = $randevu->tarih instanceof \DateTimeInterface
                ? $randevu->tarih->format('Y-m-d')
                : \Carbon\Carbon::parse($randevu->tarih)->toDateString();
            $randevuZamani = \Carbon\Carbon::parse($tarihStr.' '.$randevu->saat);
            $limitZamani = now()->addHours((int) $ayarlar->iptal_saat_limiti);
            if ($randevuZamani->lt($limitZamani)) {
                throw new InvalidArgumentException(
                    'Randevu başlangıcına '.$ayarlar->iptal_saat_limiti.' saatten az kaldığı için iptal edilemez.'
                );
            }
        }

        $eski = $randevu->durum;
        $randevu->update(['durum' => 'iptal']);
        \App\Events\RandevuDurumuDegisti::dispatch($randevu, $eski, 'iptal');

        return $randevu->fresh();
    }

    /**
     * Create a new appointment.
     *
     * @param  array{
     *     doktor: Doktor,
     *     hasta: Hasta,
     *     hizmet_id: int,
     *     tarih: string,
     *     saat: string,
     *     not?: string|null,
     *     durum?: string,
     *     ad?: string|null,
     *     soyad?: string|null,
     *     telefon?: string|null,
     *     e_posta?: string|null,
     *     gorusme_tipi?: string,
     *     skip_schedule_validation?: bool,
     * }  $data
     *
     * @throws InvalidArgumentException when validation fails
     */
    public function create(array $data): Randevu
    {
        /** @var Doktor $doktor */
        $doktor = $data['doktor'];
        /** @var Hasta $hasta */
        $hasta = $data['hasta'];
        $tarih = $data['tarih'];
        $saat = substr((string) $data['saat'], 0, 5);
        $hizmetId = (int) $data['hizmet_id'];
        $skipSchedule = (bool) ($data['skip_schedule_validation'] ?? false);

        $gorusmeTipi = ($data['gorusme_tipi'] ?? 'yuz_yuze') === 'online' ? 'online' : 'yuz_yuze';
        if ($gorusmeTipi === 'online') {
            $paket = $doktor->aktifPaket();
            if (! $paket || ! $paket->hasFeature('online_gorusme')) {
                throw new InvalidArgumentException('Online görüşme bu hekimin paketinde yer almıyor.');
            }
        }
        // Normalize so create body always uses the validated value
        $data['gorusme_tipi'] = $gorusmeTipi;

        $hizmet = Hizmet::query()
            ->where('id', $hizmetId)
            ->where('doktor_id', $doktor->id)
            ->where('aktif_mi', true)
            ->first();

        if (! $hizmet) {
            throw new InvalidArgumentException('Seçilen hizmet bu hekime ait değil veya aktif değil.');
        }

        if (! $skipSchedule) {
            $hata = $this->dogrulamaService->dogrula($doktor, $tarih, $saat);
            if ($hata) {
                throw new InvalidArgumentException($hata);
            }
        }

        $lockKey = sprintf('randevu-slot:%d:%s:%s', $doktor->id, $tarih, $saat);
        $lock = Cache::lock($lockKey, 10);

        try {
            return $lock->block(5, function () use ($doktor, $hasta, $hizmet, $tarih, $saat, $data) {
                return DB::transaction(function () use ($doktor, $hasta, $hizmet, $tarih, $saat, $data) {
                    $cakisma = Randevu::query()
                        ->where('doktor_id', $doktor->id)
                        ->whereDate('tarih', $tarih)
                        ->where(function ($q) use ($saat) {
                            $q->where('saat', $saat)
                                ->orWhere('saat', $saat.':00');
                        })
                        ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi'])
                        ->lockForUpdate()
                        ->exists();

                    if ($cakisma) {
                        throw new InvalidArgumentException('Seçtiğiniz randevu saati maalesef doludur. Lütfen başka bir saat seçin.');
                    }

                    try {
                        $gorusmeTipi = ($data['gorusme_tipi'] ?? 'yuz_yuze') === 'online' ? 'online' : 'yuz_yuze';

                        $randevu = Randevu::create([
                            'doktor_id' => $doktor->id,
                            'hizmet_id' => $hizmet->id,
                            'hasta_id' => $hasta->id,
                            'ad' => $data['ad'] ?? $hasta->ad,
                            'soyad' => $data['soyad'] ?? $hasta->soyad,
                            'telefon' => $data['telefon'] ?? $hasta->telefon,
                            'e_posta' => $data['e_posta'] ?? $hasta->e_posta,
                            'tarih' => $tarih,
                            'saat' => $saat,
                            'not' => $data['not'] ?? null,
                            'durum' => $data['durum'] ?? 'beklemede',
                            'gorusme_tipi' => $gorusmeTipi,
                            'yonetim_token' => $data['yonetim_token'] ?? Str::random(48),
                        ]);
                    } catch (\Illuminate\Database\QueryException $e) {
                        if (str_contains($e->getMessage(), 'slot_token') || str_contains(strtolower($e->getMessage()), 'unique')) {
                            throw new InvalidArgumentException('Seçtiğiniz randevu saati maalesef doludur. Lütfen başka bir saat seçin.');
                        }
                        throw $e;
                    }

                    RandevuOlusturuldu::dispatch($randevu);

                    return $randevu;
                });
            });
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            throw new InvalidArgumentException('Randevu kaydı şu an işlenemiyor. Lütfen birkaç saniye sonra tekrar deneyin.');
        }
    }

    /**
     * Reschedule an existing appointment with locking.
     *
     * @throws InvalidArgumentException
     */
    public function reschedule(Randevu $randevu, string $tarih, string $saat, bool $skipScheduleValidation = false): Randevu
    {
        $saat = substr($saat, 0, 5);
        $doktor = $randevu->doktor;

        if (! $doktor) {
            throw new InvalidArgumentException('Randevuya ait hekim bulunamadı.');
        }

        if (! $skipScheduleValidation) {
            $hata = $this->dogrulamaService->dogrula($doktor, $tarih, $saat, $randevu->id);
            if ($hata) {
                throw new InvalidArgumentException($hata);
            }
        }

        $lockKey = sprintf('randevu-slot:%d:%s:%s', $doktor->id, $tarih, $saat);
        $lock = Cache::lock($lockKey, 10);

        try {
            return $lock->block(5, function () use ($randevu, $doktor, $tarih, $saat) {
                return DB::transaction(function () use ($randevu, $doktor, $tarih, $saat) {
                    $cakisma = Randevu::query()
                        ->where('doktor_id', $doktor->id)
                        ->where('id', '!=', $randevu->id)
                        ->whereDate('tarih', $tarih)
                        ->where(function ($q) use ($saat) {
                            $q->where('saat', $saat)
                                ->orWhere('saat', $saat.':00');
                        })
                        ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi'])
                        ->lockForUpdate()
                        ->exists();

                    if ($cakisma) {
                        throw new InvalidArgumentException('Seçilen saat dilimi doludur.');
                    }

                    $eskiTarih = $randevu->tarih instanceof \DateTimeInterface
                        ? $randevu->tarih->format('Y-m-d')
                        : substr((string) $randevu->tarih, 0, 10);
                    $eskiSaat = substr((string) $randevu->saat, 0, 5);

                    $randevu->update([
                        'tarih' => $tarih,
                        'saat' => $saat,
                    ]);

                    $fresh = $randevu->fresh(['hasta', 'doktor', 'hizmet']);

                    // Hekim ertelediğinde hastayı bilgilendir
                    try {
                        if ($fresh?->hasta) {
                            $fresh->hasta->notify(new \App\Notifications\RandevuErtelemeBildirimi(
                                $fresh,
                                $eskiTarih,
                                $eskiSaat,
                                'hasta'
                            ));
                        }
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('Randevu erteleme hasta bildirimi: '.$e->getMessage());
                    }

                    return $fresh;
                });
            });
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            throw new InvalidArgumentException('Randevu kaydı şu an işlenemiyor. Lütfen birkaç saniye sonra tekrar deneyin.');
        }
    }
}
