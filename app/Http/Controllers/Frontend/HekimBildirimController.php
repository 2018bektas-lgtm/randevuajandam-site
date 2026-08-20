<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\HekimBildirimService;
use Illuminate\Support\Facades\Auth;

class HekimBildirimController extends Controller
{
    public function __construct(private HekimBildirimService $bildirim) {}

    public function index()
    {
        $doktor = Auth::guard('doktor')->user();
        $bildirimler = $this->bildirim->getBildirimler($doktor);

        // Zamana göre grupla: bugün / bu hafta / daha önce
        $bugun = now()->startOfDay();
        $haftaBasi = now()->startOfWeek();

        $gruplar = [
            'bugun'      => $bildirimler->filter(fn ($b) => $b['tarih']->gte($bugun))->values(),
            'bu_hafta'   => $bildirimler->filter(fn ($b) => $b['tarih']->lt($bugun) && $b['tarih']->gte($haftaBasi))->values(),
            'daha_once'  => $bildirimler->filter(fn ($b) => $b['tarih']->lt($haftaBasi))->values(),
        ];

        return view('hekim.bildirimler', [
            'bildirimler' => $bildirimler,
            'gruplar'     => $gruplar,
        ]);
    }
}
