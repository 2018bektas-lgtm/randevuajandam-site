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
            'guncelleme' => '17 Temmuz 2026',
        ]);
    }

    public function kullanim(): View
    {
        return view('frontend.legal.kullanim', [
            'baslik' => 'Kullanım Koşulları',
            'guncelleme' => '17 Temmuz 2026',
        ]);
    }

    public function kvkk(): View
    {
        return view('frontend.legal.kvkk', [
            'baslik' => 'KVKK Aydınlatma Metni',
            'guncelleme' => '17 Temmuz 2026',
        ]);
    }
}
