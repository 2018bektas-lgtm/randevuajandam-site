<?php

namespace App\Http\Controllers;

use App\Http\Requests\Yonetim\DoktorUpdateRequest;
use App\Models\BelgeErisimLog;
use App\Models\Brans;
use App\Models\Doktor;
use App\Models\DoktorMezuniyetBelgesi;
use App\Models\EdevletDogrulamaLog;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Klinik;
use App\Models\Paket;
use App\Models\Unvan;
use App\Models\UyelikOdeme;
use App\Models\Yonetici;
use App\Notifications\MeslekBelgesiSonucBildirimi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DoktorController extends Controller
{
    /**
     * Display a listing of the doctors.
     */
    public function index(Request $request)
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();
        $query = Doktor::with('paket', 'il', 'ilce')->orderBy('id', 'desc');
        $meslekFilter = $request->query('meslek');
        if (in_array($meslekFilter, ['beklemede', 'onaylandi', 'reddedildi'], true)) {
            $query->where('meslek_dogrulama_durumu', $meslekFilter);
            if ($meslekFilter === 'beklemede') {
                $query->orderBy('created_at', 'asc');
            }
        }
        $doktorlar = $query->withCount('mezuniyetBelgeleri')->get();
        $bekleyenMeslek = Doktor::where('meslek_dogrulama_durumu', 'beklemede')->count();
        $meslekFilter = $meslekFilter ?: 'hepsi';

        return view('yonetim.doktorlar.index', compact('yonetici', 'doktorlar', 'bekleyenMeslek', 'meslekFilter'));
    }

    /**
     * Meslek belgesi inceleme kuyruğu (beklemede + belgeler).
     */
    public function meslekKuyruk()
    {
        $yonetici = Auth::guard('yonetici')->user();
        $doktorlar = Doktor::query()
            ->where('meslek_dogrulama_durumu', 'beklemede')
            ->with(['mezuniyetBelgeleri', 'paket', 'kayitPaketi'])
            ->orderBy('created_at')
            ->get();

        return view('yonetim.doktorlar.meslek_kuyruk', compact('yonetici', 'doktorlar'));
    }

    /**
     * e-Devlet doğrulama logları.
     */
    public function edevletLoglari(Request $request)
    {
        $yonetici = Auth::guard('yonetici')->user();
        $logs = EdevletDogrulamaLog::query()
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        $ozet = [
            'basarili' => EdevletDogrulamaLog::where('durum', 'basarili')->where('created_at', '>=', now()->subDay())->count(),
            'basarisiz' => EdevletDogrulamaLog::where('durum', 'basarisiz')->where('created_at', '>=', now()->subDay())->count(),
        ];

        return view('yonetim.edevlet_loglari', compact('yonetici', 'logs', 'ozet'));
    }

    /**
     * Üyelik ödemeleri fatura durumu.
     */
    public function faturalar(Request $request)
    {
        $yonetici = Auth::guard('yonetici')->user();
        $durum = $request->query('fatura', 'bekliyor');
        $q = UyelikOdeme::query()
            ->with(['doktor', 'paket'])
            ->orderByDesc('id');

        // Fatura kuyruğu: yalnızca onaylı (gerçek) ödemeler
        if (in_array($durum, ['bekliyor', 'kesildi'], true)) {
            $q->where('durum', 'onaylandi');
            if ($durum === 'bekliyor') {
                $q->where(function ($w) {
                    $w->where('fatura_durumu', 'bekliyor')->orWhereNull('fatura_durumu');
                });
            } else {
                $q->where('fatura_durumu', 'kesildi');
            }
        } elseif ($durum === 'onayli_odeme') {
            $q->where('durum', 'onaylandi');
        }

        $odemeler = $q->limit(300)->get();

        return view('yonetim.faturalar', compact('yonetici', 'odemeler', 'durum'));
    }

    public function faturaDurumGuncelle(Request $request, $id)
    {
        $odeme = UyelikOdeme::findOrFail($id);

        // Aksiyon tipini belirle (input'a göre)
        $aksiyon = $request->input('aksiyon', 'durum'); // durum | yukle | sil

        if ($aksiyon === 'sil') {
            // Yüklü faturayı sil
            if ($odeme->fatura_url) {
                $this->faturaDosyasiniSil($odeme->fatura_url);
            }
            $odeme->update([
                'fatura_url'         => null,
                'fatura_no'          => null,
                'fatura_kesildi_at'  => null,
                'fatura_durumu'      => 'bekliyor',
            ]);
            return back()->with('basarili', 'Fatura kaldırıldı; durum "bekliyor" a döndü.');
        }

        if ($aksiyon === 'yukle') {
            $data = $request->validate([
                'fatura_pdf'        => ['required', 'file', 'mimes:pdf', 'max:8192'], // 8MB
                'fatura_no'         => ['required', 'string', 'max:100'],
                'fatura_kesildi_at' => ['nullable', 'date'],
            ], [
                'fatura_pdf.required' => 'PDF dosyası seçmelisiniz.',
                'fatura_pdf.mimes'    => 'Yalnızca PDF dosyası yüklenebilir.',
                'fatura_pdf.max'      => 'Dosya en fazla 8 MB olabilir.',
                'fatura_no.required'  => 'Fatura numarası zorunludur.',
            ]);

            // Eski dosya varsa sil (değiştirme senaryosu)
            if ($odeme->fatura_url) {
                $this->faturaDosyasiniSil($odeme->fatura_url);
            }

            // Yeni dosyayı kaydet: storage/app/public/faturalar/{yil}/uyelik-{id}-{time}.pdf
            $yil = now()->year;
            $dosyaAdi = 'uyelik-'.$odeme->id.'-'.now()->timestamp.'.pdf';
            $yol = $request->file('fatura_pdf')->storeAs("public/faturalar/{$yil}", $dosyaAdi);
            // Public erişim için "storage/faturalar/..." formuna çevir
            $publicYol = str_replace('public/', 'storage/', $yol);

            $odeme->update([
                'fatura_url'        => $publicYol,
                'fatura_no'         => $data['fatura_no'],
                'fatura_kesildi_at' => $data['fatura_kesildi_at'] ?? now(),
                'fatura_durumu'     => 'kesildi',
            ]);

            return back()->with('basarili', "Fatura yüklendi (No: {$data['fatura_no']}). Hekim faturasını indirebilir.");
        }

        // Sadece durum güncelle (eski davranış)
        $request->validate([
            'fatura_durumu' => ['required', 'in:bekliyor,kesildi'],
        ]);
        $odeme->update(['fatura_durumu' => $request->input('fatura_durumu')]);

        return back()->with('basarili', 'Fatura durumu güncellendi.');
    }

    /**
     * Fatura PDF dosyasını disk'ten sil.
     */
    private function faturaDosyasiniSil(string $publicYol): void
    {
        // 'storage/faturalar/..' -> 'public/faturalar/..'
        $diskYol = str_replace('storage/', 'public/', $publicYol);
        try {
            Storage::disk('local')->delete($diskYol);
        } catch (\Throwable $e) {
            // sessizce geç, dosya zaten yoksa sorun değil
        }
    }

    /**
     * Meslek belgesi onay / red.
     */
    public function meslekDogrula(Request $request, $id)
    {
        $doktor = Doktor::findOrFail($id);
        $request->validate([
            'karar' => ['required', 'in:onaylandi,reddedildi'],
            'not' => ['nullable', 'string', 'max:500', 'required_if:karar,reddedildi'],
        ], [
            'not.required_if' => 'Reddetmeden önce gerekçe notu zorunludur.',
        ]);

        $yonetici = Auth::guard('yonetici')->user();
        $onay = $request->input('karar') === 'onaylandi';

        $doktor->forceFill([
            'meslek_dogrulama_durumu' => $onay ? 'onaylandi' : 'reddedildi',
            'meslek_dogrulama_notu' => $request->input('not'),
            'meslek_dogrulandi_at' => now(),
            'meslek_dogrulayan_yonetici_id' => $yonetici?->id,
            // Onaylanınca platform listesine izin (hekim isterse kapatır)
            'platformda_gorunur' => $onay ? true : false,
        ])->save();

        try {
            $doktor->notify(new MeslekBelgesiSonucBildirimi($onay, $request->input('not')));
        } catch (\Throwable $e) {
            Log::warning('Meslek belgesi e-posta gönderilemedi', [
                'doktor_id' => $doktor->id,
                'message' => $e->getMessage(),
            ]);
        }

        return back()->with(
            'basarili',
            $onay
                ? 'Meslek belgesi onaylandı. Hekim e-posta ile bilgilendirildi; paket seçimine geçebilir.'
                : 'Meslek belgesi reddedildi. Hekim e-posta ile bilgilendirildi.'
        );
    }

    /**
     * Meslek belgesi stream (auth: yonetici). Public URL yerine.
     */
    public function meslekBelgeGoster($id): StreamedResponse|\Illuminate\Http\Response
    {
        $doktor = Doktor::findOrFail($id);
        $path = (string) ($doktor->meslek_belge_yolu ?? '');
        if ($path === '') {
            abort(404);
        }

        BelgeErisimLog::kaydet(
            $doktor->id,
            'yonetici',
            'meslek_belgesi',
            Auth::guard('yonetici')->id()
        );

        // Private storage key: private/... or relative under storage/app
        if (str_starts_with($path, 'private/') || str_starts_with($path, 'meslek-belgeleri/')) {
            $diskPath = str_starts_with($path, 'private/') ? $path : 'private/'.$path;
            if (! Storage::disk('local')->exists($diskPath) && Storage::disk('local')->exists($path)) {
                $diskPath = $path;
            }
            if (! Storage::disk('local')->exists($diskPath)) {
                abort(404);
            }
            $mime = Storage::disk('local')->mimeType($diskPath) ?: 'application/octet-stream';

            return Storage::disk('local')->response($diskPath, basename($diskPath), [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="'.basename($diskPath).'"',
            ]);
        }

        // Legacy public paths
        $full = public_path(ltrim($path, '/'));
        if (! is_file($full)) {
            $full = public_path(ltrim(str_replace('storage/', '', $path), '/'));
            if (! is_file($full) && Storage::disk('public')->exists(str_replace('storage/', '', $path))) {
                return Storage::disk('public')->response(str_replace('storage/', '', $path));
            }
            if (! is_file($full)) {
                abort(404);
            }
        }

        return response()->file($full);
    }

    /**
     * Show the form for editing the specified doctor.
     */
    public function edit($id)
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();
        $doktor = Doktor::with('il', 'ilce', 'paket', 'klinik', 'branslar')->findOrFail($id);
        // Aktif paketler + hekimin mevcut (pasif olsa bile) paketi kaybolmasın
        $paketler = Paket::query()
            ->where(function ($q) use ($doktor) {
                $q->where('aktif_mi', true);
                if ($doktor->paket_id) {
                    $q->orWhere('id', $doktor->paket_id);
                }
            })
            ->orderBy('sira')
            ->orderBy('ad')
            ->get();

        $klinikler = Klinik::query()
            ->orderBy('ad')
            ->get(['id', 'ad', 'aktif_mi', 'sahip_doktor_id']);

        $klinikYetkiAnahtarlari = DoktorUpdateRequest::KLINIK_YETKI_ANAHTARLARI;

        // Tüm unvanlar / branşlar DB'den
        $unvanlar = Unvan::query()->orderBy('ad')->get(['id', 'ad']);
        $branslar = Brans::query()->orderBy('ad')->get(['id', 'ad']);
        $seciliBransIds = old('branslar', $doktor->branslar->pluck('id')->all());

        return view('yonetim.doktorlar.duzenle', compact(
            'yonetici',
            'doktor',
            'paketler',
            'klinikler',
            'klinikYetkiAnahtarlari',
            'unvanlar',
            'branslar',
            'seciliBransIds'
        ));
    }

    /**
     * Update the specified doctor in storage.
     */
    public function update(DoktorUpdateRequest $request, $id)
    {
        $doktor = Doktor::findOrFail($id);

        $ilModel = $request->filled('il')
            ? Il::where('ad', $request->il)->first()
            : null;
        $ilceModel = ($ilModel && $request->filled('ilce'))
            ? Ilce::where('il_id', $ilModel->id)->where('ad', $request->ilce)->first()
            : null;

        $tur = $request->input('tur', $doktor->tur);
        if (! in_array($tur, ['bireysel', 'klinik'], true)) {
            $tur = $doktor->tur ?: 'bireysel';
        }

        $bransIds = collect($request->input('branslar', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $bransIsimleri = $bransIds === []
            ? []
            : Brans::query()->whereIn('id', $bransIds)->orderBy('ad')->pluck('ad')->all();
        $uzmanlikAlani = $bransIsimleri === [] ? null : implode(', ', $bransIsimleri);

        $data = [
            'unvan' => $request->unvan,
            'ad_soyad' => $request->ad_soyad,
            'e_posta' => $request->e_posta,
            'telefon' => $request->telefon,
            'il_id' => $ilModel?->id ?? $doktor->il_id,
            'ilce_id' => $ilceModel?->id ?? ($ilModel ? null : $doktor->ilce_id),
            'tur' => $tur,
            'klinik_adi' => $request->klinik_adi,
            'paket_id' => $request->paket_id,
            'odeme_periyodu' => $request->odeme_periyodu,
            'uyelik_baslangic' => $request->uyelik_baslangic,
            'uyelik_bitis' => $request->uyelik_bitis,
            'aktif_mi' => $request->boolean('aktif_mi'),
            'platformda_gorunur' => $request->boolean('platformda_gorunur'),
            'uzmanlik_alani' => $uzmanlikAlani,
        ];

        // İl seçilmediyse mevcut konum korunsun
        if (! $request->filled('il')) {
            unset($data['il_id'], $data['ilce_id']);
        }

        if ($request->filled('sifre')) {
            $data['sifre'] = Hash::make($request->sifre);
        }

        $eskiKlinikId = $doktor->klinik_id;
        $data = array_merge($data, $this->resolveKlinikUyelikData($request, $doktor));

        $doktor->update($data);
        $doktor->branslar()->sync($bransIds);

        $this->syncKlinikSahip($doktor->fresh(), $eskiKlinikId);

        return redirect()
            ->route('yonetim.doktorlar.duzenle', $doktor->id)
            ->with('basarili', 'Doktor bilgileri başarıyla güncellendi.');
    }

    /**
     * Yönetim paneli: klinik bağlantısı / rol / yetki / komisyon.
     *
     * @return array<string, mixed>
     */
    private function resolveKlinikUyelikData(DoktorUpdateRequest $request, Doktor $doktor): array
    {
        $klinikId = $request->input('klinik_id');

        if (! $klinikId) {
            return [
                'klinik_id' => null,
                'klinik_rolu' => null,
                'klinik_yetkileri' => null,
                'klinik_aktif_mi' => null,
                'klinik_katilma_tarihi' => null,
                // DB: komisyon_orani NOT NULL default 0
                'komisyon_orani' => 0,
            ];
        }

        $rol = $request->input('klinik_rolu', 'doktor');
        if (! in_array($rol, ['doktor', 'ortak', 'sahip'], true)) {
            $rol = 'doktor';
        }

        $yetkiler = $this->buildKlinikYetkileri($request, $rol);

        $data = [
            'klinik_id' => (int) $klinikId,
            'klinik_rolu' => $rol,
            'klinik_yetkileri' => $yetkiler,
            'klinik_aktif_mi' => $request->boolean('klinik_aktif_mi'),
            'komisyon_orani' => $request->input('komisyon_orani', 0),
        ];

        // Yeni kliniğe bağlanıyorsa katılma tarihini yenile
        if ((int) $doktor->klinik_id !== (int) $klinikId || ! $doktor->klinik_katilma_tarihi) {
            $data['klinik_katilma_tarihi'] = now();
        }

        return $data;
    }

    /**
     * @return array<string, bool>
     */
    private function buildKlinikYetkileri(DoktorUpdateRequest $request, string $rol): array
    {
        $anahtarlar = DoktorUpdateRequest::KLINIK_YETKI_ANAHTARLARI;

        if (in_array($rol, ['sahip', 'ortak'], true)) {
            return array_fill_keys($anahtarlar, true);
        }

        $input = $request->input('klinik_yetkileri', []);
        $yetkiler = [];
        foreach ($anahtarlar as $key) {
            $yetkiler[$key] = ! empty($input[$key]);
        }

        return $yetkiler;
    }

    /**
     * Sahip rolü atandıysa klinik.sahip_doktor_id senkronize et.
     * sahip_doktor_id NOT NULL olduğu için ayrılmada null yazılmaz; mümkünse başka üyeye devredilir.
     */
    private function syncKlinikSahip(Doktor $doktor, ?int $eskiKlinikId): void
    {
        if ($doktor->klinik_id && $doktor->klinik_rolu === 'sahip') {
            Klinik::where('id', $doktor->klinik_id)->update([
                'sahip_doktor_id' => $doktor->id,
            ]);
        }

        // Eski klinikten ayrıldıysa ve o kliniğin sahibi kendisiyse: kalan bir üyeye devret
        if ($eskiKlinikId && (int) $eskiKlinikId !== (int) ($doktor->klinik_id ?? 0)) {
            $klinik = Klinik::find($eskiKlinikId);
            if ($klinik && (int) $klinik->sahip_doktor_id === (int) $doktor->id) {
                $yeniSahipId = Doktor::query()
                    ->where('klinik_id', $eskiKlinikId)
                    ->where('id', '!=', $doktor->id)
                    ->orderByRaw("CASE WHEN klinik_rolu = 'sahip' THEN 0 WHEN klinik_rolu = 'ortak' THEN 1 ELSE 2 END")
                    ->value('id');

                if ($yeniSahipId) {
                    $klinik->update(['sahip_doktor_id' => $yeniSahipId]);
                    Doktor::where('id', $yeniSahipId)->update(['klinik_rolu' => 'sahip']);
                }
                // Kalan üye yoksa sahip_doktor_id (NOT NULL) olduğu gibi bırakılır.
            }
        }
    }

    /**
     * Remove the specified doctor from storage.
     */
    public function destroy($id)
    {
        $doktor = Doktor::findOrFail($id);
        $doktor->delete();

        return redirect()->route('yonetim.doktorlar.index')->with('basarili', 'Doktor hesabı sistemden silindi.');
    }

    /**
     * Toggle the doctor status.
     */
    public function toggleDurum($id)
    {
        $doktor = Doktor::findOrFail($id);
        $doktor->update([
            'aktif_mi' => ! $doktor->aktif_mi,
        ]);

        return redirect()->route('yonetim.doktorlar.index')->with('basarili', 'Doktor durumu güncellendi.');
    }
}
