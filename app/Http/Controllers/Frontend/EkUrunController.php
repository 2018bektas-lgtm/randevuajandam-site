<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\EkUrunOdeme;
use App\Services\PaytrService;
use App\Services\SmsKontorService;
use App\Support\KistHesap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Ek ürün ödemeleri — PayTR Direkt API 3D (non_3d=0), kart saklama yok.
 */
class EkUrunController extends Controller
{
    public function smsForm()
    {
        $doktor = Auth::guard('doktor')->user();
        $paket = $doktor->aktifPaket();
        if (! $paket || ! $paket->hasFeature('sms_hatirlatma')) {
            return redirect()->route('frontend.hekim.paket_sec', ['degistir' => 1])
                ->with('hata', 'SMS hatırlatma özellikli bir paket gereklidir.');
        }

        $kontor = app(SmsKontorService::class);
        $kalan = $kontor->kalan($doktor);
        $kullanilan = $kontor->kullanilan($doktor);
        $ek = $kontor->ekKontor($doktor);
        $paketler = config('ek_urunler.sms_paketleri', []);

        return view('hekim.ek-urun.sms', compact('doktor', 'paket', 'kalan', 'kullanilan', 'ek', 'paketler'));
    }

    public function smsOdeme(Request $request, PaytrService $paytr)
    {
        $request->validate([
            'paket_kod' => 'required|string',
            'okudum_anladim' => 'accepted',
            'kart_sahibi' => 'required|string|max:100',
            'kart_no' => 'required|string|min:15|max:19',
            'kart_ay' => 'required|string|min:1|max:2',
            'kart_yil' => 'required|string|min:2|max:4',
            'kart_cvv' => 'required|string|min:3|max:4',
        ]);

        $packs = config('ek_urunler.sms_paketleri', []);
        if (! isset($packs[$request->paket_kod])) {
            return back()->with('hata', 'Geçersiz SMS paketi.');
        }

        $pack = $packs[$request->paket_kod];
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinikteMi() ? $doktor->klinik : null;

        if (! $paytr->isConfigured()) {
            return back()->with('hata', 'PayTR yapılandırılmamış.');
        }

        $merchantOid = $paytr->makeMerchantOid('SM');
        EkUrunOdeme::create([
            'tip' => 'sms_kontor',
            'doktor_id' => $doktor->id,
            'klinik_id' => $klinik?->id,
            'adet' => (int) $pack['adet'],
            'birim_fiyat' => (float) $pack['fiyat'],
            'tutar' => (float) $pack['fiyat'],
            'periyot' => 'tek_sefer',
            'kist_orani' => 1,
            'durum' => 'beklemede',
            'merchant_oid' => $merchantOid,
            'meta' => ['paket_kod' => $request->paket_kod, 'etiket' => $pack['etiket'] ?? ''],
        ]);

        return $this->start3d(
            $paytr,
            $request,
            $merchantOid,
            (float) $pack['fiyat'],
            'SMS Kontor — '.($pack['etiket'] ?? $pack['adet'].' adet'),
            $doktor
        );
    }

    public function personelForm()
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;
        if (! $klinik || ! $doktor->hasClinicPermission('yonetim_paneli')) {
            return redirect()->route('hekim.panel')->with('hata', 'Klinik personel koltuğu için yetkiniz yok.');
        }

        $paket = $klinik->paket;
        if (! $paket) {
            return redirect()->route('hekim.klinik.personeller')->with('hata', 'Paket bulunamadı.');
        }

        $periyot = $klinik->odeme_periyodu ?? 'aylik';
        $birim = $periyot === 'yillik'
            ? (float) ($paket->ek_personel_yillik_fiyat ?: config('ek_urunler.ek_personel_yillik'))
            : (float) ($paket->ek_personel_aylik_fiyat ?: config('ek_urunler.ek_personel_aylik'));

        $kist = KistHesap::tutar($birim, $klinik->uyelik_bitis, false);
        $dahil = (int) ($paket->max_personel_sayisi ?? 0);
        $ek = (int) ($klinik->ek_personel_koltuk_sayisi ?? 0);
        $mevcut = $klinik->personeller()->count();

        return view('klinik.ek-personel', compact(
            'klinik', 'paket', 'periyot', 'birim', 'kist', 'dahil', 'ek', 'mevcut'
        ));
    }

    public function personelOdeme(Request $request, PaytrService $paytr)
    {
        $request->validate([
            'adet' => 'required|integer|min:1|max:10',
            'okudum_anladim' => 'accepted',
            'kart_sahibi' => 'required|string|max:100',
            'kart_no' => 'required|string|min:15|max:19',
            'kart_ay' => 'required|string|min:1|max:2',
            'kart_yil' => 'required|string|min:2|max:4',
            'kart_cvv' => 'required|string|min:3|max:4',
        ]);

        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;
        $paket = $klinik?->paket;
        if (! $klinik || ! $paket) {
            return back()->with('hata', 'Klinik/paket bulunamadı.');
        }

        $adet = (int) $request->adet;
        $periyot = $klinik->odeme_periyodu ?? 'aylik';
        $birim = $periyot === 'yillik'
            ? (float) ($paket->ek_personel_yillik_fiyat ?: config('ek_urunler.ek_personel_yillik'))
            : (float) ($paket->ek_personel_aylik_fiyat ?: config('ek_urunler.ek_personel_aylik'));

        $kist = KistHesap::tutar($birim * $adet, $klinik->uyelik_bitis, false);
        $merchantOid = $paytr->makeMerchantOid('EP');

        EkUrunOdeme::create([
            'tip' => 'personel_koltuk',
            'doktor_id' => $doktor->id,
            'klinik_id' => $klinik->id,
            'adet' => $adet,
            'birim_fiyat' => $birim,
            'tutar' => $kist['tutar'],
            'periyot' => $periyot,
            'kist_orani' => $kist['oran'],
            'durum' => 'beklemede',
            'merchant_oid' => $merchantOid,
            'meta' => ['kist' => $kist],
        ]);

        return $this->start3d(
            $paytr,
            $request,
            $merchantOid,
            $kist['tutar'],
            $adet.' Ek Personel Koltugu (kist %'.round($kist['oran'] * 100).')',
            $doktor,
            $klinik->adres
        );
    }

    protected function start3d(
        PaytrService $paytr,
        Request $request,
        string $merchantOid,
        float $tutar,
        string $basket,
        $doktor,
        ?string $address = null
    ) {
        $result = $paytr->createDirectPayment([
            'merchant_oid' => $merchantOid,
            'email' => $doktor->e_posta,
            'payment_amount' => $tutar,
            'user_name' => $doktor->ad_soyad ?? 'Hekim',
            'user_address' => $address ?: ($doktor->adres ?? 'Turkiye'),
            'user_phone' => $doktor->telefon ?? '05000000000',
            'user_ip' => $request->ip(),
            'basket_name' => $basket,
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

        Log::error('Ek ürün 3D başarısız', ['oid' => $merchantOid, 'result' => $result]);
        EkUrunOdeme::where('merchant_oid', $merchantOid)->update(['durum' => 'iptal']);

        return back()->withInput()->with('hata', $result['errorMessage'] ?? '3D ödeme başlatılamadı.');
    }
}
