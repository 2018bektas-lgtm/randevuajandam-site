<?php

namespace App\Services;

use App\Models\SiteAyari;
use App\Models\UyelikOdeme;
use App\Models\Doktor;
use App\Models\Klinik;
use App\Models\Il;
use App\Models\Ilce;
use App\Support\MetaPixel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Ödeme kanalları bağımsız aç/kapa:
 * site_ayarlari.paytr_aktif | iyzico_aktif | havale_aktif
 * (Eski odeme_saglayici alanı geriye dönük senkron tutulur.)
 */
class PaymentDriverService
{
    /**
     * Geriye dönük: birincil sürücü (yenileme / varsayılan).
     * Öncelik: iyzico (aktif+yapılandırılmış) → paytr → paytr (fallback string).
     */
    public function driver(): string
    {
        if ($this->isIyzicoActive()) {
            return 'iyzico';
        }

        return 'paytr';
    }

    /** Yönetim paneli bayrağı (yapılandırma şartı yok). */
    public function isPaytrEnabled(): bool
    {
        $ayar = SiteAyari::cached();
        if ($ayar !== null && $ayar->getAttribute('paytr_aktif') !== null) {
            return (bool) $ayar->paytr_aktif;
        }

        // Migration öncesi: exclusive odeme_saglayici
        return trim((string) ($ayar?->odeme_saglayici ?? 'paytr')) !== 'iyzico';
    }

    public function isIyzicoEnabled(): bool
    {
        $ayar = SiteAyari::cached();
        if ($ayar !== null && $ayar->getAttribute('iyzico_aktif') !== null) {
            return (bool) $ayar->iyzico_aktif;
        }

        return trim((string) ($ayar?->odeme_saglayici ?? '')) === 'iyzico'
            || (bool) ($ayar?->iyzico_enabled ?? false);
    }

    public function isHavaleEnabled(): bool
    {
        $ayar = SiteAyari::cached();
        if ($ayar !== null && $ayar->getAttribute('havale_aktif') !== null) {
            return (bool) $ayar->havale_aktif;
        }

        return true;
    }

    /** Bayrak + merchant/API yapılandırması. */
    public function isPaytrActive(): bool
    {
        return $this->isPaytrEnabled() && app(PaytrService::class)->isConfigured();
    }

    public function isIyzicoActive(): bool
    {
        if (! $this->isIyzicoEnabled()) {
            return false;
        }

        return app(IyzicoSubscriptionService::class)->isConfigured();
    }

    public function isHavaleActive(): bool
    {
        if (! $this->isHavaleEnabled()) {
            return false;
        }

        $ayar = SiteAyari::cached() ?? SiteAyari::query()->first();

        return filled($ayar?->banka_adi)
            && filled($ayar?->banka_hesap_sahibi)
            && filled($ayar?->banka_iban);
    }

    /**
     * Checkout'ta seçilebilir yöntemler: paytr | iyzico | havale
     *
     * @return list<string>
     */
    public function availableMethods(): array
    {
        $methods = [];
        if ($this->isPaytrActive()) {
            $methods[] = 'paytr';
        }
        if ($this->isIyzicoActive()) {
            $methods[] = 'iyzico';
        }
        if ($this->isHavaleActive()) {
            $methods[] = 'havale';
        }

        return $methods;
    }

