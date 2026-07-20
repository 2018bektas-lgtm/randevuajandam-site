<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\IyzicoSubscriptionService;
use App\Services\PaytrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Hekim abonelik / paket yönetimi — iptal (dönem sonuna kadar erişim).
 * PayTR tek seferlik ödemedir; iptal yerelde yenileme kapatır (otomatik çekim yok).
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
    public function iptal(Request $request, IyzicoSubscriptionService $iyzico, PaytrService $paytr)
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
            return $this->iptalKlinik($request, $doktor, $iyzico, $paytr);
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
        $isPaytr = $paytr->isPaytrReference($ref);
        $isRealIyzico = $iyzico->isRealSubscriptionReference($ref);

        // Eski iyzico aboneliği varsa API iptal dene
        if ($isRealIyzico && ! $isPaytr && $iyzico->isConfigured()) {
            $cancelResult = $iyzico->cancelSubscription($ref);
            if (($cancelResult['status'] ?? '') !== 'success') {
                Log::error('BLOCKED local cancel — iyzico cancel failed', [
                    'doktor_id' => $doktor->id,
                    'ref' => $ref,
                    'result' => $cancelResult,
                ]);

                return back()->with(
                    'hata',
                    ($cancelResult['errorMessage'] ?? 'Eski iyzico abonelik iptali başarısız.')
                    .' Lütfen destek alın veya iyzico panelinden iptal edin.'
                );
            }
        }

        $doktor->forceFill([
            'abonelik_yenileme_kapali' => true,
            'abonelik_iptal_at' => now(),
            'abonelik_iptal_nedeni' => $request->input('neden'),
            'iyzico_subscription_status' => 'CANCELED',
        ])->save();

        $bitis = $doktor->uyelik_bitis?->format('d.m.Y H:i') ?? 'dönem sonu';
        $note = $isPaytr
            ? ' PayTR tek seferlik ödeme olduğu için otomatik yenileme zaten yoktur.'
            : ' Dönem sonunda erişim sona erer.';

        return redirect()
            ->route('hekim.uyelik')
            ->with(
                'basarili',
                "Aboneliğiniz iptal edildi.{$note} "
                ."Mevcut paketinizi {$bitis} tarihine kadar kullanmaya devam edebilirsiniz."
            );
    }

    protected function iptalKlinik(Request $request, $doktor, IyzicoSubscriptionService $iyzico, ?PaytrService $paytr = null)
    {
        $paytr = $paytr ?? app(PaytrService::class);
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
        $isPaytr = $paytr->isPaytrReference($ref);
        $isRealIyzico = $iyzico->isRealSubscriptionReference($ref);

        if ($isRealIyzico && ! $isPaytr && $iyzico->isConfigured()) {
            $cancelResult = $iyzico->cancelSubscription($ref);
            if (($cancelResult['status'] ?? '') !== 'success') {
                Log::error('BLOCKED clinic local cancel — iyzico failed', [
                    'klinik_id' => $klinik->id,
                    'ref' => $ref,
                    'result' => $cancelResult,
                ]);

                return back()->with(
                    'hata',
                    ($cancelResult['errorMessage'] ?? 'Eski iyzico klinik iptali başarısız.')
                    .' Yerel iptal yapılmadı.'
                );
            }
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
        $note = $isPaytr ? ' PayTR otomatik yenileme yapmaz.' : '';

        return redirect()
            ->route('hekim.uyelik')
            ->with(
                'basarili',
                "Klinik aboneliği iptal edildi.{$note} Erişim {$bitis} tarihine kadar devam eder."
            );
    }
}
