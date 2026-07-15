<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Doktor;
use App\Models\Hasta;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HekimFinansController extends Controller
{
    /**
     * Financial Overview (General Dashboard)
     */
    public function index(Request $request)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $baslangicTarihi = Carbon::now()->startOfMonth();
        $bitisTarihi = Carbon::now()->endOfMonth();

        // 1. Stats Cards
        $buAyGelir = (float) $doktor->odemeler()
            ->whereBetween('odeme_tarihi', [$baslangicTarihi, $bitisTarihi])
            ->where('durum', '!=', 'iptal')
            ->sum('odenen_tutar');

        $buAyGider = (float) $doktor->giderler()
            ->whereBetween('tarih', [$baslangicTarihi, $bitisTarihi])
            ->sum('tutar');

        $buAyNetKar = $buAyGelir - $buAyGider;

        $toplamBorc = (float) $doktor->odemeler()
            ->whereIn('durum', ['beklemede', 'kismi_odeme'])
            ->selectRaw('SUM(tutar - odenen_tutar) as bakiye')
            ->value('bakiye') ?? 0.00;

        // 2. Chart 1: Monthly income/expense trends (Last 12 months)
        $months = [];
        $incomeTrends = [];
        $expenseTrends = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $year = $date->year;
            $month = $date->month;

            $months[] = $date->translatedFormat('F Y');

            $incomeTrends[] = (float) $doktor->odemeler()
                ->whereYear('odeme_tarihi', $year)
                ->whereMonth('odeme_tarihi', $month)
                ->where('durum', '!=', 'iptal')
                ->sum('odenen_tutar');

            $expenseTrends[] = (float) $doktor->giderler()
                ->whereYear('tarih', $year)
                ->whereMonth('tarih', $month)
                ->sum('tutar');
        }

        // 3. Chart 2: Service-based revenue distribution (Top 5 services + others)
        $hizmetGelirleriRaw = $doktor->odemeler()
            ->where('durum', '!=', 'iptal')
            ->whereNotNull('hizmet_id')
            ->selectRaw('hizmet_id, SUM(odenen_tutar) as toplam_gelir')
            ->groupBy('hizmet_id')
            ->with('hizmet')
            ->get();

        $hizmetLabels = [];
        $hizmetValues = [];
        $digerGelir = 0.00;

        // Add standalone manual revenues not linked to any service
        $serbestGelir = (float) $doktor->odemeler()
            ->where('durum', '!=', 'iptal')
            ->whereNull('hizmet_id')
            ->sum('odenen_tutar');

        if ($serbestGelir > 0) {
            $hizmetLabels[] = 'Diğer / Serbest Gelir';
            $hizmetValues[] = $serbestGelir;
        }

        foreach ($hizmetGelirleriRaw as $item) {
            $hizmetAd = $item->hizmet ? $item->hizmet->ad : 'Bilinmeyen Hizmet';
            if (count($hizmetLabels) < 5) {
                $hizmetLabels[] = $hizmetAd;
                $hizmetValues[] = (float) $item->toplam_gelir;
            } else {
                $digerGelir += (float) $item->toplam_gelir;
            }
        }

        if ($digerGelir > 0) {
            if (($key = array_search('Diğer / Serbest Gelir', $hizmetLabels)) !== false) {
                $hizmetValues[$key] += $digerGelir;
            } else {
                $hizmetLabels[] = 'Diğer Hizmetler';
                $hizmetValues[] = $digerGelir;
            }
        }

        // 4. Chart 3: Expense category distribution
        $giderKategorileriRaw = $doktor->giderler()
            ->selectRaw('kategori, SUM(tutar) as toplam_tutar')
            ->groupBy('kategori')
            ->get();

        $giderLabels = [];
        $giderValues = [];
        $kategoriIsimleri = [
            'kira' => 'Kira',
            'personel' => 'Personel',
            'malzeme' => 'Malzeme',
            'ekipman' => 'Ekipman',
            'vergi' => 'Vergi',
            'sigorta' => 'Sigorta',
            'diger' => 'Diğer',
        ];

        foreach ($giderKategorileriRaw as $item) {
            $giderLabels[] = $kategoriIsimleri[$item->kategori] ?? 'Diğer';
            $giderValues[] = (float) $item->toplam_tutar;
        }

        // 5. Recent transactions
        $sonOdemeler = $doktor->odemeler()
            ->latest()
            ->take(5)
            ->with('hasta', 'hizmet')
            ->get();

        $sonGiderler = $doktor->giderler()
            ->latest()
            ->take(5)
            ->get();

        return view('hekim.finans.index', compact(
            'doktor',
            'buAyGelir',
            'buAyGider',
            'buAyNetKar',
            'toplamBorc',
            'months',
            'incomeTrends',
            'expenseTrends',
            'hizmetLabels',
            'hizmetValues',
            'giderLabels',
            'giderValues',
            'sonOdemeler',
            'sonGiderler'
        ));
    }

    /**
     * Revenue List
     */
    public function gelirler(Request $request)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $query = $doktor->odemeler()->with('hasta', 'hizmet', 'finansKategori', 'kalemler')->latest();

        if ($request->filled('durum')) {
            $query->where('durum', $request->durum);
        }
        if ($request->filled('finans_kategori_id')) {
            $query->where('finans_kategori_id', $request->finans_kategori_id);
        }
        if ($request->filled('tarih_baslangic')) {
            $query->where('odeme_tarihi', '>=', $request->tarih_baslangic);
        }
        if ($request->filled('tarih_bitis')) {
            $query->where('odeme_tarihi', '<=', $request->tarih_bitis);
        }
        if ($request->filled('hasta_id')) {
            $query->where('hasta_id', $request->hasta_id);
        }

        $odemeler = $query->paginate(15)->withQueryString();

        $hastalar = Hasta::where(function ($q) use ($doktor) {
            $q->whereHas('randevular', function ($r) use ($doktor) {
                $r->where('doktor_id', $doktor->id);
            })->orWhereHas('odemeler', function ($o) use ($doktor) {
                $o->where('doktor_id', $doktor->id);
            });
        })->orderBy('ad')->get();

        $hizmetler = $doktor->hizmetler;
        $gelirKategorileri = $doktor->finansKategoriler()->gelir()->aktif()->orderBy('ad')->get();

        return view('hekim.finans.gelirler', compact('doktor', 'odemeler', 'hastalar', 'hizmetler', 'gelirKategorileri'));
    }

    /**
     * Save new Revenue (ana kayıt — ilk ödeme kalemi ile birlikte)
     */
    public function gelirKaydet(Request $request)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $validated = $request->validate([
            'hasta_id' => 'nullable|exists:hastalar,id',
            'hizmet_id' => 'nullable|exists:hizmetler,id',
            'finans_kategori_id' => 'nullable|exists:finans_kategoriler,id',
            'tutar' => 'required|numeric|min:0.01',
            'aciklama' => 'nullable|string|max:1000',
            'odeme_tarihi' => 'required|date',
            // İlk ödeme kalemi
            'ilk_odeme_tutar' => 'nullable|numeric|min:0',
            'ilk_odeme_yontemi' => 'required|in:nakit,kredi_karti,havale,online',
        ], [
            'tutar.required' => 'Toplam tutar zorunludur.',
            'odeme_tarihi.required' => 'Tarih zorunludur.',
        ]);

        $ilkOdemeTutar = (float) ($request->ilk_odeme_tutar ?? 0);
        $durum = 'beklemede';

        if ($ilkOdemeTutar >= (float) $validated['tutar']) {
            $durum = 'odendi';
        } elseif ($ilkOdemeTutar > 0) {
            $durum = 'kismi_odeme';
        }

        $odeme = $doktor->odemeler()->create([
            'hasta_id' => $validated['hasta_id'] ?? null,
            'hizmet_id' => $validated['hizmet_id'] ?? null,
            'finans_kategori_id' => $validated['finans_kategori_id'] ?? null,
            'tutar' => $validated['tutar'],
            'odenen_tutar' => $ilkOdemeTutar,
            'odeme_yontemi' => $validated['ilk_odeme_yontemi'],
            'durum' => $durum,
            'aciklama' => $validated['aciklama'] ?? null,
            'odeme_tarihi' => $validated['odeme_tarihi'],
        ]);

        // İlk ödeme kalemi oluştur
        if ($ilkOdemeTutar > 0) {
            $odeme->kalemler()->create([
                'tutar' => $ilkOdemeTutar,
                'tarih' => $validated['odeme_tarihi'],
                'odeme_yontemi' => $validated['ilk_odeme_yontemi'],
                'not' => 'İlk ödeme',
            ]);
        }

        return redirect()->route('hekim.finans.gelirler')->with('basarili', 'Gelir kaydı oluşturuldu.');
    }

    /**
     * Update Revenue (sadece ana bilgiler — tutar, kategori, hasta, hizmet, açıklama)
     */
    public function gelirGuncelle(Request $request, int $id)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $odeme = $doktor->odemeler()->findOrFail($id);

        $validated = $request->validate([
            'hasta_id' => 'nullable|exists:hastalar,id',
            'hizmet_id' => 'nullable|exists:hizmetler,id',
            'finans_kategori_id' => 'nullable|exists:finans_kategoriler,id',
            'tutar' => 'required|numeric|min:0.01',
            'aciklama' => 'nullable|string|max:1000',
            'odeme_tarihi' => 'required|date',
        ]);

        $odeme->update($validated);
        $odeme->odenenTutariGuncelle();

        return redirect()->back()->with('basarili', 'Gelir kaydı güncellendi.');
    }

    /**
     * Add payment installment to a revenue record
     */
    public function gelirKalemEkle(Request $request, int $id)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $odeme = $doktor->odemeler()->findOrFail($id);

        $validated = $request->validate([
            'tutar' => 'required|numeric|min:0.01',
            'tarih' => 'required|date',
            'odeme_yontemi' => 'required|in:nakit,kredi_karti,havale,online',
            'not' => 'nullable|string|max:500',
        ], [
            'tutar.required' => 'Ödeme tutarı zorunludur.',
            'tarih.required' => 'Ödeme tarihi zorunludur.',
        ]);

        $odeme->kalemler()->create($validated);
        $odeme->odenenTutariGuncelle();

        return redirect()->back()->with('basarili', 'Ödeme kalemi eklendi.');
    }

    /**
     * Delete payment installment
     */
    public function gelirKalemSil(int $odemeId, int $kalemId)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $odeme = $doktor->odemeler()->findOrFail($odemeId);
        $kalem = $odeme->kalemler()->findOrFail($kalemId);
        $kalem->delete();
        $odeme->odenenTutariGuncelle();

        return redirect()->back()->with('basarili', 'Ödeme kalemi silindi.');
    }

    /**
     * Delete Revenue Record
     */
    public function gelirSil(int $id)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $odeme = $doktor->odemeler()->findOrFail($id);
        $odeme->delete();

        return redirect()->back()->with('basarili', 'Gelir kaydı silindi.');
    }

    /**
     * Expense List
     */
    public function giderler(Request $request)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $query = $doktor->giderler()->with('finansKategori')->latest();

        if ($request->filled('finans_kategori_id')) {
            $query->where('finans_kategori_id', $request->finans_kategori_id);
        }
        if ($request->filled('tarih_baslangic')) {
            $query->where('tarih', '>=', $request->tarih_baslangic);
        }
        if ($request->filled('tarih_bitis')) {
            $query->where('tarih', '<=', $request->tarih_bitis);
        }

        $giderler = $query->paginate(15)->withQueryString();
        $giderKategorileri = $doktor->finansKategoriler()->gider()->aktif()->orderBy('ad')->get();

        return view('hekim.finans.giderler', compact('doktor', 'giderler', 'giderKategorileri'));
    }

    /**
     * Save Expense
     */
    public function giderKaydet(Request $request)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $validated = $request->validate([
            'baslik' => 'required|string|max:255',
            'finans_kategori_id' => 'nullable|exists:finans_kategoriler,id',
            'tutar' => 'required|numeric|min:0.01',
            'tarih' => 'required|date',
            'aciklama' => 'nullable|string|max:1000',
            'belge' => 'nullable|file|mimes:jpeg,png,pdf,jpg|max:4096',
        ], [
            'baslik.required' => 'Gider başlığı zorunludur.',
            'tutar.required' => 'Tutar zorunludur.',
            'tarih.required' => 'Tarih zorunludur.',
            'belge.max' => 'Belge en fazla 4MB olabilir.',
            'belge.mimes' => 'Belge formatı JPEG, PNG, JPG veya PDF olmalıdır.',
        ]);

        if ($request->hasFile('belge')) {
            $file = $request->file('belge');
            $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
            if (! in_array($ext, ['jpeg', 'jpg', 'png', 'pdf'], true)) {
                return back()->withErrors(['belge' => 'Geçersiz belge formatı.'])->withInput();
            }
            $fileName = 'belge_'.$doktor->id.'_'.time().'_'.\Illuminate\Support\Str::random(8).'.'.$ext;
            $file->storeAs('uploads/belgeler', $fileName, 'public');
            $validated['belge_yolu'] = 'uploads/belgeler/'.$fileName;
        }

        $doktor->giderler()->create($validated);

        return redirect()->route('hekim.finans.giderler')->with('basarili', 'Gider kaydı oluşturuldu.');
    }

    /**
     * Update Expense
     */
    public function giderGuncelle(Request $request, int $id)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $gider = $doktor->giderler()->findOrFail($id);

        $validated = $request->validate([
            'baslik' => 'required|string|max:255',
            'finans_kategori_id' => 'nullable|exists:finans_kategoriler,id',
            'tutar' => 'required|numeric|min:0.01',
            'tarih' => 'required|date',
            'aciklama' => 'nullable|string|max:1000',
        ]);

        $gider->update($validated);

        return redirect()->back()->with('basarili', 'Gider kaydı güncellendi.');
    }

    /**
     * Delete Expense
     */
    public function giderSil(int $id)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $gider = $doktor->giderler()->findOrFail($id);

        if ($gider->belge_yolu && file_exists(public_path($gider->belge_yolu))) {
            @unlink(public_path($gider->belge_yolu));
        }

        $gider->delete();

        return redirect()->back()->with('basarili', 'Gider kaydı silindi.');
    }

    /**
     * Patient Balances (Debts / Payments / Remainders)
     */
    public function hastaBakiyeleri(Request $request)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        // Load all patient accounts associated with the doctor
        $hastalarQuery = Hasta::whereHas('randevular', function ($q) use ($doktor) {
            $q->where('doktor_id', $doktor->id);
        })->orWhereHas('odemeler', function ($q) use ($doktor) {
            $q->where('doktor_id', $doktor->id);
        });

        if ($request->filled('arama')) {
            $arama = $request->arama;
            $hastalarQuery->where(function ($q) use ($arama) {
                $q->where('ad', 'like', "%{$arama}%")
                    ->orWhere('soyad', 'like', "%{$arama}%")
                    ->orWhere('telefon', 'like', "%{$arama}%");
            });
        }

        $hastalar = $hastalarQuery->get()
            ->map(function ($hasta) use ($doktor) {
                $odemeler = $hasta->odemeler()->where('doktor_id', $doktor->id)->where('durum', '!=', 'iptal')->get();
                $hasta->toplam_borc = (float) $odemeler->sum('tutar');
                $hasta->toplam_odenen = (float) $odemeler->sum('odenen_tutar');
                $hasta->kalan_bakiye = $hasta->toplam_borc - $hasta->toplam_odenen;

                return $hasta;
            })
            ->filter(function ($hasta) use ($request) {
                if ($request->filled('sadece_borclular') && $request->sadece_borclular == '1') {
                    return $hasta->kalan_bakiye > 0;
                }

                return $hasta->toplam_borc > 0;
            });

        return view('hekim.finans.hasta_bakiyeleri', compact('doktor', 'hastalar'));
    }

    /**
     * Generate PDF Financial Report
     */
    public function raporPdf(Request $request)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $tarihBaslangic = $request->filled('tarih_baslangic') ? Carbon::parse($request->tarih_baslangic) : Carbon::now()->startOfMonth();
        $tarihBitis = $request->filled('tarih_bitis') ? Carbon::parse($request->tarih_bitis) : Carbon::now()->endOfMonth();

        $odemeler = $doktor->odemeler()
            ->whereBetween('odeme_tarihi', [$tarihBaslangic, $tarihBitis])
            ->with('hasta', 'hizmet')
            ->orderBy('odeme_tarihi')
            ->get();

        $giderler = $doktor->giderler()
            ->whereBetween('tarih', [$tarihBaslangic, $tarihBitis])
            ->orderBy('tarih')
            ->get();

        $toplamGelir = (float) $odemeler->where('durum', '!=', 'iptal')->sum('odenen_tutar');
        $toplamGider = (float) $giderler->sum('tutar');
        $netKar = $toplamGelir - $toplamGider;

        $toplamTahsilEdilmeyen = (float) $odemeler->whereIn('durum', ['beklemede', 'kismi_odeme'])->sum('tutar')
                                 - (float) $odemeler->whereIn('durum', ['beklemede', 'kismi_odeme'])->sum('odenen_tutar');

        $data = [
            'doktor' => $doktor,
            'tarihBaslangic' => $tarihBaslangic,
            'tarihBitis' => $tarihBitis,
            'odemeler' => $odemeler,
            'giderler' => $giderler,
            'toplamGelir' => $toplamGelir,
            'toplamGider' => $toplamGider,
            'netKar' => $netKar,
            'toplamTahsilEdilmeyen' => $toplamTahsilEdilmeyen,
        ];

        $pdf = Pdf::loadView('hekim.finans.rapor_pdf', $data);

        return $pdf->download('Finans_Raporu_'.$tarihBaslangic->format('d_m_Y').'_'.$tarihBitis->format('d_m_Y').'.pdf');
    }
}
