<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\IyzicoSubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Hekim abonelik / paket yönetimi — iptal (dönem sonuna kadar erişim, iyzico yenileme kapalı).
 */
class HekimUyelikController extends Controller
{
    public function index()
    {
        $doktor = Auth::guard('doktor')->user();
        $doktor->load(['paket', 'klinik.paket']);

        $klinik = $doktor->klinikSahibiMi() ? $doktor->klinik : null;

        return view('hekim.uyelik', [
            'doktor' => $doktor,
            'paket' => $doktor->aktifPaket(),
            'klinik' => $klinik,
        ]);
    }

    /**
     * Aboneliği iptal et.
     * Kartlı (gerçek iyzico ref): önce iyzico cancel BAŞARILI olmalı, sonra yerel bayrak.
     * Trial/havale: sadece yerel bayrak (çekim yok).
     */
    public function iptal(Request $request, IyzicoSubscriptionService $iyzico)
    {
        $doktor = Auth::guard('doktor')->user();

        $request->validate([
            'onay' => ['accepted'],
            'neden' => ['nullable', 'string', 'max:255'],
            'hedef' => ['nullable', 'in:bireysel,klinik'],
        ], [
            'onay.accepted' => 'İptal için onay kutusunu işaretleyin.',
        ]);

        $hedef = $request->input('hedef', 'bireysel');

        if ($hedef === 'klinik' || ($doktor->klinikSahibiMi() && $doktor->klinik && $request->boolean('klinik'))) {
            return $this->iptalKlinik($request, $doktor, $iyzico);
        }

        if ($doktor->klinikteMi() && ! $doktor->klinikSahibiMi()) {
            return back()->with('hata', 'Klinik aboneliğini yalnızca klinik sahibi yönetebilir.');
        }

        if (! $doktor->canCancelSubscription()) {
            return back()->with(
                'hata',
                $doktor->abonelik_yenileme_kapali
                    ? 'Aboneliğiniz zaten iptal sürecinde. Erişim '.$doktor->uyelik_bitis?->format('d.m.Y').' tarihine kadar sürer.'
                    : 'İptal edilecek aktif abonelik bulunamadı.'
            );
        }

        $ref = (string) ($doktor->iyzico_subscription_reference_code ?? '');
        $isReal = $iyzico->isRealSubscriptionReference($ref);
        $paidPeriod = in_array($doktor->odeme_periyodu, ['aylik', 'yillik'], true);

        // Kartlı abonelikte ref yoksa tehlikeli — iyzico panelde kalmış olabilir
        if ($paidPeriod && ! $isReal && $iyzico->isConfigured()) {
            return back()->with(
                'hata',
                'Kartlı abonelik referansı bulunamadı. iyzico tarafında yenileme devam edebilir. '
                .'Lütfen destek ile iletişime geçin (info@randevuajandam.com) veya iyzico merchant panelinden aboneliği iptal edin.'
            );
        }

        $cancelResult = $iyzico->cancelSubscription($ref);

        // GERÇEK iyzico aboneliğinde cancel başarısızsa yerel iptal YAZMA
        if ($isReal && ($cancelResult['status'] ?? '') !== 'success') {
            Log::error('BLOCKED local cancel — iyzico cancel failed', [
                'doktor_id' => $doktor->id,
                'ref' => $ref,
                'result' => $cancelResult,
            ]);

            return back()->with(
                'hata',
                ($cancelResult['errorMessage'] ?? 'iyzico abonelik iptali başarısız.')
                .' Abonelik sitede iptal edilmedi; yenileme devam edebilir. Lütfen tekrar deneyin veya destek alın.'
            );
        }

        $doktor->forceFill([
            'abonelik_yenileme_kapali' => true,
            'abonelik_iptal_at' => now(),
            'abonelik_iptal_nedeni' => $request->input('neden'),
            'iyzico_subscription_status' => 'CANCELED',
        ])->save();

        $bitis = $doktor->uyelik_bitis?->format('d.m.Y H:i') ?? 'dönem sonu';
        $iyzicoNote = ! empty($cancelResult['iyzico_canceled'])
            ? ' iyzico otomatik yenileme kapatıldı; dönem sonunda karttan yeni çekim yapılmaz.'
            : ' (Yerel deneme/havale — kartlı yenileme kaydı yok.)';

        return redirect()
            ->route('hekim.uyelik')
            ->with(
                'basarili',
                "Aboneliğiniz iptal edildi.{$iyzicoNote} "
                ."Mevcut paketinizi {$bitis} tarihine kadar kullanmaya devam edebilirsiniz."
            );
    }

