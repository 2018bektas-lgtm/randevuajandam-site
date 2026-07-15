<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Egitim;
use App\Models\EgitimBasvuru;
use App\Models\EgitimFormAlani;
use App\Services\EgitimBasvuruService;
use App\Services\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use InvalidArgumentException;

class HekimEgitimController extends Controller
{
    public function index(): View
    {
        $doktor = Auth::guard('doktor')->user();
        $egitimler = $doktor->egitimler()->withCount([
            'basvurular',
            'basvurular as bekleyen_basvuru' => fn ($q) => $q->where('durum', 'beklemede'),
        ])->paginate(12);

        return view('hekim.egitim.index', compact('egitimler'));
    }

    public function create(): View
    {
        return view('hekim.egitim.form', ['egitim' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $doktor = Auth::guard('doktor')->user();

        if ($request->hasFile('kapak')) {
            $data['kapak'] = $request->file('kapak')->store('uploads/egitim', 'public');
        }

        $data['basvuru_acik_mi'] = $request->boolean('basvuru_acik_mi');
        $egitim = $doktor->egitimler()->create($data);

        $this->syncFormAlanlari($egitim, $request->input('alanlar', []));

        return redirect()
            ->route('hekim.egitimler.edit', $egitim->id)
            ->with('basarili', 'Eğitim kaydedildi. Form alanlarını düzenleyip yayınlayabilirsiniz.');
    }

    public function edit(int $id): View
    {
        $doktor = Auth::guard('doktor')->user();
        $egitim = $doktor->egitimler()->with('formAlanlari')->findOrFail($id);

        return view('hekim.egitim.form', compact('egitim'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $doktor = Auth::guard('doktor')->user();
        $egitim = $doktor->egitimler()->findOrFail($id);
        $data = $this->validated($request);

        if ($request->boolean('kapak_sil') && $egitim->kapak) {
            Storage::disk('public')->delete($egitim->kapak);
            $data['kapak'] = null;
        } elseif ($request->hasFile('kapak')) {
            if ($egitim->kapak) {
                Storage::disk('public')->delete($egitim->kapak);
            }
            $data['kapak'] = $request->file('kapak')->store('uploads/egitim', 'public');
        }

        $data['basvuru_acik_mi'] = $request->boolean('basvuru_acik_mi');
        $egitim->update($data);
        $this->syncFormAlanlari($egitim, $request->input('alanlar', []));

        return redirect()->back()->with('basarili', 'Eğitim güncellendi.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $doktor = Auth::guard('doktor')->user();
        $egitim = $doktor->egitimler()->findOrFail($id);
        if ($egitim->kapak) {
            Storage::disk('public')->delete($egitim->kapak);
        }
        $egitim->delete();

        return redirect()->route('hekim.egitimler.index')->with('basarili', 'Eğitim silindi.');
    }

    /**
     * Tüm eğitimlerin başvuruları (hekim paneli).
     */
    public function basvurularTumu(Request $request): View
    {
        $doktor = Auth::guard('doktor')->user();

        $query = EgitimBasvuru::query()
            ->where('doktor_id', $doktor->id)
            ->with(['egitim:id,baslik,slug,fiyat,durum'])
            ->orderByDesc('id');

        if ($request->filled('durum')) {
            $query->where('durum', $request->input('durum'));
        }
        if ($request->filled('ucret')) {
            $query->where('ucret_durumu', $request->input('ucret'));
        }
        if ($request->filled('egitim_id')) {
            $query->where('egitim_id', (int) $request->input('egitim_id'));
        }
        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($w) use ($q) {
                $w->where('ad', 'like', '%'.$q.'%')
                    ->orWhere('soyad', 'like', '%'.$q.'%')
                    ->orWhere('telefon', 'like', '%'.$q.'%')
                    ->orWhere('e_posta', 'like', '%'.$q.'%');
            });
        }

        $basvurular = $query->paginate(20)->withQueryString();
        $egitimler = $doktor->egitimler()->orderByDesc('id')->get(['id', 'baslik']);

        $ozet = [
            'toplam' => EgitimBasvuru::where('doktor_id', $doktor->id)->count(),
            'beklemede' => EgitimBasvuru::where('doktor_id', $doktor->id)->where('durum', 'beklemede')->count(),
            'onaylandi' => EgitimBasvuru::where('doktor_id', $doktor->id)->where('durum', 'onaylandi')->count(),
            'odeme_bekleyen' => EgitimBasvuru::where('doktor_id', $doktor->id)
                ->whereIn('ucret_durumu', ['bekliyor', 'kismi'])
                ->count(),
        ];

        // Form alan etiketleri (tüm eğitimler)
        $alanEtiketleri = EgitimFormAlani::query()
            ->whereIn('egitim_id', $egitimler->pluck('id'))
            ->get(['id', 'egitim_id', 'etiket'])
            ->keyBy('id');

        return view('hekim.egitim.basvurular', [
            'egitim' => null,
            'basvurular' => $basvurular,
            'egitimler' => $egitimler,
            'ozet' => $ozet,
            'alanEtiketleri' => $alanEtiketleri,
            'tumu' => true,
        ]);
    }

    public function basvurular(Request $request, int $id): View
    {
        $doktor = Auth::guard('doktor')->user();
        $egitim = $doktor->egitimler()->with('formAlanlari')->findOrFail($id);

        $query = $egitim->basvurular()->orderByDesc('id');
        if ($request->filled('durum')) {
            $query->where('durum', $request->input('durum'));
        }
        if ($request->filled('ucret')) {
            $query->where('ucret_durumu', $request->input('ucret'));
        }
        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($w) use ($q) {
                $w->where('ad', 'like', '%'.$q.'%')
                    ->orWhere('soyad', 'like', '%'.$q.'%')
                    ->orWhere('telefon', 'like', '%'.$q.'%')
                    ->orWhere('e_posta', 'like', '%'.$q.'%');
            });
        }

        $basvurular = $query->paginate(20)->withQueryString();

        $ozet = [
            'toplam' => $egitim->basvurular()->count(),
            'beklemede' => $egitim->basvurular()->where('durum', 'beklemede')->count(),
            'onaylandi' => $egitim->basvurular()->where('durum', 'onaylandi')->count(),
            'odeme_bekleyen' => $egitim->basvurular()
                ->whereIn('ucret_durumu', ['bekliyor', 'kismi'])
                ->count(),
        ];

        $alanEtiketleri = $egitim->formAlanlari->keyBy('id');

        return view('hekim.egitim.basvurular', [
            'egitim' => $egitim,
            'basvurular' => $basvurular,
            'egitimler' => collect([$egitim]),
            'ozet' => $ozet,
            'alanEtiketleri' => $alanEtiketleri,
            'tumu' => false,
        ]);
    }

    public function basvuruDurum(Request $request, int $id, int $basvuruId): RedirectResponse
    {
        $doktor = Auth::guard('doktor')->user();
        $egitim = $doktor->egitimler()->findOrFail($id);
        $basvuru = $egitim->basvurular()->findOrFail($basvuruId);

        $data = $request->validate([
            'durum' => ['required', 'in:beklemede,onaylandi,reddedildi,iptal'],
            'hekim_notu' => ['nullable', 'string', 'max:2000'],
        ]);

        $basvuru->update([
            'durum' => $data['durum'],
            'hekim_notu' => $data['hekim_notu'] ?? $basvuru->hekim_notu,
        ]);

        return back()->with('basarili', 'Başvuru durumu güncellendi.');
    }

    public function basvuruOdeme(Request $request, int $id, int $basvuruId, EgitimBasvuruService $service): RedirectResponse
    {
        $doktor = Auth::guard('doktor')->user();
        $egitim = $doktor->egitimler()->findOrFail($id);
        $basvuru = $egitim->basvurular()->findOrFail($basvuruId);

        $data = $request->validate([
            'odenen_tutar' => ['required', 'numeric', 'min:0.01'],
            'odeme_yontemi' => ['nullable', 'string', 'max:80'],
        ]);

        try {
            $service->odemeAlindi(
                $basvuru,
                (float) $data['odenen_tutar'],
                $data['odeme_yontemi'] ?? null
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basarili', 'Ödeme kaydedildi ve finans gelirlerine yansıtıldı (kategori: Eğitim).');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'baslik' => ['required', 'string', 'max:255'],
            'ozet' => ['nullable', 'string', 'max:2000'],
            'icerik' => ['nullable', 'string'],
            'kapak' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'tip' => ['required', 'in:yuz_yuze,online,hibrit'],
            'baslangic_at' => ['nullable', 'date'],
            'bitis_at' => ['nullable', 'date', 'after_or_equal:baslangic_at'],
            'mekan' => ['nullable', 'string', 'max:255'],
            'online_url' => ['nullable', 'string', 'max:500'],
            'fiyat' => ['nullable', 'numeric', 'min:0'],
            'odeme_notu' => ['nullable', 'string', 'max:500'],
            'kontenjan' => ['nullable', 'integer', 'min:1'],
            'basvuru_bitis_at' => ['nullable', 'date'],
            'durum' => ['required', 'in:taslak,yayinda,arsiv'],
            'meta_baslik' => ['nullable', 'string', 'max:255'],
            'meta_aciklama' => ['nullable', 'string', 'max:500'],
            'meta_anahtar_kelimeler' => ['nullable', 'string', 'max:255'],
            'sira' => ['nullable', 'integer', 'min:0'],
            'alanlar' => ['nullable', 'array'],
            'alanlar.*.etiket' => ['nullable', 'string', 'max:120'],
            'alanlar.*.tip' => ['nullable', 'in:text,textarea,email,phone,number,select,checkbox,date'],
            'alanlar.*.zorunlu_mu' => ['nullable'],
            'alanlar.*.secenekler' => ['nullable', 'string'],
            'alanlar.*.placeholder' => ['nullable', 'string', 'max:255'],
        ], [
            'baslik.required' => 'Eğitim başlığı zorunludur.',
        ]);

        $data['sira'] = (int) ($data['sira'] ?? 0);
        if (array_key_exists('fiyat', $data) && $data['fiyat'] === '') {
            $data['fiyat'] = null;
        }

        $clean = collect($data)->except(['alanlar', 'kapak'])->all();
        if (array_key_exists('icerik', $clean)) {
            $clean['icerik'] = HtmlSanitizer::clean($clean['icerik'] ?? '');
        }
        if (array_key_exists('ozet', $clean) && is_string($clean['ozet'])) {
            $clean['ozet'] = strip_tags($clean['ozet']);
        }

        return $clean;
    }

    /**
     * @param  array<int, array<string, mixed>>  $alanlar
     */
    protected function syncFormAlanlari(Egitim $egitim, array $alanlar): void
    {
        $keep = [];
        $sira = 0;
        foreach ($alanlar as $row) {
            $etiket = trim((string) ($row['etiket'] ?? ''));
            if ($etiket === '') {
                continue;
            }
            $tip = (string) ($row['tip'] ?? 'text');
            $anahtar = Str::slug($etiket, '_');
            if ($anahtar === '') {
                $anahtar = 'alan_'.$sira;
            }
            $secenekler = null;
            if ($tip === 'select' && ! empty($row['secenekler'])) {
                $secenekler = collect(preg_split('/\r\n|\r|\n/', (string) $row['secenekler']))
                    ->map(fn ($l) => trim($l))
                    ->filter()
                    ->values()
                    ->all();
            }

            $payload = [
                'egitim_id' => $egitim->id,
                'etiket' => $etiket,
                'anahtar' => $anahtar,
                'tip' => $tip,
                'zorunlu_mu' => ! empty($row['zorunlu_mu']),
                'secenekler' => $secenekler,
                'placeholder' => $row['placeholder'] ?? null,
                'sira' => $sira++,
                'aktif_mi' => true,
            ];

            if (! empty($row['id'])) {
                $alan = $egitim->formAlanlari()->where('id', $row['id'])->first();
                if ($alan) {
                    $alan->update($payload);
                    $keep[] = $alan->id;
                    continue;
                }
            }

            $alan = EgitimFormAlani::create($payload);
            $keep[] = $alan->id;
        }

        $egitim->formAlanlari()->whereNotIn('id', $keep ?: [0])->delete();
    }
}
