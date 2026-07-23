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

        $periyot        = $request->input('odeme_periyodu', 'aylik');
        $periodPrice    = $periyot === 'yillik' ? (float) $paket->yillik_fiyat : (float) $paket->aylik_fiyat;
        $discountedPrice = $periyot === 'yillik' ? $paket->yillik_indirimli_fiyat : $paket->aylik_indirimli_fiyat;
        $tutar          = $discountedPrice !== null && (float) $discountedPrice > 0 ? (float) $discountedPrice : $periodPrice;
        $refFiyat       = app(ReferansService::class)->indirimliTutar($doktor, $tutar);
        $tutarBrut      = $refFiyat['brut'];
        $tutar          = $refFiyat['tutar'];

        $rules = [
            'paket_id'        => 'required|exists:paketler,id',
            'odeme_periyodu'  => 'required|in:aylik,yillik',
            'mesafeli_onay'   => 'accepted',
            'kvkk_odeme_onay' => 'accepted',
            'kart_sahibi'     => 'required|string|max:100',
            'kart_no'         => 'required|string|min:15|max:19',
            'kart_ay'         => 'required|string|size:2',
            'kart_yil'        => 'required|string|size:2',
            'kart_cvv'        => 'required|string|min:3|max:4',
        ];

        if ($paket->klinikPaketiMi()) {
            $rules['klinik_adi'] = 'required|string|max:255';
            $rules['telefon']    = 'required|string';
            $rules['e_posta']    = 'nullable|email|max:255';
            $rules['adres']      = 'required|string';
            $rules['il_id']      = 'required|exists:iller,id';
            $rules['ilce_id']    = 'required|string|max:255';
        }

        $request->validate($rules, [
            'mesafeli_onay.accepted'   => 'Mesafeli satış sözleşmesini kabul etmelisiniz.',
            'kvkk_odeme_onay.accepted' => 'KVKK aydınlatma metnini kabul etmelisiniz.',
            'kart_sahibi.required'     => 'Kart sahibinin adını girin.',
            'kart_no.required'         => 'Kart numarasını girin.',
            'kart_ay.required'         => 'Son kullanma ayını girin.',
            'kart_yil.required'        => 'Son kullanma yılını girin.',
            'kart_cvv.required'        => 'CVV kodunu girin.',
            'klinik_adi.required'      => 'Klinik adı zorunludur.',
            'il_id.required'           => 'İl seçimi zorunludur.',
            'ilce_id.required'         => 'İlçe seçimi zorunludur.',
        ]);

        $paytr = app(PaytrService::class);
        if (! $paytr->isConfigured()) {
            return response()->json(['error' => 'Kartlı ödeme şu anda kullanıma açık değil.'], 422);
        }

        $merchantOid = $paytr->makeMerchantOid();
        $kurulum = $paket->klinikPaketiMi()
            ? $request->only(['klinik_adi', 'telefon', 'e_posta', 'adres', 'il_id', 'ilce_id'])
            : [];
        $kurulum['tutar_brut']              = $tutarBrut;
        $kurulum['referans_indirim_yuzde']  = $refFiyat['indirim_yuzde'];

        UyelikOdeme::create([
            'doktor_id'      => $doktor->id,
            'paket_id'       => $paket->id,
            'odeme_yontemi'  => 'paytr',
            'provider'       => 'paytr',
            'odeme_periyodu' => $periyot,
            'tutar'          => $tutar,
            'durum'          => 'beklemede',
            'merchant_oid'   => $merchantOid,
            'kurulum_verisi' => $kurulum ?: null,
        ]);

        $cardNo   = preg_replace('/\D+/', '', (string) $request->input('kart_no', ''));
        $cardType = match (true) {
            str_starts_with($cardNo, '9') => 'troy',
            str_starts_with($cardNo, '4') => 'visa',
            str_starts_with($cardNo, '5') => 'mastercard',
            str_starts_with($cardNo, '3') => 'amex',
            default => '',
        };

        $result = $paytr->createDirectPayment([
            'merchant_oid'     => $merchantOid,
            'email'            => $doktor->e_posta,
            'payment_amount'   => $tutar,
            'user_name'        => $doktor->ad_soyad,
            'user_address'     => $doktor->adres ?: ($doktor->il?->ad ?? 'Turkiye'),
            'user_phone'       => $doktor->telefon,
            'user_ip'          => $request->ip(),
            'basket_name'      => 'Randevu Ajandam - '.$paket->ad.' ('.$periyot.')',
            'card_owner'       => $request->input('kart_sahibi'),
            'card_number'      => $cardNo,
            'card_expire'      => $request->input('kart_ay').'/'.$request->input('kart_yil'),
            'card_cvv'         => $request->input('kart_cvv'),
            'card_type'        => $cardType,
            'recurring'        => true,
            'merchant_ok_url'  => route('frontend.odeme.paytr.3d.ok'),
            'merchant_fail_url' => route('frontend.odeme.paytr.3d.fail'),
        ]);

        if ($result['status'] === '3d') {
            return response()->json(['html' => $result['html'], 'merchant_oid' => $merchantOid]);
        }

        if ($result['status'] === 'success') {
            return response()->json(['redirect' => route('frontend.odeme.paytr.ok')]);
        }

        UyelikOdeme::where('merchant_oid', $merchantOid)->update(['durum' => 'reddedildi']);

        return response()->json([
            'error' => $result['errorMessage'] ?? 'Ödeme başlatılamadı. Kart bilgilerinizi kontrol edin.',
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
        return response(
            '<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>'
            .'<body style="font-family:sans-serif;text-align:center;padding:40px;background:#fef2f2">'
            .'<div style="max-width:280px;margin:0 auto">'
            .'<div style="font-size:52px;color:#dc2626">&#10007;</div>'
            .'<p style="color:#b91c1c;font-weight:bold;font-size:14px;margin:8px 0">3D doğrulama başarısız</p>'
            .'<p style="color:#6b7280;font-size:12px">Kart bilgilerinizi kontrol edin.</p>'
            .'</div>'
            .'<script>try{window.parent.postMessage({paytr3d:"fail"},"*")}catch(e){}'
            .'try{window.top.postMessage({paytr3d:"fail"},"*")}catch(e){}</script>'
            .'</body></html>',
            200,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }
}