    protected function iptalKlinik(Request $request, $doktor, IyzicoSubscriptionService $iyzico)
    {
        $klinik = $doktor->klinik;
        if (! $doktor->klinikSahibiMi() || ! $klinik) {
            return back()->with('hata', 'Klinik aboneliğini yalnızca sahip iptal edebilir.');
        }

        if (! $klinik->uyelik_bitis || $klinik->uyelik_bitis->isPast()) {
            return back()->with('hata', 'Aktif klinik aboneliği bulunamadı.');
        }

        if ($klinik->abonelik_yenileme_kapali ?? false) {
            return back()->with(
                'hata',
                'Klinik aboneliği zaten iptal sürecinde. Erişim '.$klinik->uyelik_bitis->format('d.m.Y').' tarihine kadar sürer.'
            );
        }

        $ref = (string) (
            $klinik->iyzico_subscription_reference_code
            ?: $doktor->iyzico_subscription_reference_code
            ?: ''
        );
        $isReal = $iyzico->isRealSubscriptionReference($ref);
        $paidPeriod = in_array($klinik->odeme_periyodu ?? $doktor->odeme_periyodu, ['aylik', 'yillik'], true);

        if ($paidPeriod && ! $isReal && $iyzico->isConfigured()) {
            return back()->with(
                'hata',
                'Klinik kartlı abonelik referansı bulunamadı. iyzico’da yenileme devam edebilir. Destek ile iletişime geçin.'
            );
        }

        $cancelResult = $iyzico->cancelSubscription($ref);

        if ($isReal && ($cancelResult['status'] ?? '') !== 'success') {
            Log::error('BLOCKED clinic local cancel — iyzico failed', [
                'klinik_id' => $klinik->id,
                'ref' => $ref,
                'result' => $cancelResult,
            ]);

            return back()->with(
                'hata',
                ($cancelResult['errorMessage'] ?? 'iyzico klinik iptali başarısız.')
                .' Yerel iptal yapılmadı.'
            );
        }

        $attrs = [
            'abonelik_yenileme_kapali' => true,
            'abonelik_iptal_at' => now(),
            'abonelik_iptal_nedeni' => $request->input('neden'),
            'iyzico_subscription_status' => 'CANCELED',
        ];
        $klinik->forceFill(array_filter(
            $attrs,
            fn ($_, $k) => \Illuminate\Support\Facades\Schema::hasColumn($klinik->getTable(), $k),
            ARRAY_FILTER_USE_BOTH
        ))->save();

        $doktor->forceFill([
            'abonelik_yenileme_kapali' => true,
            'abonelik_iptal_at' => now(),
            'abonelik_iptal_nedeni' => $request->input('neden'),
            'iyzico_subscription_status' => 'CANCELED',
        ])->save();

        $bitis = $klinik->uyelik_bitis->format('d.m.Y H:i');
        $iyzicoNote = ! empty($cancelResult['iyzico_canceled'])
            ? ' iyzico yenileme kapatıldı.'
            : '';

        return redirect()
            ->route('hekim.uyelik')
            ->with(
                'basarili',
                "Klinik aboneliği iptal edildi.{$iyzicoNote} Erişim {$bitis} tarihine kadar devam eder; sonrasında yeni çekim olmaz."
            );
    }
}
