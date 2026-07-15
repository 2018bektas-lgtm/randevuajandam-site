<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Doktor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HekimHizmetController extends Controller
{
    /**
     * Display a listing of the doctor's services.
     */
    public function index(): View
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $hizmetler = $doktor->hizmetler()->latest()->paginate(10);

        return view('hekim.hizmet.index', compact('hizmetler'));
    }

    /**
     * Show the form for creating a new service.
     */
    public function create(): View
    {
        return view('hekim.hizmet.ekle');
    }

    /**
     * Store a newly created service in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'ad' => ['required', 'string', 'max:255'],
            'aciklama' => ['nullable', 'string'],
            'resim' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
            'sure' => ['required', 'integer', 'min:1', 'max:1440'],
            'fiyat' => ['nullable', 'numeric', 'min:0'],
            'meta_baslik' => ['nullable', 'string', 'max:255'],
            'meta_aciklama' => ['nullable', 'string', 'max:255'],
            'meta_anahtar_kelimeler' => ['nullable', 'string', 'max:255'],
        ], [
            'ad.required' => 'Hizmet adı zorunludur.',
            'resim.image' => 'Yüklenen dosya bir resim olmalıdır.',
            'resim.max' => 'Resim boyutu en fazla 10 MB olabilir.',
            'sure.required' => 'Hizmet süresi zorunludur.',
            'sure.integer' => 'Hizmet süresi tam sayı olmalıdır.',
            'sure.min' => 'Hizmet süresi en az 1 dakika olmalıdır.',
            'fiyat.numeric' => 'Fiyat geçerli bir sayı olmalıdır.',
            'fiyat.min' => 'Fiyat en az 0 olabilir.',
        ]);

        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $data = [
            'ad' => $request->ad,
            'aciklama' => $request->aciklama,
            'sure' => $request->sure,
            'fiyat' => $request->fiyat,
            'meta_baslik' => $request->meta_baslik,
            'meta_aciklama' => $request->meta_aciklama,
            'meta_anahtar_kelimeler' => $request->meta_anahtar_kelimeler,
            'aktif_mi' => $request->has('aktif_mi'),
        ];

        if ($request->hasFile('resim')) {
            $data['resim'] = $request->file('resim')->store('uploads/hizmet', 'public');
        }

        $doktor->hizmetler()->create($data);

        return redirect()->route('hekim.hizmetler.index')->with('basarili', 'Hizmet başarıyla eklendi.');
    }

    /**
     * Show the form for editing the specified service.
     */
    public function edit(string $id): View
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $hizmet = $doktor->hizmetler()->findOrFail($id);

        return view('hekim.hizmet.duzenle', compact('hizmet'));
    }

    /**
     * Update the specified service in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'ad' => ['required', 'string', 'max:255'],
            'aciklama' => ['nullable', 'string'],
            'resim' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
            'sure' => ['required', 'integer', 'min:1', 'max:1440'],
            'fiyat' => ['nullable', 'numeric', 'min:0'],
            'meta_baslik' => ['nullable', 'string', 'max:255'],
            'meta_aciklama' => ['nullable', 'string', 'max:255'],
            'meta_anahtar_kelimeler' => ['nullable', 'string', 'max:255'],
        ], [
            'ad.required' => 'Hizmet adı zorunludur.',
            'resim.image' => 'Yüklenen dosya bir resim olmalıdır.',
            'resim.max' => 'Resim boyutu en fazla 10 MB olabilir.',
            'sure.required' => 'Hizmet süresi zorunludur.',
            'sure.integer' => 'Hizmet süresi tam sayı olmalıdır.',
            'sure.min' => 'Hizmet süresi en az 1 dakika olmalıdır.',
            'fiyat.numeric' => 'Fiyat geçerli bir sayı olmalıdır.',
            'fiyat.min' => 'Fiyat en az 0 olabilir.',
        ]);

        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $hizmet = $doktor->hizmetler()->findOrFail($id);

        $data = [
            'ad' => $request->ad,
            'aciklama' => $request->aciklama,
            'sure' => $request->sure,
            'fiyat' => $request->fiyat,
            'meta_baslik' => $request->meta_baslik,
            'meta_aciklama' => $request->meta_aciklama,
            'meta_anahtar_kelimeler' => $request->meta_anahtar_kelimeler,
            'aktif_mi' => $request->has('aktif_mi'),
        ];

        if ($request->hasFile('resim')) {
            if ($hizmet->resim) {
                Storage::disk('public')->delete($hizmet->resim);
            }

            $data['resim'] = $request->file('resim')->store('uploads/hizmet', 'public');
        }

        $hizmet->update($data);

        return redirect()->route('hekim.hizmetler.index')->with('basarili', 'Hizmet başarıyla güncellendi.');
    }

    /**
     * Remove the specified service from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $hizmet = $doktor->hizmetler()->findOrFail($id);

        if ($hizmet->resim) {
            Storage::disk('public')->delete($hizmet->resim);
        }

        $hizmet->delete();

        return redirect()->route('hekim.hizmetler.index')->with('basarili', 'Hizmet başarıyla silindi.');
    }
}
