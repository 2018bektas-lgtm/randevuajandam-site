<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Doktor;
use App\Models\Hasta;
use App\Models\HastaDosya;
use App\Support\PaketYetki;
use App\Support\PublicMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HekimHastaDosyaController extends Controller
{
    public function store(Request $request, int $hastaId)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        if ($deny = PaketYetki::denyIfMissing($request, $doktor, 'hasta_not_dosya')) {
            return $deny;
        }

        $this->assertHastaOwned($doktor, $hastaId);

        $data = $request->validate([
            'dosya' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx'],
            'baslik' => ['nullable', 'string', 'max:255'],
            'not' => ['nullable', 'string', 'max:1000'],
        ], [
            'dosya.required' => 'Dosya seçmelisiniz.',
            'dosya.max' => 'Dosya en fazla 10 MB olabilir.',
        ]);

        $file = $request->file('dosya');
        $path = PublicMedia::store($file, 'uploads/hasta-dosya');

        HastaDosya::create([
            'doktor_id' => $doktor->id,
            'hasta_id' => $hastaId,
            'baslik' => $data['baslik'] ?? $file->getClientOriginalName(),
            'dosya_yolu' => $path,
            'orijinal_ad' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'boyut' => $file->getSize(),
            'not' => $data['not'] ?? null,
        ]);

        return back()->with('basarili', 'Dosya yüklendi.');
    }

    public function destroy(Request $request, int $id)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        if ($deny = PaketYetki::denyIfMissing($request, $doktor, 'hasta_not_dosya')) {
            return $deny;
        }

        $dosya = HastaDosya::where('doktor_id', $doktor->id)->findOrFail($id);
        PublicMedia::delete($dosya->dosya_yolu);
        $dosya->delete();

        return back()->with('basarili', 'Dosya silindi.');
    }

    private function assertHastaOwned(Doktor $doktor, int $hastaId): void
    {
        $ids = $doktor->randevular()->distinct()->pluck('hasta_id');
        abort_unless($ids->contains($hastaId), 404);
        Hasta::findOrFail($hastaId);
    }
}
