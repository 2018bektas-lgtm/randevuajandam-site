<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Doktor;
use App\Models\Paket;
use App\Models\UyelikOdeme;
use App\Services\PaytrService;
use App\Services\ReferansService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * PayTR Direkt API:
 * - Abonelik ilk ödeme: 3D (non_3d=0) + store_card=1 → utoken/ctoken
 * - Ek ödemeler (SMS/koltuk): 3D (non_3d=0), kart saklama opsiyonel
 * - Tekrarlayan abonelik: AbonelikYenileCommand → Non3D + recurring_payment
 *
 * @see https://dev.paytr.com/direkt-api/direkt-api-1-adim
 * @see https://dev.paytr.com/direkt-api/kart-saklama-api/yeni-kart-ekleme
 * @see https://dev.paytr.com/direkt-api/kart-saklama-api/kayitli-kart-tekrarlayan-odeme
 */
class PaytrDirectController extends Controller
{
    public function charge(Request $request)
    {
        /** @var Doktor|null $doktor */
        $doktor = Auth::guard('doktor')->user();
        if (! $doktor || ! $doktor->canProceedToPayment()) {
            return response()->json(['error' => 'Ödeme yapılamaz.'], 403);
        }

        $paket = Paket::where('aktif_mi', true)->findOrFail($request->input('paket_id'));

        $periyot = $request->input('odeme_periyodu', 'aylik');
        $periodPrice = $periyot === 'yillik' ? (float) $paket->yillik_fiyat : (float) $paket->aylik_fiyat;
        $discountedPrice = $periyot === 'yillik' ? $paket->yillik_indirimli_fiyat : $paket->aylik_indirimli_fiyat;
        $tutar = $discountedPrice !== null && (float) $discountedPrice > 0 ? (float) $discountedPrice : $periodPrice;
        $refFiyat = app(ReferansService::class)->indirimliTutar($doktor, $tutar);
        $tutarBrut = $refFiyat['brut'];
        $tutar = $refFiyat['tutar'];

        $rules = [
            'paket_id' => 'required|exists:paketler,id',
            'odeme_periyodu' => 'required|in:aylik,yillik',
            'mesafeli_onay' => 'accepted',
            'kvkk_odeme_onay' => 'accepted',
            'kart_sahibi' => 'required|string|max:100',
            'kart_no' => 'required|string|min:15|max:19',
            'kart_ay' => 'required|string|min:1|max:2',
            'kart_yil' => 'required|string|min:2|max:4',
            'kart_cvv' => 'required|string|min:3|max:4',
            'store_card' => 'nullable|boolean',
        ];

        if ($paket->klinikPaketiMi()) {
            $rules['klinik_adi'] = 'required|string|max:255';
            $rules['telefon'] = 'required|string';
            $rules['e_posta'] = 'nullable|email|max:255';
            $rules['adres'] = 'required|string';
            $rules['il_id'] = 'required|exists:iller,id';
            $rules['ilce_id'] = 'required|string|max:255';
        }

        $request->validate($rules, [
            'mesafeli_onay.accepted' => 'Mesafeli satış sözleşmesini kabul etmelisiniz.',
            'kvkk_odeme_onay.accepted' => 'KVKK aydınlatma metnini kabul etmelisiniz.',
            'kart_sahibi.required' => 'Kart sahibinin adını girin.',
            'kart_no.required' => 'Kart numarasını girin.',
            'kart_ay.required' => 'Son kullanma ayını girin.',
            'kart_yil.required' => 'Son kullanma yılını girin.',
            'kart_cvv.required' => 'CVV kodunu girin.',
        ]);

        $paytr = app(PaytrService::class);
        if (! $paytr->isConfigured()) {
            return response()->json(['error' => 'Kartlı ödeme şu anda kullanıma açık değil.'], 422);
        }

        $merchantOid = $paytr->makeMerchantOid();
        $kurulum = $paket->klinikPaketiMi()
            ? $request->only(['klinik_adi', 'telefon', 'e_posta', 'adres', 'il_id', 'ilce_id'])
            : [];
        $kurulum['tutar_brut'] = $tutarBrut;
        $kurulum['referans_indirim_yuzde'] = $refFiyat['indirim_yuzde'];
        $storeCard = $request->boolean('store_card', true);

        UyelikOdeme::create([
            'doktor_id' => $doktor->id,
            'paket_id' => $paket->id,
            'odeme_yontemi' => 'paytr',
            'provider' => 'paytr',
            'odeme_periyodu' => $periyot,
            'tutar' => $tutar,
            'durum' => 'beklemede',
            'merchant_oid' => $merchantOid,
            'kurulum_verisi' => $kurulum ?: null,
            'otomatik_yenileme' => $storeCard,
        ]);

        $result = $paytr->createDirectPayment([
            'merchant_oid' => $merchantOid,
            'email' => $doktor->e_posta,
            'payment_amount' => $tutar,
            'user_name' => $doktor->ad_soyad,
            'user_address' => $doktor->adres ?: ($doktor->il?->ad ?? 'Turkiye'),
            'user_phone' => $doktor->telefon,
            'user_ip' => $request->ip(),
            'basket_name' => 'Randevu Ajandam - '.$paket->ad.' ('.$periyot.')',
            'card_owner' => $request->input('kart_sahibi'),
            'card_number' => $request->input('kart_no'),
            'expiry_month' => $request->input('kart_ay'),
            'expiry_year' => $request->input('kart_yil'),
            'card_cvv' => $request->input('kart_cvv'),
            'store_card' => $storeCard,
            'utoken' => (string) ($doktor->paytr_utoken ?? ''),
            'non_3d' => '0', // İlk ödeme: 3D Secure
            'merchant_ok_url' => route('frontend.odeme.paytr.3d.ok'),
            'merchant_fail_url' => route('frontend.odeme.paytr.3d.fail'),
        ]);

        if (($result['status'] ?? '') === '3d') {
            return response()->json(['html' => $result['html'], 'merchant_oid' => $merchantOid]);
        }

        if (in_array($result['status'] ?? '', ['success', 'wait_callback'], true)) {
            // Token JSON yanıtta gelmiş olabilir (Non3D); 3D'de genelde notify'da gelir
            $this->persistTokensIfAny($doktor, $result);

            return response()->json(['redirect' => route('frontend.odeme.paytr.ok')]);
        }

        UyelikOdeme::where('merchant_oid', $merchantOid)->update(['durum' => 'reddedildi']);

        return response()->json([
            'error' => $result['errorMessage'] ?? 'Ödeme başlatılamadı. Kart bilgilerinizi kontrol edin.',
        ], 422);
    }

