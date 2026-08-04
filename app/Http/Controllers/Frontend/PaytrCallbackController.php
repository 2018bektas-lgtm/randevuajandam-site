<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Doktor;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Klinik;
use App\Models\PaytrCallbackLog;
use App\Models\UyelikOdeme;
use App\Models\KlinikEkKoltukOdeme;
use App\Models\EkUrunOdeme;
use App\Support\GarantiFiyat;
use App\Services\PaytrService;
use App\Support\MetaPixel;
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
        $isEkKoltuk = str_starts_with($merchantOid, 'EK');
        if ($isEkKoltuk) {
            $odeme = KlinikEkKoltukOdeme::query()
                ->where('merchant_oid', $merchantOid)
                ->where('doktor_id', $doktor->id)
                ->where('durum', 'beklemede')
                ->firstOrFail();
            $paket = $odeme->klinik?->paket;
        } else {
            $odeme = UyelikOdeme::query()
                ->where('merchant_oid', $merchantOid)
                ->where('doktor_id', $doktor->id)
                ->where('durum', 'beklemede')
                ->firstOrFail();
            $paket = $odeme->paket;
        }

        $token = session('paytr_iframe_token_'.$merchantOid);
        if (! $token) {
            return redirect()
                ->route('frontend.hekim.paket_sec')
                ->with('hata', 'Ödeme oturumu süresi doldu. Lütfen tekrar deneyin.');
        }
        MetaPixel::queue('AddPaymentInfo', array_merge(
            MetaPixel::money((float) $odeme->tutar),
            [
                'content_name' => $paket?->ad ?? 'Üyelik',
                'content_ids' => $paket ? [(string) $paket->id] : [],
                'content_type' => 'product',
            ]
        ));

        return view('frontend.odeme.paytr_iframe', [
            'token' => $token,
            'odeme' => $odeme,
            'merchantOid' => $merchantOid,
        ]);
    }

    /**
     * Direkt API 3D Secure formu (abonelik + ek ödemeler).
     * Session'daki ACS HTML'i gösterir; sonuç merchant_ok/fail + notify ile gelir.
     */
    public function threeDFrame(string $merchantOid)
    {
        $html = session('paytr_direct_3d_html_'.$merchantOid);
        if (! $html) {
            return redirect()
                ->route('frontend.hekim.paket_sec')
                ->with('hata', '3D oturumu süresi doldu. Lütfen ödemeyi tekrar başlatın.');
        }

        return response(
            '<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8">'
            .'<meta name="viewport" content="width=device-width,initial-scale=1">'
            .'<title>3D Secure — PayTR</title>'
            .'<style>html,body{margin:0;height:100%;background:#0f172a}iframe{border:0;width:100%;height:100%}</style>'
            .'</head><body>'
            .'<iframe id="acs" title="3D Secure" sandbox="allow-forms allow-scripts allow-same-origin allow-top-navigation allow-popups"></iframe>'
            .'<script>(function(){var h='.json_encode($html, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE).';'
            .'var d=document.getElementById("acs").contentDocument||document.getElementById("acs").contentWindow.document;'
            .'d.open();d.write(h);d.close();})();</script>'
            .'<script>window.addEventListener("message",function(e){'
            .'if(!e.data||!e.data.paytr3d)return;'
            .'if(e.data.paytr3d==="ok"){location.href='.json_encode(route('frontend.odeme.paytr.ok')).';}'
            .'if(e.data.paytr3d==="fail"){location.href='.json_encode(route('frontend.odeme.paytr.fail')).'+ (e.data.message?("?msg="+encodeURIComponent(e.data.message)):"") ;}'
            .'});</script>'
            .'</body></html>',
            200,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
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
        // Kart saklama: utoken/ctoken notify'da döner (store_card=1)
        $utoken = (string) ($request->input('utoken') ?? $request->input('u_token') ?? '');
        $ctoken = (string) ($request->input('ctoken') ?? $request->input('c_token') ?? '');
        $recurringId = (string) $request->input('recurring_id', '');
        if ($recurringId === '' && $ctoken !== '') {
            $recurringId = $ctoken; // geriye uyum: ctoken = kayıtlı kart kimliği
        }
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

        // Ek ürünler: EK hekim koltuk | EP personel | SM sms
        $isEkKoltuk = str_starts_with($merchantOid, 'EK');
        $isEkUrun = str_starts_with($merchantOid, 'EP') || str_starts_with($merchantOid, 'SM');

        $odeme = UyelikOdeme::query()
            ->where('merchant_oid', $merchantOid)
            ->first();

        if (! $odeme && $isEkUrun) {
            $ekUrun = EkUrunOdeme::where('merchant_oid', $merchantOid)->first();
            if ($ekUrun) {
                if ($ekUrun->durum === 'odendi') {
                    $this->logCallback($merchantOid, $ekUrun->id, $status, $totalAmount, true, true, 'ek_urun already approved', $raw);

                    return response('OK', 200)->header('Content-Type', 'text/plain');
                }
                if ($status === 'success') {
                    try {
                        $this->activateEkUrun($ekUrun);
                        $this->logCallback($merchantOid, $ekUrun->id, $status, $totalAmount, true, true, null, $raw);
                    } catch (\Throwable $e) {
                        Log::error('Ek ürün activate failed', ['merchant_oid' => $merchantOid, 'message' => $e->getMessage()]);
                        $this->logCallback($merchantOid, $ekUrun->id, $status, $totalAmount, true, false, $e->getMessage(), $raw);

                        return response('FAIL', 500)->header('Content-Type', 'text/plain');
                    }
                } else {
                    $ekUrun->update(['durum' => 'reddedildi', 'callback_payload' => $raw]);
                    $this->logCallback($merchantOid, $ekUrun->id, $status, $totalAmount, true, true, 'ek_urun payment failed', $raw);
                }

                return response('OK', 200)->header('Content-Type', 'text/plain');
            }
        }

        if (! $odeme && $isEkKoltuk) {
            $ekKoltukOdeme = KlinikEkKoltukOdeme::where('merchant_oid', $merchantOid)->first();
            if ($ekKoltukOdeme) {
                if ($ekKoltukOdeme->durum === 'odendi') {
                    $this->logCallback($merchantOid, $ekKoltukOdeme->id, $status, $totalAmount, true, true, 'ek_koltuk already approved', $raw);
                    return response('OK', 200)->header('Content-Type', 'text/plain');
                }

                if ($status === 'success') {
                    try {
                        $this->activateEkKoltuk($ekKoltukOdeme);
                        $this->logCallback($merchantOid, $ekKoltukOdeme->id, $status, $totalAmount, true, true, null, $raw);
                    } catch (\Throwable $e) {
                        Log::error('Ek koltuk activate failed', [
                            'merchant_oid' => $merchantOid,
                            'message' => $e->getMessage(),
                        ]);
                        $this->logCallback($merchantOid, $ekKoltukOdeme->id, $status, $totalAmount, true, false, $e->getMessage(), $raw);
                        return response('FAIL', 500)->header('Content-Type', 'text/plain');
                    }
                } else {
                    $ekKoltukOdeme->update([
                        'durum' => 'reddedildi',
                        'callback_payload' => $raw,
                    ]);
                    $this->logCallback($merchantOid, $ekKoltukOdeme->id, $status, $totalAmount, true, true, 'ek_koltuk payment failed', $raw);
                }

                return response('OK', 200)->header('Content-Type', 'text/plain');
            }
        }

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
                $this->activateMembership($odeme, $paytr, $recurringId, $utoken, $ctoken);
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

            // Son ödeme kaydı (onaylı veya bekleyen) — Purchase/Subscribe değerleri
            $sonOdeme = UyelikOdeme::query()
                ->with('paket')
                ->where('doktor_id', $doktor->id)
                ->whereIn('durum', ['onaylandi', 'beklemede'])
                ->where('odeme_yontemi', 'paytr')
                ->latest('id')
                ->first();

            if ($sonOdeme) {
                $value = (float) $sonOdeme->tutar;
                $contentName = $sonOdeme->paket?->ad ?? $paket?->ad ?? 'Üyelik';
                $contentId = (string) ($sonOdeme->paket_id ?? $paket?->id ?? 'membership');
                $purchaseParams = array_merge(
                    MetaPixel::money($value),
                    [
                        'content_name' => $contentName,
                        'content_ids' => [$contentId],
                        'content_type' => 'product',
                        'num_items' => 1,
                    ]
                );

                MetaPixel::queueOnce(
                    'purchase_'.$sonOdeme->merchant_oid.'_'.$sonOdeme->id,
                    'Purchase',
                    $purchaseParams
                );
                MetaPixel::queueOnce(
                    'subscribe_'.$sonOdeme->merchant_oid.'_'.$sonOdeme->id,
                    'Subscribe',
                    array_merge(
                        MetaPixel::money($value),
                        [
                            'content_name' => $contentName,
                            'content_ids' => [$contentId],
                            'predicted_ltv' => $value,
                        ]
                    )
                );
            }

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

    protected function activateMembership(
        UyelikOdeme $odeme,
        PaytrService $paytr,
        string $recurringId = '',
        string $utoken = '',
        string $ctoken = ''
    ): void {
        DB::transaction(function () use ($odeme, $paytr, $recurringId, $utoken, $ctoken) {
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

            $tokenFill = array_filter([
                'paytr_utoken' => $utoken !== '' ? $utoken : null,
                'paytr_ctoken' => $ctoken !== '' ? $ctoken : null,
                'paytr_recurring_id' => $recurringId !== '' ? $recurringId : ($ctoken !== '' ? $ctoken : null),
            ], fn ($v) => $v !== null);

            if ($paket->klinikPaketiMi() && ! empty($kurulum['klinik_adi'])) {
                $ilModel = Il::find($kurulum['il_id'] ?? null);
                $ilceModel = Ilce::where('il_id', $ilModel?->id)
                    ->where('ad', $kurulum['ilce_id'] ?? '')
                    ->first();

                $klinik = Klinik::create(array_merge([
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
                ], $tokenFill));

                $doktor->forceFill(array_merge([
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
                ], $tokenFill))->save();

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
                // Mevcut klinik sahibi yenilemesi: klinik token'larını da güncelle
                if ($doktor->klinik_id && $doktor->klinik_rolu === 'sahip' && $tokenFill !== []) {
                    Klinik::where('id', $doktor->klinik_id)->update($tokenFill);
                }

                $doktor->forceFill(array_merge([
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
                ], $tokenFill))->save();
            }

            $odemeFill = array_filter([
                'paytr_recurring_id' => $recurringId !== '' ? $recurringId : null,
                'paytr_utoken' => $utoken !== '' ? $utoken : null,
                'paytr_ctoken' => $ctoken !== '' ? $ctoken : null,
            ]);

            $hasCardTokens = $utoken !== '' && $ctoken !== '';
            $odeme->forceFill(array_merge($odemeFill, [
                'durum' => 'onaylandi',
                'onaylandi_at' => now(),
                'provider' => 'paytr',
                'fatura_durumu' => 'bekliyor',
                // Kayıtlı kart varsa dönem sonu 3D'siz yenileme hedeflenir
                'otomatik_yenileme' => $hasCardTokens && (bool) config('services.paytr.recurring_enabled', true),
            ]))->save();

            // Excel: 1 dönem fiyat garantisi kilidi
            try {
                GarantiFiyat::kilitle($doktor->fresh(), $paket, (string) $odeme->odeme_periyodu, $bitis);
                if ($paket->klinikPaketiMi() && $doktor->fresh()->klinik_id) {
                    $k = Klinik::find($doktor->fresh()->klinik_id);
                    if ($k) {
                        GarantiFiyat::kilitle($k, $paket, (string) $odeme->odeme_periyodu, $bitis);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Garanti fiyat kilidi: '.$e->getMessage());
            }

            // Hatırlatma sayaçlarını yeni dönem için sıfırla
            $doktor->forceFill([
                'kayit_paket_id' => null,
                'kayit_periyot' => null,
                'abonelik_yenileme_kapali' => false,
                'abonelik_iptal_at' => null,
                'abonelik_iptal_nedeni' => null,
                'uyelik_hatirlat_7_at' => null,
                'uyelik_hatirlat_3_at' => null,
                'uyelik_hatirlat_1_at' => null,
            ])->save();

            if (! $hasCardTokens) {
                Log::info('PayTR ödeme onaylandı ancak utoken/ctoken yok — otomatik yenileme yapılamaz', [
                    'merchant_oid' => $odeme->merchant_oid,
                    'doktor_id' => $doktor->id,
                ]);
            }
        });

        // Referans ödülü (transaction dışında idempotent)
        try {
            $odeme->refresh();
            app(\App\Services\ReferansService::class)->odullendir($odeme);
        } catch (\Throwable $e) {
            Log::warning('PayTR referans ödül hatası: '.$e->getMessage());
        }
    }

    protected function activateEkKoltuk(KlinikEkKoltukOdeme $odeme): void
    {
        DB::transaction(function () use ($odeme) {
            $odeme->refresh();
            if ($odeme->durum === 'odendi') {
                return;
            }

            $klinik = Klinik::query()->lockForUpdate()->find($odeme->klinik_id);
            if (! $klinik) {
                throw new \RuntimeException('Klinik bulunamadı');
            }

            // Ek koltuk sayısını artır
            $klinik->increment('ek_doktor_koltuk_sayisi', $odeme->adet);

            // max_doktor_sayisi senkronize et
            $klinik->refresh();
            $klinik->syncMaxDoktorSayisi();

            $odeme->update([
                'durum' => 'odendi',
                'onaylandi_at' => now(),
                'callback_payload' => request()->except(['merchant_key', 'merchant_salt']),
            ]);
        });
    }

    protected function activateEkUrun(EkUrunOdeme $odeme): void
    {
        DB::transaction(function () use ($odeme) {
            $odeme->refresh();
            if ($odeme->durum === 'odendi') {
                return;
            }

            if ($odeme->tip === 'sms_kontor') {
                if ($odeme->klinik_id) {
                    $klinik = Klinik::query()->lockForUpdate()->find($odeme->klinik_id);
                    if ($klinik) {
                        app(\App\Services\SmsKontorService::class)->ekKontorEkle($klinik, (int) $odeme->adet);
                    }
                } elseif ($odeme->doktor_id) {
                    $doktor = Doktor::query()->lockForUpdate()->find($odeme->doktor_id);
                    if ($doktor) {
                        app(\App\Services\SmsKontorService::class)->ekKontorEkle($doktor, (int) $odeme->adet);
                    }
                }
            } elseif ($odeme->tip === 'personel_koltuk' && $odeme->klinik_id) {
                $klinik = Klinik::query()->lockForUpdate()->find($odeme->klinik_id);
                if ($klinik) {
                    $klinik->increment('ek_personel_koltuk_sayisi', (int) $odeme->adet);
                }
            }

            $odeme->update([
                'durum' => 'odendi',
                'onaylandi_at' => now(),
                'callback_payload' => request()->except(['merchant_key', 'merchant_salt']),
            ]);
        });
    }
}
