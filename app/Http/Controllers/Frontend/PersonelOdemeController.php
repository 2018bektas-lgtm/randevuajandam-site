<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Odeme;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PersonelOdemeController extends Controller
{
    /**
     * Display clinic payments list.
     */
    public function index(Request $request)
    {
        $personel = Auth::guard('personel')->user();
        if (! $personel->yetkisiVarMi('odeme')) {
            abort(403, 'Bu sayfaya erişim yetkiniz bulunmamaktadır.');
        }

        $klinik = $personel->klinik;
        $doktorlar = $klinik->doktorlar()->where('aktif_mi', true)->get();

        $secilenDoktorId = $request->input('doktor_id');
        $durum = $request->input('durum');
        $tarih = $request->input('tarih', Carbon::today()->toDateString());

        $query = Odeme::whereHas('doktor', function ($q) use ($klinik) {
            $q->where('klinik_id', $klinik->id);
        })->with('doktor', 'hasta', 'hizmet');

        if ($secilenDoktorId) {
            $query->where('doktor_id', $secilenDoktorId);
        }

        if ($durum) {
            $query->where('durum', $durum);
        }

        if ($tarih) {
            $query->whereDate('odeme_tarihi', $tarih);
        }

        $odemeler = $query->orderBy('created_at', 'desc')->paginate(20);
        $toplamGelir = (float) $query->sum('odenen_tutar');

        return view('personel.odemeler.al', compact(
            'personel',
            'klinik',
            'doktorlar',
            'odemeler',
            'secilenDoktorId',
            'durum',
            'tarih',
            'toplamGelir'
        ));
    }

    /**
     * Record a new payment from patient.
     */
    public function store(Request $request)
    {
        $personel = Auth::guard('personel')->user();
        if (! $personel->yetkisiVarMi('odeme')) {
            abort(403, 'Bu işlem için yetkiniz bulunmamaktadır.');
        }

        $klinik = $personel->klinik;

        $request->validate([
            'hasta_id' => 'required|exists:hastalar,id',
            'doktor_id' => 'required|exists:doktorlar,id',
            'tutar' => 'required|numeric|min:0.01',
            'odeme_yontemi' => 'required|in:nakit,kredi_karti,havale,online',
            'odeme_tarihi' => 'required|date',
            'aciklama' => 'nullable|string|max:500',
        ], [
            'hasta_id.required' => 'Hasta seçimi zorunludur.',
            'doktor_id.required' => 'Hekim seçimi zorunludur.',
            'tutar.required' => 'Ödeme tutarı zorunludur.',
            'odeme_yontemi.required' => 'Ödeme yöntemi zorunludur.',
            'odeme_tarihi.required' => 'Ödeme tarihi zorunludur.',
        ]);

        // Ensure doctor belongs to clinic
        $doktor = $klinik->doktorlar()->findOrFail($request->doktor_id);

        $odeme = Odeme::create([
            'doktor_id' => $doktor->id,
            'hasta_id' => $request->hasta_id,
            'tutar' => $request->tutar,
            'odenen_tutar' => $request->tutar,
            'odeme_yontemi' => $request->odeme_yontemi,
            'durum' => 'odendi',
            'aciklama' => $request->aciklama,
            'odeme_tarihi' => $request->odeme_tarihi,
        ]);

        // Add payment installment details
        $odeme->kalemler()->create([
            'tutar' => $request->tutar,
            'tarih' => $request->odeme_tarihi,
            'odeme_yontemi' => $request->odeme_yontemi,
            'not' => 'Sekreter tarafından alınan ödeme',
        ]);

        return redirect()->route('personel.odemeler.index')->with('basari', 'Ödeme kaydı başarıyla eklendi.');
    }

    /**
     * Cancel payment record.
     */
    public function destroy($id)
    {
        $personel = Auth::guard('personel')->user();
        if (! $personel->yetkisiVarMi('odeme')) {
            abort(403, 'Bu işlem için yetkiniz bulunmamaktadır.');
        }

        $klinik = $personel->klinik;

        $odeme = Odeme::whereHas('doktor', function ($q) use ($klinik) {
            $q->where('klinik_id', $klinik->id);
        })->findOrFail($id);

        $odeme->update(['durum' => 'iptal']);
        $odeme->kalemler()->delete(); // delete installments

        return redirect()->route('personel.odemeler.index')->with('basari', 'Ödeme kaydı iptal edildi.');
    }
}
