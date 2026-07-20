<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\IyzicoSubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Hekim abonelik / paket yönetimi — iptal (dönem sonuna kadar erişim).
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
     * Aboneliği iptal et: iyzico yenilemeyi durdur + dönem sonuna kadar erişim.
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

        // Klinik sahibi → klinik aboneliği
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

        $ref = $doktor->iyzico_subscription_reference_code;
        $cancelResult = $iyzico->cancelSubscription($ref);

        if (($cancelResult['status'] ?? '') !== 'success') {
            Log::warning('Subscription cancel failed', [
                'doktor_id' => $doktor->id,
                'result' => $cancelResult,
            ]);

            $hasRealIyzico = filled($ref)
                && ! str_starts_with((string) $ref, 'sub_mock_')
                && ! str_starts_with((string) $ref, 'trial_')
                && ! str_starts_with((string) $ref, 'free_trial_');

            if ($hasRealIyzico && $iyzico->isConfigured()) {
                return back()->with(
                    'hata',
                    $cancelResult['errorMessage']
                        ?? 'Ödeme sağlayıcısında iptal başarısız. Destek ile iletişime geçin.'
                );
            }
        }

        $doktor->forceFill([
            'abonelik_yenileme_kapali' => true,
            'abonelik_iptal_at' => now(),
            'abonelik_iptal_nedeni' => $request->input('neden'),
            'iyzico_subscription_status' => $cancelResult['subscriptionStatus']
                ?? 'CANCELED',
        ])->save();

        $bitis = $doktor->uyelik_bitis?->format('d.m.Y H:i') ?? 'dönem sonu';

        return redirect()
            ->route('hekim.uyelik')
            ->with(
                'basarili',
                "Aboneliğiniz iptal edildi. Otomatik yenileme kapandı. "
                ."Mevcut paketinizi {$bitis} tarihine kadar kullanmaya devam edebilirsiniz; "
                .'bu tarihten sonra yeni çekim yapılmaz ve erişim sona erer.'
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

        $ref = $klinik->iyzico_subscription_reference_code
            ?: $doktor->iyzico_subscription_reference_code;
        $cancelResult = $iyzico->cancelSubscription($ref);

        if (($cancelResult['status'] ?? '') !== 'success') {
            $hasReal = filled($ref)
                && ! str_starts_with((string) $ref, 'sub_mock_')
                && ! str_starts_with((string) $ref, 'trial_');
            if ($hasReal && $iyzico->isConfigured()) {
                return back()->with('hata', $cancelResult['errorMessage'] ?? 'Klinik abonelik iptali başarısız.');
            }
        }

        $attrs = [
            'abonelik_yenileme_kapali' => true,
            'abonelik_iptal_at' => now(),
            'abonelik_iptal_nedeni' => $request->input('neden'),
            'iyzico_subscription_status' => $cancelResult['subscriptionStatus'] ?? 'CANCELED',
        ];
        // Kolon yoksa sessizce atla (migrate sonrası var)
        $klinik->forceFill(array_filter(
            $attrs,
            fn ($_, $k) => \Illuminate\Support\Facades\Schema::hasColumn($klinik->getTable(), $k),
            ARRAY_FILTER_USE_BOTH
        ))->save();

        // Sahip doktor bayrağı da senkron
        $doktor->forceFill([
            'abonelik_yenileme_kapali' => true,
            'abonelik_iptal_at' => now(),
            'abonelik_iptal_nedeni' => $request->input('neden'),
            'iyzico_subscription_status' => $cancelResult['subscriptionStatus'] ?? 'CANCELED',
        ])->save();

        $bitis = $klinik->uyelik_bitis->format('d.m.Y H:i');

        return redirect()
            ->route('hekim.uyelik')
            ->with(
                'basarili',
                "Klinik aboneliği iptal edildi. Yenileme kapalı. Erişim {$bitis} tarihine kadar devam eder."
            );
    }
}