    /**
     * Ödeme akışını başlat.
     * $preferredMethod: 'paytr' | 'iyzico' | null (driver fallback)
     *
     * @param  array<string, mixed>  $kurulum
     * @param  array<string, mixed>  $kartBilgileri  (iyzico için)
     */
    public function startCheckout(
        Doktor $doktor,
        \App\Models\Paket $paket,
        string $periyot,
        float $tutar,
        array $kurulum,
        Request $request,
        array $kartBilgileri = [],
        ?string $preferredMethod = null
    ) {
        $method = $preferredMethod ?: $this->driver();

        if ($method === 'iyzico' && $this->isIyzicoActive()) {
            return $this->startIyzicoCheckout($doktor, $paket, $periyot, $tutar, $kurulum, $kartBilgileri);
        }

        if ($method === 'paytr' && $this->isPaytrActive()) {
            return $this->startPaytrCheckout($doktor, $paket, $periyot, $tutar, $kurulum, $request);
        }

        // Tercih geçersizse diğer aktif POS'a düş
        if ($this->isPaytrActive()) {
            return $this->startPaytrCheckout($doktor, $paket, $periyot, $tutar, $kurulum, $request);
        }
        if ($this->isIyzicoActive()) {
            return $this->startIyzicoCheckout($doktor, $paket, $periyot, $tutar, $kurulum, $kartBilgileri);
        }

        return back()->withInput()->withErrors([
            'odeme_yontemi' => 'Kartlı ödeme şu an kapalı. Havale ile devam edebilir veya yöneticiye bildirebilirsiniz.',
        ]);
    }

    // ─── PayTR ────────────────────────────────────────────────────────────────

    protected function startPaytrCheckout(
        Doktor $doktor,
        \App\Models\Paket $paket,
        string $periyot,
        float $tutar,
        array $kurulum,
        Request $request
    ) {
        $paytr = app(PaytrService::class);
        if (! $paytr->isConfigured()) {
            return back()->withInput()->withErrors([
                'paket_id' => 'Kartlı ödeme (PayTR) yapılandırılmamış. Havale ile devam edebilirsiniz.',
            ]);
        }

        $refFiyat = app(ReferansService::class)->indirimliTutar($doktor, $tutar);
        $tutar    = $refFiyat['tutar'];
        $kurulum  = array_merge($kurulum, [
            'tutar_brut'               => $refFiyat['brut'],
            'referans_indirim_yuzde'   => $refFiyat['indirim_yuzde'],
        ]);

        $merchantOid = $paytr->makeMerchantOid();
        $faturaSnap = is_array($kurulum['fatura'] ?? null) ? $kurulum['fatura'] : ($doktor->faturaBilgisiArray());
        UyelikOdeme::create([
            'doktor_id'    => $doktor->id,
            'paket_id'     => $paket->id,
            'odeme_yontemi'=> 'paytr',
            'provider'     => 'paytr',
            'odeme_periyodu' => $periyot,
            'tutar'        => $tutar,
            'durum'        => 'beklemede',
            'merchant_oid' => $merchantOid,
            'kurulum_verisi' => $kurulum,
            'fatura_bilgisi' => $faturaSnap,
            'fatura_durumu' => 'bekliyor',
        ]);

        $tokenResult = $paytr->createIframeToken([
            'merchant_oid'  => $merchantOid,
            'email'         => (string) (($faturaSnap['email'] ?? null) ?: $doktor->e_posta),
            'payment_amount'=> $tutar,
            'user_name'     => (string) (($faturaSnap['unvan'] ?? null) ?: $doktor->ad_soyad),
            'user_address'  => (string) (($faturaSnap['adres'] ?? null) ?: ($doktor->adres ?: ($doktor->il?->ad ?? 'Turkiye'))),
            'user_phone'    => (string) (($faturaSnap['telefon'] ?? null) ?: $doktor->telefon),
            'user_ip'       => $request->ip(),
            'basket_name'   => 'Randevu Ajandam - ' . $paket->ad . ' (' . $periyot . ')',
            // Risksiz model: tek seferlik iFrame; otomatik recurring yok
            'recurring'     => false,
        ]);

        if (($tokenResult['status'] ?? '') !== 'success') {
            UyelikOdeme::where('merchant_oid', $merchantOid)->update(['durum' => 'reddedildi']);

            return back()->withInput()->withErrors([
                'paket_id' => $tokenResult['errorMessage'] ?? 'PayTR ödeme oturumu açılamadı.',
            ]);
        }

        session(['paytr_iframe_token_' . $merchantOid => $tokenResult['token']]);

        MetaPixel::queue('InitiateCheckout', array_merge(
            MetaPixel::money((float) $tutar),
            ['content_name' => $paket->ad, 'content_ids' => [(string) $paket->id], 'content_type' => 'product', 'num_items' => 1]
        ));

        return redirect()->route('frontend.odeme.paytr.iframe', ['merchantOid' => $merchantOid]);
    }