    /**
     * Ek ürün 3D ödemesi (SMS kontör, ek personel, ek hekim koltuğu).
     * non_3d=0; abonelik değil — store_card varsayılan kapalı.
     */
    public function chargeEk(Request $request)
    {
        /** @var Doktor|null $doktor */
        $doktor = Auth::guard('doktor')->user();
        if (! $doktor) {
            return response()->json(['error' => 'Oturum gerekli.'], 403);
        }

        $request->validate([
            'merchant_oid' => 'required|string|max:64',
            'kart_sahibi' => 'required|string|max:100',
            'kart_no' => 'required|string|min:15|max:19',
            'kart_ay' => 'required|string|min:1|max:2',
            'kart_yil' => 'required|string|min:2|max:4',
            'kart_cvv' => 'required|string|min:3|max:4',
        ]);

        $merchantOid = (string) $request->input('merchant_oid');
        $paytr = app(PaytrService::class);
        if (! $paytr->isConfigured()) {
            return response()->json(['error' => 'PayTR yapılandırılmamış.'], 422);
        }

        $tutar = 0.0;
        $basket = 'Ek odeme';
        $userEmail = $doktor->e_posta;
        $userName = $doktor->ad_soyad;
        $userPhone = $doktor->telefon;
        $userAddress = $doktor->adres ?: 'Turkiye';

        if (str_starts_with($merchantOid, 'SM') || str_starts_with($merchantOid, 'EP')) {
            $odeme = \App\Models\EkUrunOdeme::where('merchant_oid', $merchantOid)
                ->where('doktor_id', $doktor->id)
                ->where('durum', 'beklemede')
                ->first();
            if (! $odeme) {
                return response()->json(['error' => 'Ödeme kaydı bulunamadı.'], 404);
            }
            $tutar = (float) $odeme->tutar;
            $basket = $odeme->tip === 'sms_kontor'
                ? 'SMS Kontor x'.$odeme->adet
                : 'Ek Personel Koltugu x'.$odeme->adet;
        } elseif (str_starts_with($merchantOid, 'EK')) {
            $odeme = \App\Models\KlinikEkKoltukOdeme::where('merchant_oid', $merchantOid)
                ->where('doktor_id', $doktor->id)
                ->where('durum', 'beklemede')
                ->first();
            if (! $odeme) {
                return response()->json(['error' => 'Ödeme kaydı bulunamadı.'], 404);
            }
            $tutar = (float) $odeme->tutar;
            $basket = 'Ek Hekim Koltugu x'.$odeme->adet;
            $userAddress = $odeme->klinik?->adres ?: $userAddress;
        } else {
            return response()->json(['error' => 'Geçersiz ek ödeme siparişi.'], 422);
        }

        $result = $paytr->createDirectPayment([
            'merchant_oid' => $merchantOid,
            'email' => $userEmail,
            'payment_amount' => $tutar,
            'user_name' => $userName,
            'user_address' => $userAddress,
            'user_phone' => $userPhone,
            'user_ip' => $request->ip(),
            'basket_name' => $basket,
            'card_owner' => $request->input('kart_sahibi'),
            'card_number' => $request->input('kart_no'),
            'expiry_month' => $request->input('kart_ay'),
            'expiry_year' => $request->input('kart_yil'),
            'card_cvv' => $request->input('kart_cvv'),
            'store_card' => false, // ek ödeme: abonelik kartı saklama yok
            'non_3d' => '0', // 3D zorunlu
            'merchant_ok_url' => route('frontend.odeme.paytr.3d.ok'),
            'merchant_fail_url' => route('frontend.odeme.paytr.3d.fail'),
        ]);

        if (($result['status'] ?? '') === '3d') {
            session([
                'paytr_direct_3d_html_'.$merchantOid => $result['html'],
                'paytr_direct_3d_oid' => $merchantOid,
            ]);

            return response()->json([
                'redirect' => route('frontend.odeme.paytr.3d.frame', ['merchantOid' => $merchantOid]),
            ]);
        }

        if (in_array($result['status'] ?? '', ['success', 'wait_callback'], true)) {
            return response()->json(['redirect' => route('frontend.odeme.paytr.ok')]);
        }

        return response()->json([
            'error' => $result['errorMessage'] ?? '3D ödeme başlatılamadı.',
        ], 422);
    }

