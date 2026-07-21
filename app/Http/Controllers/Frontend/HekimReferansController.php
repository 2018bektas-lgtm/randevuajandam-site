<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ReferansDavet;
use App\Services\ReferansService;
use Illuminate\Support\Facades\Auth;

class HekimReferansController extends Controller
{
    public function index(ReferansService $referans)
    {
        $doktor = Auth::guard('doktor')->user();
        $ozet = $referans->panelOzet($doktor);
        $davetler = ReferansDavet::query()
            ->with('davetEdilen:id,ad_soyad,e_posta,created_at')
            ->where('davet_eden_id', $doktor->id)
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('hekim.referans.index', compact('doktor', 'ozet', 'davetler'));
    }
}