    // ─── iyzico ───────────────────────────────────────────────────────────────

    protected function startIyzicoCheckout(
        Doktor $doktor,
        \App\Models\Paket $paket,
        string $periyot,
        float $tutar,
        array $kurulum,
        array $kartBilgileri
    ) {
        $iyzico = app(IyzicoSubscriptionService::class);

        if (! $iyzico->isConfigured()) {
            return back()->withInput()->withErrors([
                'paket_id' => 'Kartlı ödeme (iyzico) yapılandırılmamış. Yöneticiye bildirin.',
            ]);
        }

        $result = $iyzico->subscribeDoctor($doktor, $paket, $periyot, $kartBilgileri);

        if (($result['status'] ?? '') !== 'success') {
            return back()->withInput()->withErrors([
                'kart_no' => $result['errorMessage'] ?? 'Abonelik oluşturulamadı. Kart bilgilerinizi kontrol edin.',
            ]);
        }

        // Aboneliği kaydet ve üyeliği aktifte
        try {
            $this->activateIyzicoMembership($doktor, $paket, $periyot, $tutar, $kurulum, $result);
        } catch (\Throwable $e) {
            Log::error('iyzico aktivasyon hatası: ' . $e->getMessage(), [
                'doktor_id'      => $doktor->id,
                'ref'            => $result['referenceCode'] ?? null,
            ]);

            return back()->withInput()->withErrors([
                'paket_id' => 'Ödeme alındı ancak üyelik aktifleştirilemedi. Lütfen destek alın.',
            ]);
        }

        MetaPixel::queue('Purchase', array_merge(
            MetaPixel::money($tutar),
            ['content_name' => $paket->ad, 'content_ids' => [(string) $paket->id], 'content_type' => 'product', 'num_items' => 1]
        ));

        return redirect()->route('frontend.odeme.paytr.ok')
            ->with('iyzico_success', true);
    }