    public function threeDOk(Request $request)
    {
        return response(
            '<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>'
            .'<body style="font-family:sans-serif;text-align:center;padding:40px;background:#f0fdf4">'
            .'<div style="max-width:280px;margin:0 auto">'
            .'<div style="font-size:52px;color:#16a34a">&#10003;</div>'
            .'<p style="color:#15803d;font-weight:bold;font-size:14px;margin:8px 0">Ödeme onaylandı</p>'
            .'<p style="color:#6b7280;font-size:12px">Lütfen bekleyin...</p>'
            .'</div>'
            .'<script>try{window.parent.postMessage({paytr3d:"ok"},"*")}catch(e){}'
            .'try{window.top.postMessage({paytr3d:"ok"},"*")}catch(e){}</script>'
            .'</body></html>',
            200,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }

    public function threeDFail(Request $request)
    {
        // PayTR fail_message ile yönlendirir (@see Direkt API 1. adım)
        $failMessage = (string) (
            $request->input('fail_message')
            ?: $request->input('failed_reason_msg')
            ?: $request->input('failed_reason_code')
            ?: ''
        );
        $failMessage = trim(strip_tags($failMessage));
        if (mb_strlen($failMessage) > 300) {
            $failMessage = mb_substr($failMessage, 0, 300).'…';
        }

        Log::warning('PayTR 3D fail', [
            'fail_message' => $failMessage !== '' ? $failMessage : null,
            'merchant_oid' => $request->input('merchant_oid'),
            'all' => $request->except(['card_number', 'cvv', 'cc_owner']),
        ]);

        if ($request->filled('merchant_oid')) {
            try {
                UyelikOdeme::where('merchant_oid', (string) $request->input('merchant_oid'))
                    ->where('durum', 'beklemede')
                    ->update(['durum' => 'reddedildi']);
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $userMsg = $failMessage !== ''
            ? $failMessage
            : '3D doğrulama başarısız. Kart bilgilerinizi veya banka SMS onayını kontrol edin.';
        $jsMsg = json_encode($userMsg, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);

        return response(
            '<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>'
            .'<body style="font-family:sans-serif;text-align:center;padding:40px;background:#fef2f2">'
            .'<div style="max-width:320px;margin:0 auto">'
            .'<div style="font-size:52px;color:#dc2626">&#10007;</div>'
            .'<p style="color:#b91c1c;font-weight:bold;font-size:14px;margin:8px 0">3D doğrulama başarısız</p>'
            .'<p style="color:#6b7280;font-size:12px;line-height:1.5">'.e($userMsg).'</p>'
            .'</div>'
            .'<script>(function(){var m={paytr3d:"fail",message:'.$jsMsg.'};'
            .'try{window.parent.postMessage(m,"*")}catch(e){}'
            .'try{window.top.postMessage(m,"*")}catch(e){}})();</script>'
            .'</body></html>',
            200,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }

    protected function persistTokensIfAny(Doktor $doktor, array $result): void
    {
        $u = trim((string) ($result['utoken'] ?? ''));
        $c = trim((string) ($result['ctoken'] ?? ''));
        if ($u === '' && $c === '') {
            return;
        }
        try {
            $doktor->forceFill(array_filter([
                'paytr_utoken' => $u !== '' ? $u : null,
                'paytr_ctoken' => $c !== '' ? $c : null,
                'paytr_recurring_id' => $c !== '' ? $c : null,
            ]))->save();
        } catch (\Throwable $e) {
            Log::warning('PayTR token kaydı (direct JSON): '.$e->getMessage());
        }
    }
}
