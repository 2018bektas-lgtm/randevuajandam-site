<?php

namespace App\Console\Commands;

use App\Models\Doktor;
use App\Models\Klinik;
use App\Models\UyelikOdeme;
use App\Services\PaymentDriverService;
use App\Services\PaytrService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Abonelik otomatik yenileme:
 *   - PayTR: recurring_id varsa bugün biten/biten üyelikleri tekrarlayan ödeme ile yeniler.
 *   - iyzico: iyzico webhook üzerinden otomatik; bu command iyzico için sadece log/kontrol yapar.
 */
class AbonelikYenileCommand extends Command
{
    protected $signature   = 'abonelik:yenile {--dry-run : Gerçek ödeme yapmadan simüle et}';
    protected $description = 'PayTR recurring ile biten üyelikleri otomatik yeniler.';

    public function handle(PaymentDriverService $driver, PaytrService $paytr): int
    {
        $dryRun    = (bool) $this->option('dry-run');
        $isPaytr   = $driver->isPaytrActive();
        $renewed   = 0;
        $failed    = 0;

        $this->info('Abonelik yenileme başladı. Driver: ' . $driver->driver() . ($dryRun ? ' [DRY-RUN]' : ''));

        // ── PayTR recurring yenileme ──────────────────────────────────────────
        if ($isPaytr) {
            // Bugün biten veya dün biten (grace: 1 gün) + recurring_id var + yenileme kapalı değil
            $doktorlar = Doktor::query()
                ->where('aktif_mi', true)
                ->whereNotNull('paket_id')
                ->whereNotNull('paytr_recurring_id')
                ->where('abonelik_yenileme_kapali', false)
                ->whereNotNull('uyelik_bitis')
                ->where('uyelik_bitis', '<=', now()->endOfDay())
                ->where('uyelik_bitis', '>=', now()->subDay())
                ->get();

            foreach ($doktorlar as $doktor) {
                $paket = $doktor->paket;
                if (! $paket) {
                    continue;
                }

                $periyot = $doktor->odeme_periyodu ?? 'aylik';
                $tutar   = $periyot === 'aylik'
                    ? (float) $paket->aylik_fiyat
                    : (float) $paket->yillik_fiyat;

                $merchantOid = $paytr->makeMerchantOid('REN');

                $this->line("Doktor #{$doktor->id} — {$doktor->ad_soyad} — {$tutar} TL");

                if ($dryRun) {
                    $renewed++;
                    continue;
                }

                $result = $paytr->chargeRecurring((string) $doktor->paytr_recurring_id, [
                    'payment_amount' => $tutar,
                    'merchant_oid'   => $merchantOid,
                    'email'          => $doktor->e_posta,
                ]);

                if (($result['status'] ?? '') === 'success') {
                    $this->extendDoktorMembership($doktor, $paket, $periyot, $tutar, $merchantOid);
                    $renewed++;
                    Log::info('PayTR recurring: doktor yenilendi', ['doktor_id' => $doktor->id, 'oid' => $merchantOid]);
                } else {
                    $failed++;
                    Log::error('PayTR recurring: yenileme başarısız', [
                        'doktor_id' => $doktor->id,
                        'error'     => $result['errorMessage'] ?? '?',
                    ]);
                    // Bildirim: doktor_uyelik-hatirlat command zaten e-posta gönderiyor
                }
            }

            // Klinikler
            $klinikler = Klinik::query()
                ->where('aktif_mi', true)
                ->whereNotNull('paytr_recurring_id')
                ->where('abonelik_yenileme_kapali', false)
                ->whereNotNull('uyelik_bitis')
                ->where('uyelik_bitis', '<=', now()->endOfDay())
                ->where('uyelik_bitis', '>=', now()->subDay())
                ->with('paket', 'sahipDoktor')
                ->get();

            foreach ($klinikler as $klinik) {
                $paket  = $klinik->paket;
                $sahip  = $klinik->sahipDoktor;
                if (! $paket || ! $sahip) {
                    continue;
                }

                $periyot     = $klinik->odeme_periyodu ?? 'aylik';
                $tutar       = $periyot === 'aylik' ? (float) $paket->aylik_fiyat : (float) $paket->yillik_fiyat;
                $merchantOid = $paytr->makeMerchantOid('RKL');

                $this->line("Klinik #{$klinik->id} — {$klinik->ad} — {$tutar} TL");

                if ($dryRun) {
                    $renewed++;
                    continue;
                }

                $result = $paytr->chargeRecurring((string) $klinik->paytr_recurring_id, [
                    'payment_amount' => $tutar,
                    'merchant_oid'   => $merchantOid,
                    'email'          => $sahip->e_posta,
                ]);

                if (($result['status'] ?? '') === 'success') {
                    $this->extendKlinikMembership($klinik, $periyot, $tutar, $merchantOid, $sahip);
                    $renewed++;
                } else {
                    $failed++;
                    Log::error('PayTR recurring: klinik yenileme başarısız', [
                        'klinik_id' => $klinik->id,
                        'error'     => $result['errorMessage'] ?? '?',
                    ]);
                }
            }
        }

        // ── iyzico: sadece log ────────────────────────────────────────────────
        if (! $isPaytr) {
            $this->info('iyzico aktif — yenileme webhook üzerinden otomatik yönetilir.');
        }

        $this->info("Tamamlandı: {$renewed} yenilendi, {$failed} başarısız.");

        return self::SUCCESS;
    }

    protected function extendDoktorMembership(Doktor $doktor, \App\Models\Paket $paket, string $periyot, float $tutar, string $merchantOid): void
    {
        DB::transaction(function () use ($doktor, $paket, $periyot, $tutar, $merchantOid) {
            $bitis = $periyot === 'aylik' ? now()->addMonth() : now()->addYear();

            $doktor->forceFill([
                'uyelik_bitis'               => $bitis,
                'iyzico_subscription_status' => 'ACTIVE',
            ])->save();

            UyelikOdeme::create([
                'doktor_id'         => $doktor->id,
                'paket_id'          => $paket->id,
                'odeme_yontemi'     => 'paytr',
                'provider'          => 'paytr',
                'odeme_periyodu'    => $periyot,
                'tutar'             => $tutar,
                'durum'             => 'onaylandi',
                'onaylandi_at'      => now(),
                'merchant_oid'      => $merchantOid,
                'paytr_recurring_id'=> $doktor->paytr_recurring_id,
                'otomatik_yenileme' => true,
                'fatura_durumu'     => 'bekliyor',
            ]);
        });
    }

    protected function extendKlinikMembership(Klinik $klinik, string $periyot, float $tutar, string $merchantOid, Doktor $sahip): void
    {
        DB::transaction(function () use ($klinik, $periyot, $tutar, $merchantOid, $sahip) {
            $bitis = $periyot === 'aylik' ? now()->addMonth() : now()->addYear();

            $klinik->forceFill([
                'uyelik_bitis'               => $bitis,
                'iyzico_subscription_status' => 'ACTIVE',
            ])->save();

            UyelikOdeme::create([
                'doktor_id'         => $sahip->id,
                'paket_id'          => $klinik->paket_id,
                'odeme_yontemi'     => 'paytr',
                'provider'          => 'paytr',
                'odeme_periyodu'    => $periyot,
                'tutar'             => $tutar,
                'durum'             => 'onaylandi',
                'onaylandi_at'      => now(),
                'merchant_oid'      => $merchantOid,
                'paytr_recurring_id'=> $klinik->paytr_recurring_id,
                'otomatik_yenileme' => true,
                'fatura_durumu'     => 'bekliyor',
            ]);
        });
    }
}
