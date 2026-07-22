<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\KlinikEkKoltukOdeme;
use App\Services\PaytrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EkKoltukController extends Controller
{
    /**
     * Ek koltuk satın alma formu.
     */
    public function formGoster()
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        if (! $klinik) {
            return redirect()->route('hekim.panel')->with('hata', 'Klinik bulunamadı.');
        }

        $paket = $klinik->paket;
        if (! $paket) {
            return redirect()->route('hekim.klinik.doktorlar')->with('hata', 'Klinik paket bilgisi bulunamadı.');
        }

        $periyot = $klinik->odeme_periyodu ?? 'aylik';
        $birimFiyat = $periyot === 'yillik'
            ? (float) ($paket->ek_doktor_yillik_fiyat ?? 10000)
            : (float) ($paket->ek_doktor_aylik_fiyat ?? 1000);

        $periyotLabel = $periyot === 'yillik' ? 'Yıllık' : 'Aylık';

        $uyelikBitis = $klinik->uyelik_bitis
            ? $klinik->uyelik_bitis->format('d.m.Y')
            : 'Belirsiz';

        $kalanGun = $klinik->uyelik_bitis
            ? (int) max(0, now()->diffInDays($klinik->uyelik_bitis, false))
            : 0;

        return view('klinik.ek-koltuk', compact(
            'klinik', 'paket', 'birimFiyat', 'periyot', 'periyotLabel', 'kalanGun', 'uyelikBitis'
        ));
    }

    /**
     * Ödeme başlat — PayTR iFrame token al.
     */
    public function odemeBaslat(Request $request, PaytrService $paytr)
    {
        $request->validate([
            'adet' => 'required|integer|min:1|max:10',
            'periyot' => 'required|in:aylik,yillik',
            'okudum_anladim' => 'accepted',
        ]);

        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        if (! $klinik || ! $klinik->paket) {
            return back()->with('hata', 'Klinik veya paket bilgisi bulunamadı.');
        }

        $paket = $klinik->paket;
        $periyot = $request->periyot;
        $adet = (int) $request->adet;

        $birimFiyat = $periyot === 'yillik'
            ? (float) ($paket->ek_doktor_yillik_fiyat ?? 10000)
            : (float) ($paket->ek_doktor_aylik_fiyat ?? 1000);

        $tutar = $adet * $birimFiyat;

        $merchantOid = $paytr->makeMerchantOid('EK');

        // Ödeme kaydı oluştur
        $odeme = KlinikEkKoltukOdeme::create([
            'klinik_id' => $klinik->id,
            'doktor_id' => $doktor->id,
            'adet' => $adet,
            'periyot' => $periyot,
            'birim_fiyat' => $birimFiyat,
            'tutar' => $tutar,
            'durum' => 'beklemede',
            'merchant_oid' => $merchantOid,
            'uyelik_bitis_hizasi' => $klinik->uyelik_bitis,
            'okudum_anladim_at' => now(),
        ]);

        // PayTR token al
        $basketName = $paket->ad . ' — ' . $adet . ' Ek Hekim Koltuğu (' . ($periyot === 'yillik' ? 'Yıllık' : 'Aylık') . ')';

        $result = $paytr->createIframeToken([
            'merchant_oid' => $merchantOid,
            'email' => $doktor->e_posta,
            'payment_amount' => $tutar,
            'user_name' => $doktor->ad_soyad ?? 'Klinik Sahibi',
            'user_address' => $klinik->adres ?? 'Turkiye',
            'user_phone' => $doktor->telefon ?? '05000000000',
            'basket_name' => $basketName,
            'merchant_ok_url' => route('frontend.odeme.paytr.ok'),
            'merchant_fail_url' => route('frontend.odeme.paytr.fail'),
        ]);

        if ($result['status'] !== 'success') {
            $odeme->update(['durum' => 'iptal']);
            Log::error('Ek koltuk PayTR token hatası', ['merchant_oid' => $merchantOid, 'error' => $result['errorMessage'] ?? '']);
            return back()->with('hata', 'Ödeme başlatılamadı: ' . ($result['errorMessage'] ?? 'Bilinmeyen hata'));
        }

        // Token'ı session'a kaydet (PaytrCallbackController::iframe ile uyumlu)
        session(['paytr_iframe_token_' . $merchantOid => $result['token']]);

        // Ödeme kaydına token yaz
        $odeme->update(['paytr_token' => $result['token']]);

        return redirect()->route('frontend.odeme.paytr.iframe', ['merchantOid' => $merchantOid]);
    }
}
