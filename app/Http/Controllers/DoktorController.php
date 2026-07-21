<?php

namespace App\Http\Controllers;

use App\Http\Requests\Yonetim\DoktorUpdateRequest;
use App\Models\BelgeErisimLog;
use App\Models\Doktor;
use App\Models\DoktorMezuniyetBelgesi;
use App\Models\EdevletDogrulamaLog;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Paket;
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
        $q = UyelikOdeme::query()->with(['doktor', 'paket'])->orderByDesc('id');
        if (in_array($durum, ['bekliyor', 'kesildi'], true)) {
            $q->where('fatura_durumu', $durum);
        } elseif ($durum === 'onayli_odeme') {
            $q->where('durum', 'onaylandi');
        }
        $odemeler = $q->limit(300)->get();

        return view('yonetim.faturalar', compact('yonetici', 'odemeler', 'durum'));
    }

    public function faturaDurumGuncelle(Request $request, $id)
    {
        $request->validate([
            'fatura_durumu' => ['required', 'in:bekliyor,kesildi'],
        ]);
        $odeme = UyelikOdeme::findOrFail($id);
        $odeme->update(['fatura_durumu' => $request->input('fatura_durumu')]);

        return back()->with('basarili', 'Fatura durumu güncellendi.');
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
        $doktor = Doktor::with('il', 'ilce', 'paket')->findOrFail($id);
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

        return view('yonetim.doktorlar.duzenle', compact('yonetici', 'doktor', 'paketler'));
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

        // Klinik hekimin türünü zorla bireysel yapma
        $tur = $request->input('tur', $doktor->tur);
        if (! in_array($tur, ['bireysel', 'klinik'], true)) {
            $tur = $doktor->tur ?: 'bireysel';
        }

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
        ];

        // İl seçilmediyse mevcut konum korunsun
        if (! $request->filled('il')) {
            unset($data['il_id'], $data['ilce_id']);
        }

        if ($request->filled('sifre')) {
            $data['sifre'] = Hash::make($request->sifre);
        }

        $doktor->update($data);

        return redirect()
            ->route('yonetim.doktorlar.duzenle', $doktor->id)
            ->with('basarili', 'Doktor bilgileri başarıyla güncellendi.');
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
