<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Doktor;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Klinik;
use App\Models\PaytrCallbackLog;
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
        $raw = $request->except(['merchant_key', 'merchant_salt']);

        if ($merchantOid === '' || $hash === '') {
            Log::warning('PayTR notify: missing fields', $request->only(['merchant_oid', 'status']));
            $this->logCallback($merchantOid, null, $status, $totalAmount, false, false, 'missing fields', $raw);

            return response('OK', 200)->header('Content-Type', 'text/plain');
        }

        $hashOk = $paytr->verifyCallbackHash($merchantOid, $status, $totalAmount, $hash);
        if (! $hashOk) {
            Log::error('PayTR notify: bad hash', ['merchant_oid' => $merchantOid]);
            $this->logCallback($merchantOid, null, $status, $totalAmount, false, false, 'bad hash', $raw);

            return response('PAYTR notification failed: bad hash', 400)->header('Content-Type', 'text/plain');
        }

        $odeme = UyelikOdeme::query()
            ->where('merchant_oid', $merchantOid)
            ->first();

        if (! $odeme) {
            Log::warning('PayTR notify: order not found', ['merchant_oid' => $merchantOid]);
            $this->logCallback($merchantOid, null, $status, $totalAmount, true, false, 'order not found', $raw);

            return response('OK', 200)->header('Content-Type', 'text/plain');
        }

        // Idempotent
        if ($odeme->durum === 'onaylandi') {
            $this->logCallback($merchantOid, $odeme->id, $status, $totalAmount, true, true, 'already approved', $raw);

            return response('OK', 200)->header('Content-Type', 'text/plain');
        }

        if ($status === 'success') {
            try {
                $this->activateMembership($odeme, $paytr);
                $this->logCallback($merchantOid, $odeme->id, $status, $totalAmount, true, true, null, $raw);
            } catch (\Throwable $e) {
                Log::error('PayTR activate failed', [
                    'merchant_oid' => $merchantOid,
                    'message' => $e->getMessage(),
                ]);
                $this->logCallback($merchantOid, $odeme->id, $status, $totalAmount, true, false, $e->getMessage(), $raw);

                // PayTR tekrar denesin
                return response('FAIL', 500)->header('Content-Type', 'text/plain');
            }
        } else {
            $odeme->update([
                'durum' => 'reddedildi',
                'callback_payload' => $raw,
            ]);
            $this->logCallback($merchantOid, $odeme->id, $status, $totalAmount, true, true, 'payment failed', $raw);
            Log::info('PayTR payment failed', ['merchant_oid' => $merchantOid, 'status' => $status]);
        }

        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Müşteri başarı sayfası (sipariş onayı burada YAPILMAZ).
     */
    public function ok()
    {
        $doktor = auth('doktor')->user();
        if ($doktor) {
            $doktor->refresh();
            $paket = $doktor->paket;

            return view('frontend.odeme.sonuc', [
                'basarili' => true,
                'mesaj' => $doktor->uyelik_bitis
                    ? 'Ödemeniz alındı ve üyeliğiniz aktif. Panele geçerek kullanmaya başlayabilirsiniz.'
                    : 'Ödeme alındı. Üyeliğiniz birkaç saniye içinde aktifleşecektir; sayfayı yenileyebilirsiniz.',
                'paketAd' => $paket?->ad,
                'periyotLabel' => $doktor->odeme_periyodu === 'yillik' ? 'Yıllık' : ($doktor->odeme_periyodu === 'aylik' ? 'Aylık' : null),
                'bitis' => $doktor->uyelik_bitis?->format('d.m.Y'),
            ]);
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
            return view('frontend.odeme.sonuc', [
                'basarili' => false,
                'mesaj' => 'Ödeme tamamlanamadı veya iptal edildi. Tekrar deneyebilirsiniz.',
            ]);
        }

        return redirect()
            ->route('frontend.paketler')
            ->with('hata', 'Ödeme tamamlanamadı veya iptal edildi.');
    }

    protected function logCallback(
        string $merchantOid,
        ?int $odemeId,
        string $status,
        string $totalAmount,
        bool $hashOk,
        bool $processed,
        ?string $error,
        array $raw
    ): void {
        try {
            PaytrCallbackLog::create([
                'merchant_oid' => $merchantOid ?: null,
                'uyelik_odeme_id' => $odemeId,
                'status' => $status ?: null,
                'total_amount' => $totalAmount ?: null,
                'hash_ok' => $hashOk,
                'processed' => $processed,
                'error_message' => $error ? Str::limit($error, 500) : null,
                'raw_payload' => $raw,
            ]);
        } catch (\Throwable $e) {
            Log::warning('PayTR callback log yazılamadı: '.$e->getMessage());
        }
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
                    'max_doktor_sayisi' => $paket->max_doktor_sayisi ?? 3,
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
                    'klinik_aktif_mi' => true,
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
                    'platformda_gorunur' => true,
                ])->save();

                // Bireysel → klinik geçişinde hasta havuzuna taşı
                $patientIds = \App\Models\Hasta::whereHas('randevular', function ($q) use ($doktor) {
                    $q->where('doktor_id', $doktor->id);
                })->pluck('id')->all();
                if ($patientIds !== []) {
                    $sync = [];
                    foreach ($patientIds as $pid) {
                        $sync[$pid] = [
                            'kayit_tarihi' => now(),
                            'notlar' => 'Klinik paket ödemesi sonrası aktarıldı.',
                        ];
                    }
                    $klinik->hastalar()->syncWithoutDetaching($sync);
                }
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
                    'platformda_gorunur' => true,
                ])->save();
            }

            $odeme->update([
                'durum' => 'onaylandi',
                'onaylandi_at' => now(),
                'provider' => 'paytr',
                'fatura_durumu' => 'bekliyor',
            ]);

            // Kayıt niyeti tamamlandı
            $doktor->forceFill([
                'kayit_paket_id' => null,
                'kayit_periyot' => null,
            ])->save();
        });
    }
}
