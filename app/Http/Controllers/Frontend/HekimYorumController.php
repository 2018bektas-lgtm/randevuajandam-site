<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Doktor;
use App\Models\Yorum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Hekim / klinik paneli: hasta yorumlarını listeleme ve yanıtlama.
 * Onay / red / silme yalnızca platform yönetiminde (adil puanlama).
 */
class HekimYorumController extends Controller
{
    /**
     * Hekim kendi yorumlarını; klinik sahibi tüm klinik hekimlerinin yorumlarını görür.
     *
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Yorum>
     */
    protected function yorumQuery(Doktor $doktor)
    {
        if ($doktor->klinikSahibiMi() && $doktor->klinik_id) {
            $ids = Doktor::query()
                ->where('klinik_id', $doktor->klinik_id)
                ->pluck('id');

            return Yorum::query()->whereIn('doktor_id', $ids);
        }

        return Yorum::query()->where('doktor_id', $doktor->id);
    }

    public function index(Request $request)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $klinikGeneli = $doktor->klinikSahibiMi() && (bool) $doktor->klinik_id;

        $query = $this->yorumQuery($doktor)
            ->with(['hasta', 'randevu.hizmet', 'doktor:id,ad_soyad'])
            ->latest();

        if ($request->filled('durum')) {
            $query->where('onay_durumu', $request->durum);
        }

        if ($request->filled('puan')) {
            $query->where('puan', $request->puan);
        }

        if ($klinikGeneli && $request->filled('doktor_id')) {
            $query->where('doktor_id', (int) $request->doktor_id);
        }

        $yorumlar = $query->paginate(15)->withQueryString();

        $istatistikler = [
            'toplam' => $this->yorumQuery($doktor)->count(),
            'beklemede' => $this->yorumQuery($doktor)->where('onay_durumu', 'beklemede')->count(),
            'onaylandi' => $this->yorumQuery($doktor)->where('onay_durumu', 'onaylandi')->count(),
            'ortalama_puan' => round(
                (float) ($this->yorumQuery($doktor)->where('onay_durumu', 'onaylandi')->avg('puan') ?? 0),
                1
            ) ?: null,
        ];

        $klinikDoktorlar = $klinikGeneli
            ? Doktor::query()
                ->where('klinik_id', $doktor->klinik_id)
                ->orderBy('ad_soyad')
                ->get(['id', 'ad_soyad'])
            : collect();

        return view('hekim.yorum.index', compact('yorumlar', 'istatistikler', 'klinikGeneli', 'klinikDoktorlar'));
    }

    public function yanitla(Request $request, int $id)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $request->validate([
            'doktor_yaniti' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'doktor_yaniti.required' => 'Yanıt alanı zorunludur.',
            'doktor_yaniti.min' => 'Yanıt en az 5 karakter olmalıdır.',
            'doktor_yaniti.max' => 'Yanıt en fazla 500 karakter olabilir.',
        ]);

        $yorum = $this->yorumQuery($doktor)->whereKey($id)->firstOrFail();

        $yorum->update([
            'doktor_yaniti' => $request->input('doktor_yaniti'),
        ]);

        return redirect()->back()->with('basarili', 'Yanıtınız başarıyla kaydedildi.');
    }
}
