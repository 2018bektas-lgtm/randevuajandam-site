<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\KlinikEkKoltukOdeme;
use App\Services\PaytrService;
use App\Support\KistHesap;
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
            ? (float) ($paket->ek_doktor_yillik_fiyat ?? 6240)
            : (float) ($paket->ek_doktor_aylik_fiyat ?? 650);

        $periyotLabel = $periyot === 'yillik' ? 'Yıllık' : 'Aylık';

        $uyelikBitis = $klinik->uyelik_bitis
            ? $klinik->uyelik_bitis->format('d.m.Y')
            : 'Belirsiz';

        $kalanGun = $klinik->uyelik_bitis
            ? (int) max(0, now()->diffInDays($klinik->uyelik_bitis, false))
            : 0;

        $kist = KistHesap::tutar($birimFiyat, $klinik->uyelik_bitis, false);

        return view('klinik.ek-koltuk', compact(
            'klinik', 'paket', 'birimFiyat', 'periyot', 'periyotLabel', 'kalanGun', 'uyelikBitis', 'kist'
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
            'kart_sahibi' => 'required|string|max:100',
            'kart_no' => 'required|string|min:15|max:19',
            'kart_ay' => 'required|string|min:1|max:2',
            'kart_yil' => 'required|string|min:2|max:4',
            'kart_cvv' => 'required|string|min:3|max:4',
        ]);

        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        if (! $klinik || ! $klinik->paket) {
            return back()->with('hata', 'Klinik veya paket bilgisi bulunamadı.');
        }

        $paket = $klinik->paket;
        $periyot = $request->periyot;
        $adet = (int) $request->adet;

        $maxEk = $paket->max_ek_doktor;
        $mevcutEk = (int) ($klinik->ek_doktor_koltuk_sayisi ?? 0);
        if ($maxEk !== null && ($mevcutEk + $adet) > (int) $maxEk) {
            $kalan = max(0, (int) $maxEk - $mevcutEk);

            return back()->with('hata', "Bu pakette en fazla {$maxEk} ek hekim koltuğu eklenebilir. Kalan: {$kalan}.");
        }

        $birimFiyat = $periyot === 'yillik'
            ? (float) ($paket->ek_doktor_yillik_fiyat ?? 6240)
            : (float) ($paket->ek_doktor_aylik_fiyat ?? 650);

        $kist = KistHesap::tutar($adet * $birimFiyat, $klinik->uyelik_bitis, false);
        $tutar = $kist['tutar'];
        $merchantOid = $paytr->makeMerchantOid('EK');

        KlinikEkKoltukOdeme::create([
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

        // Direkt API 3D (ek ödeme — store_card yok)
        $result = $paytr->createDirectPayment([
            'merchant_oid' => $merchantOid,
            'email' => $doktor->e_posta,
            'payment_amount' => $tutar,
            'user_name' => $doktor->ad_soyad ?? 'Klinik Sahibi',
            'user_address' => $klinik->adres ?? 'Turkiye',
            'user_phone' => $doktor->telefon ?? '05000000000',
            'user_ip' => $request->ip(),
            'basket_name' => $paket->ad.' — '.$adet.' Ek Hekim Koltugu (kist %'.round($kist['oran'] * 100).')',
            'card_owner' => $request->input('kart_sahibi'),
            'card_number' => $request->input('kart_no'),
            'expiry_month' => $request->input('kart_ay'),
            'expiry_year' => $request->input('kart_yil'),
            'card_cvv' => $request->input('kart_cvv'),
            'store_card' => false,
            'non_3d' => '0',
            'merchant_ok_url' => route('frontend.odeme.paytr.3d.ok'),
            'merchant_fail_url' => route('frontend.odeme.paytr.3d.fail'),
        ]);

        if (($result['status'] ?? '') === '3d') {
            session(['paytr_direct_3d_html_'.$merchantOid => $result['html']]);

            return redirect()->route('frontend.odeme.paytr.3d.frame', ['merchantOid' => $merchantOid]);
        }

        if (in_array($result['status'] ?? '', ['success', 'wait_callback'], true)) {
            return redirect()->route('frontend.odeme.paytr.ok');
        }

        Log::error('Ek koltuk 3D hata', ['oid' => $merchantOid, 'result' => $result]);
        KlinikEkKoltukOdeme::where('merchant_oid', $merchantOid)->update(['durum' => 'iptal']);

        return back()->withInput()->with('hata', $result['errorMessage'] ?? '3D ödeme başlatılamadı.');
    }
}
