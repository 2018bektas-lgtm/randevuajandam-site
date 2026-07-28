<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class LegalController extends Controller
{
    /** Tüm yasal sayfalarda ortak son güncelleme */
    private const GUNCELLEME = '28 Temmuz 2026';

    public function gizlilik(): View
    {
        return view('frontend.legal.gizlilik', [
            'baslik' => 'Gizlilik Politikası',
            'guncelleme' => self::GUNCELLEME,
        ]);
    }

    public function kullanim(): View
    {
        return view('frontend.legal.kullanim', [
            'baslik' => 'Kullanım Koşulları',
            'guncelleme' => self::GUNCELLEME,
        ]);
    }

    public function kvkk(): View
    {
        return view('frontend.legal.kvkk', [
            'baslik' => 'KVKK Aydınlatma Metni',
            'guncelleme' => self::GUNCELLEME,
        ]);
    }

    public function mesafeli(): View
    {
        return view('frontend.legal.mesafeli', [
            'baslik' => 'Mesafeli Satış ve Abonelik Sözleşmesi',
            'guncelleme' => self::GUNCELLEME,
        ]);
    }

    public function iade(): View
    {
        return view('frontend.legal.iade', [
            'baslik' => 'İade, Cayma ve Abonelik İptal Politikası',
            'guncelleme' => self::GUNCELLEME,
        ]);
    }

    public function hakkimizda(): View
    {
        return view('frontend.legal.hakkimizda', [
            'baslik' => 'Hakkımızda',
            'guncelleme' => self::GUNCELLEME,
        ]);
    }

    public function iletisim(): View
    {
        return view('frontend.legal.iletisim', [
            'baslik' => 'İletişim',
            'guncelleme' => self::GUNCELLEME,
        ]);
    }
}
