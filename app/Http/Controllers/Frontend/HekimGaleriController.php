<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Doktor;
use App\Models\DoktorGaleri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HekimGaleriController extends Controller
{
    /**
     * Display a listing of the doctor's gallery images.
     */
    public function index()
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        
        $galeriler = $doktor->galeriler()->orderBy('sira')->get();

        return view('hekim.galeri.index', compact('doktor', 'galeriler'));
    }

    /**
     * Store newly uploaded gallery images in storage.
     */
    public function store(Request $request)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $request->validate([
            'resimler' => 'required|array',
            'resimler.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // Max 5MB per image
            'basliklar' => 'nullable|array',
            'basliklar.*' => 'nullable|string|max:255',
        ], [
            'resimler.required' => 'Lütfen en az bir resim seçin.',
            'resimler.*.image' => 'Yüklenen dosyalar geçerli bir resim olmalıdır.',
            'resimler.*.mimes' => 'Resim formatı jpeg, png, jpg, gif veya webp olmalıdır.',
            'resimler.*.max' => 'Her bir resim en fazla 5MB boyutunda olabilir.',
        ]);

        $maxSira = $doktor->galeriler()->max('sira') ?? 0;
        $savedCount = 0;

        if ($request->hasFile('resimler')) {
            $files = $request->file('resimler');
            $basliklar = $request->input('basliklar', []);

            foreach ($files as $index => $file) {
                $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                if (! in_array($ext, ['jpeg', 'jpg', 'png', 'gif', 'webp'], true)) {
                    continue;
                }
                $fileName = 'doktor_'.$doktor->id.'_'.Str::uuid().'.'.$ext;
                $stored = $file->storeAs('uploads/galeri', $fileName, 'public');
                if (! $stored) {
                    continue;
                }
                // DB: uploads/... → public URL: /uploads/...
                $baslik = isset($basliklar[$index]) ? $basliklar[$index] : null;

                $doktor->galeriler()->create([
                    'resim_yolu' => $stored,
                    'baslik' => $baslik,
                    'sira' => ++$maxSira,
                ]);

                $savedCount++;
            }
        }

        return redirect()->route('hekim.galeriler.index')->with('basarili', $savedCount . ' adet fotoğraf başarıyla galerinize eklendi.');
    }

    /**
     * Update the specified gallery image details.
     */
    public function update(Request $request, $id)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $galeri = $doktor->galeriler()->findOrFail($id);

        $validated = $request->validate([
            'baslik' => 'nullable|string|max:255',
            'sira' => 'nullable|integer|min:0',
        ]);

        $galeri->update([
            'baslik' => $validated['baslik'],
            'sira' => $validated['sira'] ?? $galeri->sira,
        ]);

        return redirect()->route('hekim.galeriler.index')->with('basarili', 'Fotoğraf bilgileri başarıyla güncellendi.');
    }

    /**
     * Remove the specified gallery image.
     */
    public function destroy($id)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $galeri = $doktor->galeriler()->findOrFail($id);

        $path = ltrim((string) $galeri->resim_yolu, '/');
        if ($path !== '' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
        $legacy = public_path($path);
        if (is_file($legacy)) {
            @unlink($legacy);
        }

        $galeri->delete();

        return redirect()->route('hekim.galeriler.index')->with('basarili', 'Fotoğraf galerinizden başarıyla kaldırıldı.');
    }

    /**
     * Sort the gallery images.
     */
    public function sirala(Request $request)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:doktor_galerileri,id',
        ]);

        $ids = $request->input('ids');
        foreach ($ids as $index => $id) {
            $doktor->galeriler()->where('id', $id)->update(['sira' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