    protected function activateIyzicoMembership(
        Doktor $doktor,
        \App\Models\Paket $paket,
        string $periyot,
        float $tutar,
        array $kurulum,
        array $iyzicoResult
    ): void {
        DB::transaction(function () use ($doktor, $paket, $periyot, $tutar, $kurulum, $iyzicoResult) {
            $ref       = $iyzicoResult['referenceCode'] ?? '';
            $subStatus = $iyzicoResult['subscriptionStatus'] ?? 'ACTIVE';
            $baslangic = now();
            $bitis     = $periyot === 'aylik' ? now()->addMonth() : now()->addYear();

            $faturaSnap = is_array($kurulum['fatura'] ?? null) ? $kurulum['fatura'] : ($doktor->faturaBilgisiArray());
            $odeme = UyelikOdeme::create([
                'doktor_id'     => $doktor->id,
                'paket_id'      => $paket->id,
                'odeme_yontemi' => 'iyzico',
                'provider'      => 'iyzico',
                'odeme_periyodu'=> $periyot,
                'tutar'         => $tutar,
                'durum'         => 'onaylandi',
                'onaylandi_at'  => now(),
                'kurulum_verisi'=> $kurulum,
                'fatura_bilgisi'=> $faturaSnap,
                'fatura_durumu' => 'bekliyor',
                'otomatik_yenileme' => true,
            ]);

            if ($paket->klinikPaketiMi() && ! empty($kurulum['klinik_adi'])) {
                $ilModel   = Il::find($kurulum['il_id'] ?? null);
                $ilceModel = Ilce::where('il_id', $ilModel?->id)
                    ->where('ad', $kurulum['ilce_id'] ?? '')
                    ->first();

                $klinik = Klinik::create([
                    'ad'               => $kurulum['klinik_adi'],
                    'sahip_doktor_id'  => $doktor->id,
                    'paket_id'         => $paket->id,
                    'telefon'          => $kurulum['telefon'] ?? $doktor->telefon,
                    'e_posta'          => $kurulum['e_posta'] ?? $doktor->e_posta,
                    'adres'            => $kurulum['adres'] ?? '',
                    'il_id'            => $ilModel?->id,
                    'ilce_id'          => $ilceModel?->id,
                    'odeme_periyodu'   => $periyot,
                    'uyelik_baslangic' => $baslangic,
                    'uyelik_bitis'     => $bitis,
                    'max_doktor_sayisi'=> $paket->max_doktor_sayisi ?? 3,
                    'iyzico_subscription_reference_code' => $ref,
                    'iyzico_subscription_status'         => $subStatus,
                    'abonelik_yenileme_kapali' => false,
                    'aktif_mi'         => true,
                ]);

                $doktor->forceFill([
                    'tur'              => 'klinik',
                    'klinik_id'        => $klinik->id,
                    'klinik_rolu'      => 'sahip',
                    'klinik_katilma_tarihi' => now(),
                    'klinik_aktif_mi'  => true,
                    'klinik_adi'       => $kurulum['klinik_adi'],
                ])->save();
            }

            $doktor->forceFill([
                'paket_id'      => $paket->id,
                'odeme_periyodu'=> $periyot,
                'uyelik_baslangic' => $baslangic,
                'uyelik_bitis'  => $bitis,
                'iyzico_subscription_reference_code' => $ref,
                'iyzico_subscription_status'         => $subStatus,
                'abonelik_yenileme_kapali' => false,
                'abonelik_iptal_at'        => null,
                'abonelik_iptal_nedeni'    => null,
                'platformda_gorunur'       => true,
                'kayit_paket_id'           => null,
                'kayit_periyot'            => null,
            ])->save();
        });
    }

    // ─── Abonelik iptali ──────────────────────────────────────────────────────

    /**
     * Hekim aboneliğini iptal et (sağlayıcıda ve yerel).
     */
    public function cancelDoktorSubscription(Doktor $doktor, string $neden = 'hekim_istegi'): array
    {
        $ref = (string) ($doktor->iyzico_subscription_reference_code ?? '');

        if ($this->isIyzicoActive() || app(IyzicoSubscriptionService::class)->isRealSubscriptionReference($ref)) {
            $result = app(IyzicoSubscriptionService::class)->cancelSubscription($ref);
            if (($result['status'] ?? '') !== 'success') {
                return $result;
            }
        }

        $doktor->forceFill([
            'abonelik_yenileme_kapali' => true,
            'abonelik_iptal_at'        => now(),
            'abonelik_iptal_nedeni'    => $neden,
            'paytr_recurring_id'       => null,
            'iyzico_subscription_status' => 'CANCELED',
        ])->save();

        return ['status' => 'success'];
    }

    /**
     * Klinik aboneliğini iptal et.
     */
    public function cancelKlinikSubscription(Klinik $klinik, string $neden = 'klinik_istegi'): array
    {
        $ref = (string) ($klinik->iyzico_subscription_reference_code ?? '');

        if ($this->isIyzicoActive() || app(IyzicoSubscriptionService::class)->isRealSubscriptionReference($ref)) {
            $result = app(IyzicoSubscriptionService::class)->cancelSubscription($ref);
            if (($result['status'] ?? '') !== 'success') {
                return $result;
            }
        }

        $klinik->forceFill([
            'abonelik_yenileme_kapali' => true,
            'abonelik_iptal_at'        => now(),
            'abonelik_iptal_nedeni'    => $neden,
            'paytr_recurring_id'       => null,
            'iyzico_subscription_status' => 'CANCELED',
        ])->save();

        return ['status' => 'success'];
    }
}
