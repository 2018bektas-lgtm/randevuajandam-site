<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Doktor;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Klinik;
use App\Models\UyelikOdeme;
use App\Services\PaytrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaytrCallbackController extends Controller
{
    /**
     * Ödeme iframe sayfası (hekim oturumu).
     */
    public function iframe(string $merchantOid)
    {
        $doktor = auth('doktor')->user();
        $odeme = UyelikOdeme::query()
            ->where('merchant_oid', $merchantOid)
            ->where('doktor_id', $doktor->id)
            ->where('durum', 'beklemede')
            ->firstOrFail();

        $token = session('paytr_iframe_token_'.$merchantOid);
        if (! $token) {
            return redirect()
                ->route('frontend.hekim.paket_sec')
                ->with('hata', 'Ödeme oturumu süresi doldu. Lütfen tekrar deneyin.');
        }

        return view('frontend.odeme.paytr_iframe', [
            'token' => $token,
            'odeme' => $odeme,
            'merchantOid' => $merchantOid,
        ]);
    }

    /**
     * PayTR bildirim URL (server-to-server). CSRF kapalı. Yanıt: OK
     */
    public function notify(Request $request, PaytrService $paytr)
    {
        $merchantOid = (string) $request->input('merchant_oid', '');
        $status = (string) $request->input('status', '');
        $totalAmount = (string) $request->input('total_amount', '');
        $hash = (string) $request->input('hash', '');

        if ($merchantOid === '' || $hash === '') {
            Log::warning('PayTR notify: missing fields', $request->only(['merchant_oid', 'status']));

            return response('OK', 200)->header('Content-Type', 'text/plain');
        }

        if (! $paytr->verifyCallbackHash($merchantOid, $status, $totalAmount, $hash)) {
            Log::error('PayTR notify: bad hash', ['merchant_oid' => $merchantOid]);

            return response('PAYTR notification failed: bad hash', 400)->header('Content-Type', 'text/plain');
        }

        $odeme = UyelikOdeme::query()
            ->where('merchant_oid', $merchantOid)
            ->first();

        if (! $odeme) {
            Log::warning('PayTR notify: order not found', ['merchant_oid' => $merchantOid]);

            return response('OK', 200)->header('Content-Type', 'text/plain');
        }

        // Idempotent
        if ($odeme->durum === 'onaylandi') {
            return response('OK', 200)->header('Content-Type', 'text/plain');
        }

        if ($status === 'success') {
            try {
                $this->activateMembership($odeme, $paytr);
            } catch (\Throwable $e) {
                Log::error('PayTR activate failed', [
                    'merchant_oid' => $merchantOid,
                    'message' => $e->getMessage(),
                ]);

                // PayTR tekrar denesin
                return response('FAIL', 500)->header('Content-Type', 'text/plain');
            }
        } else {
            $odeme->update(['durum' => 'reddedildi']);
            Log::info('PayTR payment failed', ['merchant_oid' => $merchantOid, 'status' => $status]);
        }

        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Müşteri başarı sayfası (sipariş onayı burada YAPILMAZ).
     */
    public function ok()
    {
        if (auth('doktor')->check()) {
            return redirect()
                ->route('frontend.hekim.paket_sec')
                ->with('basarili', 'Ödeme alındı. Üyeliğiniz birkaç saniye içinde aktifleşecektir. Sayfayı yenileyebilirsiniz.');
        }

        return redirect()
            ->route('frontend.paketler')
            ->with('basarili', 'Ödeme işleminiz alındı. Onay sonrası hesabınız aktifleşir.');
    }

    /**
     * Müşteri hata sayfası.
     */
    public function fail()
    {
        if (auth('doktor')->check()) {
            return redirect()
                ->route('frontend.hekim.paket_sec')
                ->with('hata', 'Ödeme tamamlanamadı veya iptal edildi. Tekrar deneyebilirsiniz.');
        }

        return redirect()
            ->route('frontend.paketler')
            ->with('hata', 'Ödeme tamamlanamadı veya iptal edildi.');
    }

    protected function activateMembership(UyelikOdeme $odeme, PaytrService $paytr): void
    {
        DB::transaction(function () use ($odeme, $paytr) {
            $odeme->refresh();
            if ($odeme->durum === 'onaylandi') {
                return;
            }

            $doktor = Doktor::query()->lockForUpdate()->find($odeme->doktor_id);
            $paket = $odeme->paket;
            if (! $doktor || ! $paket) {
                throw new \RuntimeException('Doktor veya paket bulunamadı');
            }

            $baslangic = now();
            $bitis = $odeme->odeme_periyodu === 'aylik' ? now()->addMonth() : now()->addYear();
            $ref = $paytr->referenceCodeFromOid((string) $odeme->merchant_oid);

            $kurulum = $odeme->kurulum_verisi ?? [];

            if ($paket->klinikPaketiMi() && ! empty($kurulum['klinik_adi'])) {
                $ilModel = Il::find($kurulum['il_id'] ?? null);
                $ilceModel = Ilce::where('il_id', $ilModel?->id)
                    ->where('ad', $kurulum['ilce_id'] ?? '')
                    ->first();

                $klinik = Klinik::create([
                    'ad' => $kurulum['klinik_adi'],
                    'sahip_doktor_id' => $doktor->id,
                    'paket_id' => $paket->id,
                    'telefon' => $kurulum['telefon'] ?? $doktor->telefon,
                    'e_posta' => $kurulum['e_posta'] ?? $doktor->e_posta,
                    'adres' => $kurulum['adres'] ?? '',
                    'il_id' => $ilModel?->id,
                    'ilce_id' => $ilceModel?->id,
                    'odeme_periyodu' => $odeme->odeme_periyodu,
                    'uyelik_baslangic' => $baslangic,
                    'uyelik_bitis' => $bitis,
                    'iyzico_subscription_reference_code' => $ref,
                    'iyzico_subscription_status' => 'ACTIVE',
                    'abonelik_yenileme_kapali' => false,
                    'aktif_mi' => true,
                ]);

                $doktor->forceFill([
                    'tur' => 'klinik',
                    'klinik_id' => $klinik->id,
                    'klinik_rolu' => 'sahip',
                    'klinik_katilma_tarihi' => now(),
                    'klinik_adi' => $kurulum['klinik_adi'],
                    'paket_id' => $paket->id,
                    'odeme_periyodu' => $odeme->odeme_periyodu,
                    'uyelik_baslangic' => $baslangic,
                    'uyelik_bitis' => $bitis,
                    'iyzico_subscription_reference_code' => $ref,
                    'iyzico_subscription_status' => 'ACTIVE',
                    'abonelik_yenileme_kapali' => false,
                    'abonelik_iptal_at' => null,
                    'abonelik_iptal_nedeni' => null,
                ])->save();
            } else {
                $doktor->forceFill([
                    'paket_id' => $paket->id,
                    'odeme_periyodu' => $odeme->odeme_periyodu,
                    'uyelik_baslangic' => $baslangic,
                    'uyelik_bitis' => $bitis,
                    'iyzico_subscription_reference_code' => $ref,
                    'iyzico_subscription_status' => 'ACTIVE',
                    'abonelik_yenileme_kapali' => false,
                    'abonelik_iptal_at' => null,
                    'abonelik_iptal_nedeni' => null,
                ])->save();
            }

            $odeme->update([
                'durum' => 'onaylandi',
                'onaylandi_at' => now(),
                'provider' => 'paytr',
            ]);
        });
    }
}
