<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\FinansKategori;
use App\Models\Doktor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HekimFinansKategoriController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index()
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $gelirKategorileri = $doktor->finansKategoriler()->gelir()->orderBy('ad')->get();
        $giderKategorileri = $doktor->finansKategoriler()->gider()->orderBy('ad')->get();

        return view('hekim.finans.kategoriler', compact('doktor', 'gelirKategorileri', 'giderKategorileri'));
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $validated = $request->validate([
            'ad' => 'required|string|max:255',
            'tur' => 'required|in:gelir,gider',
            'renk' => 'nullable|string|max:7',
        ]);

        $doktor->finansKategoriler()->create([
            'ad' => $validated['ad'],
            'tur' => $validated['tur'],
            'renk' => $validated['renk'] ?? '#C96A2B',
            'aktif' => true,
        ]);

        return redirect()->back()->with('basarili', 'Kategori başarıyla oluşturuldu.');
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, $id)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $kategori = $doktor->finansKategoriler()->findOrFail($id);

        $validated = $request->validate([
            'ad' => 'required|string|max:255',
            'renk' => 'nullable|string|max:7',
        ]);

        $kategori->update([
            'ad' => $validated['ad'],
            'renk' => $validated['renk'] ?? '#C96A2B',
        ]);

        return redirect()->back()->with('basarili', 'Kategori başarıyla güncellendi.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy($id)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $kategori = $doktor->finansKategoriler()->findOrFail($id);

        // Check if there are payments or expenses associated with this category
        if ($kategori->odemeler()->exists() || $kategori->giderler()->exists()) {
            return redirect()->back()->withErrors(['hata' => 'Bu kategoriye ait finansal kayıtlar olduğundan silinemez. Pasif duruma getirebilirsiniz.']);
        }

        $kategori->delete();

        return redirect()->back()->with('basarili', 'Kategori başarıyla silindi.');
    }

    /**
     * Toggle the active status of the specified category.
     */
    public function toggleAktif($id)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $kategori = $doktor->finansKategoriler()->findOrFail($id);

        $kategori->update([
            'aktif' => !$kategori->aktif
        ]);

        return redirect()->back()->with('basarili', 'Kategori durumu başarıyla güncellendi.');
    }
}
