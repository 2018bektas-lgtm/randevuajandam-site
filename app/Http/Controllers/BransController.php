<?php

namespace App\Http\Controllers;

use App\Models\Brans;
use App\Models\Yonetici;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class BransController extends Controller
{
    /**
     * Display a listing of the branches.
     */
    public function index(Request $request)
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();

        $perPage = $request->input('per_page', 15);
        if (! in_array($perPage, [5, 10, 15, 20, 50])) {
            $perPage = 15;
        }

        $branslar = Brans::orderBy('ad', 'asc')->paginate($perPage)->withQueryString();

        return view('yonetim.branslar.index', compact('yonetici', 'branslar'));
    }

    /**
     * Show the form for creating a new branch.
     */
    public function create()
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();

        return view('yonetim.branslar.ekle', compact('yonetici'));
    }

    /**
     * Store a newly created branch in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'ad' => 'required|string|max:255|unique:branslar,ad',
        ], [
            'ad.required' => 'Branş adı alanı zorunludur.',
            'ad.unique' => 'Bu branş adı zaten eklenmiş.',
            'ad.max' => 'Branş adı en fazla 255 karakter olabilir.',
        ]);

        Brans::create([
            'ad' => $request->ad,
        ]);

        Cache::forget('branslar_listesi');

        return redirect()->route('yonetim.branslar.index')->with('basarili', 'Branş başarıyla eklendi.');
    }

    /**
     * Show the form for editing the specified branch.
     */
    public function edit($id)
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();
        $brans = Brans::findOrFail($id);

        return view('yonetim.branslar.duzenle', compact('yonetici', 'brans'));
    }

    /**
     * Update the specified branch in storage.
     */
    public function update(Request $request, $id)
    {
        $brans = Brans::findOrFail($id);

        $request->validate([
            'ad' => 'required|string|max:255|unique:branslar,ad,'.$id,
        ], [
            'ad.required' => 'Branş adı alanı zorunludur.',
            'ad.unique' => 'Bu branş adı zaten kullanımda.',
            'ad.max' => 'Branş adı en fazla 255 karakter olabilir.',
        ]);

        $brans->update([
            'ad' => $request->ad,
        ]);

        Cache::forget('branslar_listesi');

        return redirect()->route('yonetim.branslar.index')->with('basarili', 'Branş başarıyla güncellendi.');
    }

    /**
     * Remove the specified branch from storage.
     */
    public function destroy($id)
    {
        $brans = Brans::findOrFail($id);

        // Detach doctors first (many-to-many relationship)
        $brans->doktorlar()->detach();

        $brans->delete();

        Cache::forget('branslar_listesi');

        return redirect()->route('yonetim.branslar.index')->with('basarili', 'Branş başarıyla silindi.');
    }
}
