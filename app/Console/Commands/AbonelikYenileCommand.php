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
 * PayTR kayıtlı kart (utoken+ctoken) ile biten üyelikleri Non3D yeniler.
 *
 * @see https://dev.paytr.com/direkt-api/kart-saklama-api/kayitli-kart-tekrarlayan-odeme
 *
 * PAYTR_RECURRING_ENABLED=true ve mağazada Non3D + kart saklama yetkisi gerekir.
 */
class AbonelikYenileCommand extends Command
{
    protected $signature = 'abonelik:yenile
                            {--dry-run : Gerçek ödeme yapmadan simüle et}
                            {--force : PAYTR_RECURRING_ENABLED olmadan da dene}';

    protected $description = 'PayTR utoken/ctoken ile biten üyelikleri otomatik yeniler.';

    public function handle(PaymentDriverService $driver, PaytrService $paytr): int
    {
        $recurringEnabled = (bool) config('services.paytr.recurring_enabled', false);
        if (! $recurringEnabled && ! $this->option('force')) {
            $this->warn('abonelik:yenile atlandı — PAYTR_RECURRING_ENABLED=false.');
            Log::info('abonelik:yenile skipped: PAYTR_RECURRING_ENABLED is false');

            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $isPaytr = $driver->isPaytrActive();
        $renewed = 0;
        $failed = 0;

        $this->info('Abonelik yenileme. Driver: '.$driver->driver().($dryRun ? ' [DRY-RUN]' : ''));

        if ($isPaytr) {
            $doktorlar = Doktor::query()
                ->where('aktif_mi', true)
                ->whereNotNull('paket_id')
                ->whereNotNull('paytr_utoken')
                ->whereNotNull('paytr_ctoken')
                ->where('abonelik_yenileme_kapali', false)
                ->whereNotNull('uyelik_bitis')
                // Bitiş günü ve 1 gün tolerans (cron kaçırırsa)
                ->where('uyelik_bitis', '<=', now()->endOfDay())
                ->where('uyelik_bitis', '>=', now()->subDays(2)->startOfDay())
                ->get();

            foreach ($doktorlar as $doktor) {
                $paket = $doktor->paket;
                if (! $paket) {
                    continue;
                }

                // Klinik sahibi: klinik token'ı üzerinden yenile (aşağıda)
                if ($doktor->tur === 'klinik' && $doktor->klinik_rolu === 'sahip' && $doktor->klinik_id) {
                    continue;
                }

                $periyot = $doktor->odeme_periyodu ?? 'aylik';
                $tutar = \App\Support\GarantiFiyat::yenilemeTutari($doktor, $paket, $periyot);

                $merchantOid = $paytr->makeMerchantOid('REN');
                $this->line("Doktor #{$doktor->id} — {$doktor->ad_soyad} — {$tutar} TL");

                if ($dryRun) {
                    $renewed++;
                    continue;
                }

                $result = $paytr->chargeStoredCardRecurring(
                    (string) $doktor->paytr_utoken,
                    (string) $doktor->paytr_ctoken,
                    [
                        'payment_amount' => $tutar,
                        'merchant_oid' => $merchantOid,
                        'email' => $doktor->e_posta,
                        'user_name' => $doktor->ad_soyad,
                        'user_phone' => $doktor->telefon,
                        'user_address' => $doktor->adres ?: 'Turkiye',
                        'basket_name' => 'Randevu Ajandam Yenileme - '.$paket->ad,
                    ]
                );

                if (in_array($result['status'] ?? '', ['success', 'wait_callback'], true)) {
                    // success: hemen uzat; wait_callback: notify üyelik satırını onaylar — yine de kayıt aç
                    $this->extendDoktorMembership($doktor, $paket, $periyot, $tutar, $merchantOid, $result['status'] === 'success');
                    $renewed++;
                    Log::info('PayTR recurring doktor', [
                        'doktor_id' => $doktor->id,
                        'oid' => $merchantOid,
                        'status' => $result['status'],
                    ]);
                } else {
                    $failed++;
                    Log::error('PayTR recurring doktor fail', [
                        'doktor_id' => $doktor->id,
                        'error' => $result['errorMessage'] ?? '?',
                    ]);
                }
            }

            $klinikler = Klinik::query()
                ->where('aktif_mi', true)
                ->whereNotNull('paytr_utoken')
                ->whereNotNull('paytr_ctoken')
                ->where('abonelik_yenileme_kapali', false)
                ->whereNotNull('uyelik_bitis')
                ->where('uyelik_bitis', '<=', now()->endOfDay())
                ->where('uyelik_bitis', '>=', now()->subDays(2)->startOfDay())
                ->with(['paket', 'sahipDoktor'])
                ->get();

            foreach ($klinikler as $klinik) {
                $paket = $klinik->paket;
                $sahip = $klinik->sahipDoktor;
                if (! $paket || ! $sahip) {
                    continue;
                }

                $periyot = $klinik->odeme_periyodu ?? 'aylik';
                $tutar = \App\Support\GarantiFiyat::yenilemeTutari($klinik, $paket, $periyot);
                $merchantOid = $paytr->makeMerchantOid('RKL');

                $this->line("Klinik #{$klinik->id} — {$klinik->ad} — {$tutar} TL");

                if ($dryRun) {
                    $renewed++;
                    continue;
                }

                $result = $paytr->chargeStoredCardRecurring(
                    (string) $klinik->paytr_utoken,
                    (string) $klinik->paytr_ctoken,
                    [
                        'payment_amount' => $tutar,
                        'merchant_oid' => $merchantOid,
                        'email' => $sahip->e_posta,
                        'user_name' => $sahip->ad_soyad,
                        'user_phone' => $sahip->telefon,
                        'user_address' => $klinik->adres ?: 'Turkiye',
                        'basket_name' => 'Randevu Ajandam Klinik Yenileme - '.$paket->ad,
                    ]
                );

                if (in_array($result['status'] ?? '', ['success', 'wait_callback'], true)) {
                    $this->extendKlinikMembership($klinik, $periyot, $tutar, $merchantOid, $sahip, $result['status'] === 'success');
                    $renewed++;
                } else {
                    $failed++;
                    Log::error('PayTR recurring klinik fail', [
                        'klinik_id' => $klinik->id,
                        'error' => $result['errorMessage'] ?? '?',
                    ]);
                }
            }
        } else {
            $this->info('iyzico aktif — yenileme webhook üzerinden.');
        }

        $this->info("Tamamlandı: {$renewed} yenilendi/istek, {$failed} başarısız.");

        return self::SUCCESS;
    }

    protected function extendDoktorMembership(
        Doktor $doktor,
        \App\Models\Paket $paket,
        string $periyot,
        float $tutar,
        string $merchantOid,
        bool $immediateApprove
    ): void {
        DB::transaction(function () use ($doktor, $paket, $periyot, $tutar, $merchantOid, $immediateApprove) {
            if ($immediateApprove) {
                $bitis = $periyot === 'aylik' ? now()->addMonth() : now()->addYear();
                $doktor->forceFill([
                    'uyelik_bitis' => $bitis,
                    'iyzico_subscription_status' => 'ACTIVE',
                    'uyelik_hatirlat_7_at' => null,
                    'uyelik_hatirlat_3_at' => null,
                    'uyelik_hatirlat_1_at' => null,
                ])->save();
            }

            UyelikOdeme::create([
                'doktor_id' => $doktor->id,
                'paket_id' => $paket->id,
                'odeme_yontemi' => 'paytr',
                'provider' => 'paytr',
                'odeme_periyodu' => $periyot,
                'tutar' => $tutar,
                'durum' => $immediateApprove ? 'onaylandi' : 'beklemede',
                'onaylandi_at' => $immediateApprove ? now() : null,
                'merchant_oid' => $merchantOid,
                'paytr_recurring_id' => $doktor->paytr_ctoken,
                'paytr_utoken' => $doktor->paytr_utoken,
                'paytr_ctoken' => $doktor->paytr_ctoken,
                'otomatik_yenileme' => true,
                'fatura_durumu' => 'bekliyor',
            ]);
        });
    }

    protected function extendKlinikMembership(
        Klinik $klinik,
        string $periyot,
        float $tutar,
        string $merchantOid,
        Doktor $sahip,
        bool $immediateApprove
    ): void {
        DB::transaction(function () use ($klinik, $periyot, $tutar, $merchantOid, $sahip, $immediateApprove) {
            if ($immediateApprove) {
                $bitis = $periyot === 'aylik' ? now()->addMonth() : now()->addYear();
                $klinik->forceFill([
                    'uyelik_bitis' => $bitis,
                    'iyzico_subscription_status' => 'ACTIVE',
                ])->save();
                $sahip->forceFill([
                    'uyelik_bitis' => $bitis,
                    'uyelik_hatirlat_7_at' => null,
                    'uyelik_hatirlat_3_at' => null,
                    'uyelik_hatirlat_1_at' => null,
                ])->save();
            }

            UyelikOdeme::create([
                'doktor_id' => $sahip->id,
                'paket_id' => $klinik->paket_id,
                'odeme_yontemi' => 'paytr',
                'provider' => 'paytr',
                'odeme_periyodu' => $periyot,
                'tutar' => $tutar,
                'durum' => $immediateApprove ? 'onaylandi' : 'beklemede',
                'onaylandi_at' => $immediateApprove ? now() : null,
                'merchant_oid' => $merchantOid,
                'paytr_recurring_id' => $klinik->paytr_ctoken,
                'paytr_utoken' => $klinik->paytr_utoken,
                'paytr_ctoken' => $klinik->paytr_ctoken,
                'otomatik_yenileme' => true,
                'fatura_durumu' => 'bekliyor',
            ]);
        });
    }
}
