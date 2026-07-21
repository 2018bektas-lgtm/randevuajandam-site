<?php

namespace App\Http\Controllers;

use App\Models\ReferansDavet;
use App\Models\Yonetici;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class YonetimReferansController extends Controller
{
    public function index(Request $request)
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();
        $query = ReferansDavet::with(['davetEden:id,ad_soyad,e_posta', 'davetEdilen:id,ad_soyad,e_posta'])
            ->orderByDesc('id');

        if (in_array($request->input('durum'), ['bekliyor', 'odullendirildi', 'iptal', 'reddedildi'], true)) {
            $query->where('durum', $request->input('durum'));
        }

        $davetler = $query->paginate(30)->withQueryString();
        $ayar = [
            'aktif' => config('referans.aktif'),
            'indirim' => config('referans.yuzde_davet_edilen'),
            'komisyon' => config('referans.yuzde_davet_eden'),
            'limit' => config('referans.aylik_limit_davet_eden'),
        ];

        return view('yonetim.referanslar.index', compact('yonetici', 'davetler', 'ayar'));
    }

    public function iptal(int $id)
    {
        $davet = ReferansDavet::query()->findOrFail($id);
        if ($davet->durum === 'odullendirildi') {
            return back()->with('hata', 'Ödüllendirilmiş kayıt iptal edilemez (süre zaten verilmiş).');
        }
        $davet->update([
            'durum' => 'iptal',
            'red_nedeni' => 'Yönetici iptali',
        ]);

        return back()->with('basarili', 'Referans kaydı iptal edildi.');
    }
}
