<?php

namespace App\Services;

use App\Models\Doktor;
use App\Models\ReferansDavet;
use App\Models\UyelikOdeme;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReferansService
{
    public function aktifMi(): bool
    {
        return (bool) config('referans.aktif', true);
    }

    public function indirimYuzde(): int
    {
        return max(0, min(50, (int) config('referans.yuzde_davet_edilen', 15)));
    }

    public function komisyonYuzde(): int
    {
        return max(0, min(50, (int) config('referans.yuzde_davet_eden', 20)));
    }

    public function ensureKod(Doktor $doktor): string
    {
        if ($doktor->referans_kodu) {
            return (string) $doktor->referans_kodu;
        }

        do {
            $kod = 'RA'.strtoupper(Str::random(8));
        } while (Doktor::query()->where('referans_kodu', $kod)->exists());

        $doktor->forceFill(['referans_kodu' => $kod])->save();

        return $kod;
    }

    public function resolveKod(?string $kod): ?Doktor
    {
        $kod = strtoupper(trim((string) $kod));
        if ($kod === '' || ! $this->aktifMi()) {
            return null;
        }

        return Doktor::query()
            ->where('referans_kodu', $kod)
            ->where('aktif_mi', true)
            ->first();
    }

    /**
     * Kayıt sonrası davet eden bağla + bekleyen satır.
     */
    public function attachOnRegister(Doktor $yeni, ?string $kod): void
    {
        if (! $this->aktifMi()) {
            return;
        }

        $kod = $kod ?: request()->cookie(config('referans.cookie_name', 'ra_ref'));
        $kod = $kod ?: session('ra_ref');
        $davetEden = $this->resolveKod($kod);
        if (! $davetEden) {
            return;
        }

        if ((int) $davetEden->id === (int) $yeni->id) {
            return;
        }

        if ($yeni->davet_eden_id) {
            return;
        }

        // Aynı e-posta / TC engeli
        if ($yeni->e_posta && mb_strtolower($yeni->e_posta) === mb_strtolower((string) $davetEden->e_posta)) {
            return;
        }
        if ($yeni->tc_kimlik_no && $davetEden->tc_kimlik_no
            && $yeni->tc_kimlik_no === $davetEden->tc_kimlik_no) {
            return;
        }

        $yeni->forceFill([
            'davet_eden_id' => $davetEden->id,
            'referans_kodu_kullanilan' => $davetEden->referans_kodu,
        ])->save();

        ReferansDavet::query()->firstOrCreate(
            ['davet_edilen_id' => $yeni->id],
            [
                'davet_eden_id' => $davetEden->id,
                'kod' => $davetEden->referans_kodu,
                'durum' => 'bekliyor',
                'indirim_yuzde_davet_edilen' => $this->indirimYuzde(),
                'komisyon_yuzde_davet_eden' => $this->komisyonYuzde(),
            ]
        );
    }

    /**
     * Checkout: davet edilen için indirimli tutar.
     *
     * @return array{tutar: float, brut: float, indirim_yuzde: int, indirim_uygulandi: bool}
     */
    public function indirimliTutar(Doktor $doktor, float $brut): array
    {
        $brut = max(0, round($brut, 2));
        $yuzde = 0;
        $uygula = false;

        if ($this->aktifMi() && $doktor->davet_eden_id && $brut > 0) {
            $davet = ReferansDavet::query()
                ->where('davet_edilen_id', $doktor->id)
                ->whereIn('durum', ['bekliyor', 'odullendirildi'])
                ->first();

            // İndirim sadece henüz ödüllendirilmemiş / ilk ödeme öncesi
            if ($davet && $davet->durum === 'bekliyor') {
                $yuzde = (int) ($davet->indirim_yuzde_davet_edilen ?: $this->indirimYuzde());
                $uygula = $yuzde > 0;
            } elseif (! $davet && $doktor->davet_eden_id) {
                $yuzde = $this->indirimYuzde();
                $uygula = $yuzde > 0;
            }
        }

        $net = $uygula
            ? round($brut * (1 - ($yuzde / 100)), 2)
            : $brut;

        return [
            'tutar' => max(0, $net),
            'brut' => $brut,
            'indirim_yuzde' => $uygula ? $yuzde : 0,
            'indirim_uygulandi' => $uygula,
        ];
    }

    /**
     * İlk ücretli üyelik ödemesi onayında çağrılır (idempotent).
     */
    public function odullendir(UyelikOdeme $odeme): void
    {
        if (! $this->aktifMi()) {
            return;
        }

        $odeme->loadMissing(['doktor', 'paket']);
        $doktor = $odeme->doktor;
        if (! $doktor || ! $doktor->davet_eden_id) {
            return;
        }

        $net = (float) $odeme->tutar;
        $min = (float) config('referans.min_odeme_tl', 1);
        if ($net < $min) {
            return;
        }

        // Deneme / ücretsiz
        if (($odeme->odeme_periyodu ?? '') === 'deneme') {
            return;
        }

        try {
            DB::transaction(function () use ($odeme, $doktor, $net) {
                $davet = ReferansDavet::query()
                    ->where('davet_edilen_id', $doktor->id)
                    ->lockForUpdate()
                    ->first();

                if (! $davet) {
                    $davet = ReferansDavet::query()->create([
                        'davet_eden_id' => $doktor->davet_eden_id,
                        'davet_edilen_id' => $doktor->id,
                        'kod' => $doktor->referans_kodu_kullanilan ?? '',
                        'durum' => 'bekliyor',
                        'indirim_yuzde_davet_edilen' => $this->indirimYuzde(),
                        'komisyon_yuzde_davet_eden' => $this->komisyonYuzde(),
                    ]);
                    $davet = ReferansDavet::query()->whereKey($davet->id)->lockForUpdate()->first();
                }

                if (! $davet || $davet->durum === 'odullendirildi') {
                    return;
                }

                $davetEden = Doktor::query()->lockForUpdate()->find($davet->davet_eden_id);
                if (! $davetEden) {
                    $davet->update([
                        'durum' => 'reddedildi',
                        'red_nedeni' => 'Davet eden bulunamadı',
                    ]);

                    return;
                }

                $limit = (int) config('referans.aylik_limit_davet_eden', 5);
                $buAy = ReferansDavet::query()
                    ->where('davet_eden_id', $davetEden->id)
                    ->where('durum', 'odullendirildi')
                    ->where('odullendirildi_at', '>=', now()->startOfMonth())
                    ->count();

                if ($buAy >= $limit) {
                    $davet->update([
                        'durum' => 'reddedildi',
                        'red_nedeni' => 'Aylık referans limiti dolu',
                        'uyelik_odeme_id' => $odeme->id,
                        'odeme_tutari_net' => $net,
                    ]);

                    return;
                }

                $komisyon = (int) ($davet->komisyon_yuzde_davet_eden ?: $this->komisyonYuzde());
                $periyotGun = ($odeme->odeme_periyodu ?? 'aylik') === 'yillik' ? 365 : 30;
                $odulGun = max(1, (int) round($periyotGun * ($komisyon / 100)));

                $this->uzatUyelik($davetEden, $odulGun);

                $brut = $net;
                $kurulum = is_array($odeme->kurulum_verisi) ? $odeme->kurulum_verisi : [];
                if (isset($kurulum['tutar_brut'])) {
                    $brut = (float) $kurulum['tutar_brut'];
                }

                $davet->update([
                    'durum' => 'odullendirildi',
                    'uyelik_odeme_id' => $odeme->id,
                    'odul_gun_davet_eden' => $odulGun,
                    'komisyon_yuzde_davet_eden' => $komisyon,
                    'odeme_tutari_brut' => $brut,
                    'odeme_tutari_net' => $net,
                    'odullendirildi_at' => now(),
                    'red_nedeni' => null,
                ]);

                Log::info('referans_odullendirildi', [
                    'davet_id' => $davet->id,
                    'davet_eden_id' => $davetEden->id,
                    'davet_edilen_id' => $doktor->id,
                    'odul_gun' => $odulGun,
                    'odeme_id' => $odeme->id,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('referans_odullendir_hata', [
                'odeme_id' => $odeme->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function uzatUyelik(Doktor $doktor, int $gun): void
    {
        $gun = max(0, $gun);
        if ($gun === 0) {
            return;
        }

        $base = $doktor->uyelik_bitis && $doktor->uyelik_bitis->isFuture()
            ? $doktor->uyelik_bitis->copy()
            : now();

        $doktor->forceFill([
            'uyelik_bitis' => $base->addDays($gun),
        ])->save();

        // Klinik sahibi: klinik üyelik süresini de uzat
        if ($doktor->klinikSahibiMi() && $doktor->klinik_id) {
            $klinik = $doktor->klinik;
            if ($klinik && $klinik->paket_id) {
                $kBase = $klinik->uyelik_bitis && $klinik->uyelik_bitis->isFuture()
                    ? $klinik->uyelik_bitis->copy()
                    : now();
                $klinik->forceFill([
                    'uyelik_bitis' => $kBase->addDays($gun),
                ])->save();
            }
        }
    }

    public function paylasimLinki(Doktor $doktor): string
    {
        $kod = $this->ensureKod($doktor);

        // Paket seçimi zorunlu; ref cookie/session ile taşınır
        return url('/paketler?ref='.$kod);
    }

    /**
     * @return array{kod: string, link: string, bekleyen: int, odullu: int, bu_ay: int, limit: int, indirim: int, komisyon: int}
     */
    public function panelOzet(Doktor $doktor): array
    {
        $kod = $this->ensureKod($doktor);
        $limit = (int) config('referans.aylik_limit_davet_eden', 5);
        $buAy = ReferansDavet::query()
            ->where('davet_eden_id', $doktor->id)
            ->where('durum', 'odullendirildi')
            ->where('odullendirildi_at', '>=', now()->startOfMonth())
            ->count();

        return [
            'kod' => $kod,
            'link' => $this->paylasimLinki($doktor),
            'bekleyen' => ReferansDavet::query()->where('davet_eden_id', $doktor->id)->where('durum', 'bekliyor')->count(),
            'odullu' => ReferansDavet::query()->where('davet_eden_id', $doktor->id)->where('durum', 'odullendirildi')->count(),
            'bu_ay' => $buAy,
            'limit' => $limit,
            'kalan' => max(0, $limit - $buAy),
            'indirim' => $this->indirimYuzde(),
            'komisyon' => $this->komisyonYuzde(),
        ];
    }
}
