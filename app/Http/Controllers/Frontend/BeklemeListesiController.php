<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BeklemeListesi;
use App\Models\Doktor;
use App\Services\BeklemeListesiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use InvalidArgumentException;

class BeklemeListesiController extends Controller
{
    /**
     * Public join waitlist (guest or logged-in patient).
     */
    public function katil(Request $request, BeklemeListesiService $service)
    {
        $hp = config('randevu.honeypot_field', 'website_url');
        if ($request->filled($hp)) {
            return redirect()->back()->with('hata', 'Geçersiz istek.');
        }

        $throttleKey = 'bekleme-listesi:'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 8)) {
            return redirect()->back()->withInput()->with('hata', 'Çok fazla istek. Lütfen biraz sonra tekrar deneyin.');
        }

        $hasta = Auth::guard('hasta')->user();

        $rules = [
            'doktor_id' => ['required', 'exists:doktorlar,id'],
            'hizmet_id' => ['nullable', 'exists:hizmetler,id'],
            'tercih_tarih' => ['nullable', 'date', 'after_or_equal:today'],
            'tercih_saat' => ['nullable', 'date_format:H:i'],
            'not' => ['nullable', 'string', 'max:1000'],
            'kvkk_onay' => ['accepted'],
        ];

        if ($hasta) {
            $rules['ad'] = ['nullable', 'string', 'max:100'];
            $rules['soyad'] = ['nullable', 'string', 'max:100'];
            $rules['telefon'] = ['nullable', 'string', 'max:30'];
            $rules['e_posta'] = ['nullable', 'email', 'max:255'];
        } else {
            $rules['ad'] = ['required', 'string', 'max:100'];
            $rules['soyad'] = ['required', 'string', 'max:100'];
            $rules['telefon'] = ['required', 'string', 'max:30'];
            $rules['e_posta'] = ['nullable', 'email', 'max:255'];
        }

        $validated = $request->validate($rules, [
            'kvkk_onay.accepted' => 'KVKK onayını işaretlemelisiniz.',
            'ad.required' => 'Ad zorunludur.',
            'soyad.required' => 'Soyad zorunludur.',
            'telefon.required' => 'Telefon zorunludur.',
        ]);

        $doktor = Doktor::findOrFail($validated['doktor_id']);

        $payload = [
            'ad' => $validated['ad'] ?? $hasta?->ad ?? explode(' ', (string) $hasta?->ad_soyad, 2)[0] ?? 'Hasta',
            'soyad' => $validated['soyad'] ?? $hasta?->soyad ?? (explode(' ', (string) $hasta?->ad_soyad, 2)[1] ?? ''),
            'telefon' => $validated['telefon'] ?? $hasta?->telefon ?? '',
            'e_posta' => $validated['e_posta'] ?? $hasta?->e_posta,
            'hizmet_id' => $validated['hizmet_id'] ?? null,
            'tercih_tarih' => $validated['tercih_tarih'] ?? null,
            'tercih_saat' => $validated['tercih_saat'] ?? null,
            'not' => $validated['not'] ?? null,
            'hasta' => $hasta,
        ];

        if (empty($payload['telefon'])) {
            return redirect()->back()->withInput()->with('hata', 'Telefon numarası zorunludur.');
        }

        try {
            $service->join($doktor, $payload);
        } catch (InvalidArgumentException $e) {
            RateLimiter::hit($throttleKey, 300);

            return redirect()->back()->withInput()->with('hata', $e->getMessage());
        }

        RateLimiter::hit($throttleKey, 300);

        return redirect()->back()->with(
            'basarili',
            'Bekleme listesine eklendiniz. Uygun bir randevu açıldığında bilgilendirileceksiniz.'
        );
    }

    /**
     * Doctor panel: list waitlist entries.
     */
    public function index(Request $request)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $durum = $request->get('durum', 'aktif');
        $query = BeklemeListesi::query()
            ->where('doktor_id', $doktor->id)
            ->with(['hizmet', 'hasta'])
            ->orderByDesc('created_at');

        if ($durum === 'aktif') {
            $query->aktif();
        } elseif (in_array($durum, ['beklemede', 'bildirildi', 'randevu_alindi', 'iptal'], true)) {
            $query->where('durum', $durum);
        }

        $kayitlar = $query->paginate(20)->withQueryString();
        $bekleyenSayisi = BeklemeListesi::where('doktor_id', $doktor->id)->beklemede()->count();

        return view('hekim.randevu.bekleme_listesi', compact('kayitlar', 'durum', 'bekleyenSayisi'));
    }

    public function durumGuncelle(Request $request, int $id)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $data = $request->validate([
            'durum' => ['required', 'in:beklemede,bildirildi,randevu_alindi,iptal'],
        ]);

        $kayit = BeklemeListesi::where('doktor_id', $doktor->id)->findOrFail($id);
        $updates = ['durum' => $data['durum']];
        if ($data['durum'] === 'bildirildi' && ! $kayit->bildirildi_at) {
            $updates['bildirildi_at'] = now();
        }
        $kayit->update($updates);

        return redirect()->back()->with('basarili', 'Kayıt durumu güncellendi.');
    }

    public function sil(int $id)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $kayit = BeklemeListesi::where('doktor_id', $doktor->id)->findOrFail($id);
        $kayit->delete();

        return redirect()->back()->with('basarili', 'Bekleme listesi kaydı silindi.');
    }

    public function bildir(int $id, BeklemeListesiService $service)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $kayit = BeklemeListesi::where('doktor_id', $doktor->id)->with('doktor')->findOrFail($id);

        $service->notifyKayit($kayit);
        $kayit->update([
            'durum' => 'bildirildi',
            'bildirildi_at' => now(),
        ]);

        return redirect()->back()->with('basarili', 'Hasta bilgilendirildi (e-posta varsa kuyruğa alındı).');
    }
}
