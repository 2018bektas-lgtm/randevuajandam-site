<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class LegalController extends Controller
{
    public function gizlilik(): View
    {
        return view('frontend.legal.gizlilik', [
            'baslik' => 'Gizlilik Politikası',
            'guncelleme' => '18 Temmuz 2026',
        ]);
    }

    public function kullanim(): View
    {
        return view('frontend.legal.kullanim', [
            'baslik' => 'Kullanım Koşulları',
            'guncelleme' => '18 Temmuz 2026',
        ]);
    }

    public function kvkk(): View
    {
        return view('frontend.legal.kvkk', [
            'baslik' => 'KVKK Aydınlatma Metni',
            'guncelleme' => '18 Temmuz 2026',
        ]);
    }

    public function mesafeli(): View
    {
        return view('frontend.legal.mesafeli', [
            'baslik' => 'Mesafeli Satış ve Abonelik Sözleşmesi',
            'guncelleme' => '20 Temmuz 2026',
        ]);
    }

    public function iade(): View
    {
        return view('frontend.legal.iade', [
            'baslik' => 'İade, Cayma ve Abonelik İptal Politikası',
            'guncelleme' => '20 Temmuz 2026',
        ]);
    }

    public function hakkimizda(): View
    {
        return view('frontend.legal.hakkimizda', [
            'baslik' => 'Hakkımızda',
            'guncelleme' => '20 Temmuz 2026',
        ]);
    }

    public function iletisim(): View
    {
        return view('frontend.legal.iletisim', [
            'baslik' => 'İletişim',
            'guncelleme' => '20 Temmuz 2026',
        ]);
    }
}
