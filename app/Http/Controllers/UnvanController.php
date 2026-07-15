<?php

namespace App\Http\Controllers;

use App\Models\Unvan;
use App\Models\Yonetici;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnvanController extends Controller
{
    /**
     * Display a listing of the titles.
     */
    public function index(Request $request)
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();

        $perPage = $request->input('per_page', 15);
        if (! in_array($perPage, [5, 10, 15, 20, 50])) {
            $perPage = 15;
        }

        $unvanlar = Unvan::orderBy('ad', 'asc')->paginate($perPage)->withQueryString();

        return view('yonetim.unvanlar.index', compact('yonetici', 'unvanlar'));
    }

    /**
     * Show the form for creating a new title.
     */
    public function create()
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();

        return view('yonetim.unvanlar.ekle', compact('yonetici'));
    }

    /**
     * Store a newly created title in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'ad' => 'required|string|max:255|unique:unvanlar,ad',
        ], [
            'ad.required' => 'Unvan adı alanı zorunludur.',
            'ad.unique' => 'Bu unvan adı zaten eklenmiş.',
            'ad.max' => 'Unvan adı en fazla 255 karakter olabilir.',
        ]);

        Unvan::create([
            'ad' => $request->ad,
        ]);

        return redirect()->route('yonetim.unvanlar.index')->with('basarili', 'Unvan başarıyla eklendi.');
    }

    /**
     * Show the form for editing the specified title.
     */
    public function edit($id)
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();
        $unvan = Unvan::findOrFail($id);

        return view('yonetim.unvanlar.duzenle', compact('yonetici', 'unvan'));
    }

    /**
     * Update the specified title in storage.
     */
    public function update(Request $request, $id)
    {
        $unvan = Unvan::findOrFail($id);

        $request->validate([
            'ad' => 'required|string|max:255|unique:unvanlar,ad,'.$id,
        ], [
            'ad.required' => 'Unvan adı alanı zorunludur.',
            'ad.unique' => 'Bu unvan adı zaten kullanımda.',
            'ad.max' => 'Unvan adı en fazla 255 karakter olabilir.',
        ]);

        $unvan->update([
            'ad' => $request->ad,
        ]);

        return redirect()->route('yonetim.unvanlar.index')->with('basarili', 'Unvan başarıyla güncellendi.');
    }

    /**
     * Remove the specified title from storage.
     */
    public function destroy($id)
    {
        $unvan = Unvan::findOrFail($id);
        $unvan->delete();

        return redirect()->route('yonetim.unvanlar.index')->with('basarili', 'Unvan başarıyla silindi.');
    }
}
