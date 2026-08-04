<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Doktor;
use App\Models\Hasta;
use App\Models\OnamFormu;
use App\Models\OnamImza;
use App\Services\HtmlSanitizer;
use App\Support\PaketYetki;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HekimOnamController extends Controller
{
    public function index()
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $formlar = $doktor->onamFormlari()->orderBy('sira')->orderByDesc('id')->get();
        $sonImzalar = OnamImza::where('doktor_id', $doktor->id)
            ->with(['form:id,baslik', 'hasta:id,ad,soyad'])
            ->orderByDesc('imzalandi_at')
            ->limit(30)
            ->get();

        return view('hekim.onam.index', compact('doktor', 'formlar', 'sonImzalar'));
    }

    public function store(Request $request)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        if ($deny = PaketYetki::denyIfMissing($request, $doktor, 'onam_formu')) {
            return $deny;
        }

        $data = $request->validate([
            'baslik' => ['required', 'string', 'max:255'],
            'icerik' => ['required', 'string', 'max:50000'],
            'aktif_mi' => ['nullable'],
        ]);

        $doktor->onamFormlari()->create([
            'baslik' => $data['baslik'],
            'icerik' => HtmlSanitizer::clean($data['icerik']),
            'aktif_mi' => $request->boolean('aktif_mi', true),
            'sira' => (int) ($doktor->onamFormlari()->max('sira') ?? 0) + 1,
        ]);

        return back()->with('basarili', 'Onam formu eklendi.');
    }

    public function update(Request $request, int $id)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        if ($deny = PaketYetki::denyIfMissing($request, $doktor, 'onam_formu')) {
            return $deny;
        }

        $form = $doktor->onamFormlari()->findOrFail($id);
        $data = $request->validate([
            'baslik' => ['required', 'string', 'max:255'],
            'icerik' => ['required', 'string', 'max:50000'],
            'aktif_mi' => ['nullable'],
        ]);

        $form->update([
            'baslik' => $data['baslik'],
            'icerik' => HtmlSanitizer::clean($data['icerik']),
            'aktif_mi' => $request->boolean('aktif_mi'),
        ]);

        return back()->with('basarili', 'Onam formu güncellendi.');
    }

    public function destroy(Request $request, int $id)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        if ($deny = PaketYetki::denyIfMissing($request, $doktor, 'onam_formu')) {
            return $deny;
        }

        $form = $doktor->onamFormlari()->findOrFail($id);
        $form->delete();

        return back()->with('basarili', 'Onam formu silindi.');
    }

    /**
     * Hekim panelinden hasta adına dijital onam kaydı (yüz yüze imza teyidi).
     */
    public function imzaKaydet(Request $request)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        if ($deny = PaketYetki::denyIfMissing($request, $doktor, 'onam_formu')) {
            return $deny;
        }

        $data = $request->validate([
            'onam_form_id' => ['required', 'integer'],
            'hasta_id' => ['required', 'integer'],
            'not' => ['nullable', 'string', 'max:1000'],
        ]);

        $form = $doktor->onamFormlari()->where('aktif_mi', true)->findOrFail($data['onam_form_id']);
        $hastaIds = $doktor->randevular()->distinct()->pluck('hasta_id');
        abort_unless($hastaIds->contains((int) $data['hasta_id']), 404);
        $hasta = Hasta::findOrFail($data['hasta_id']);

        OnamImza::create([
            'onam_form_id' => $form->id,
            'doktor_id' => $doktor->id,
            'hasta_id' => $hasta->id,
            'hasta_ad_soyad' => trim($hasta->ad.' '.$hasta->soyad),
            'ip' => $request->ip(),
            'imzalandi_at' => now(),
            'not' => $data['not'] ?? null,
        ]);

        return back()->with('basarili', 'Onam kaydı oluşturuldu.');
    }
}
