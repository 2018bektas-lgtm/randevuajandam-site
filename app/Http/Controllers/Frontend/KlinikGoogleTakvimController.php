<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Jobs\PullGoogleBloklariJob;
use App\Services\GoogleCalendarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Throwable;

/**
 * Klinik sahibi/ortagi -> Google Takvim OAuth akisi.
 * Klinik'in birincil takvimi bagli tum hekimler icin default olur; hekim
 * kendi bagladiginda kendi baglantisi onceliklidir (bkz. Doktor::googleTakvimAyari).
 */
class KlinikGoogleTakvimController extends Controller
{
    public function __construct(protected GoogleCalendarService $service) {}

    public function baglan(Request $request): RedirectResponse
    {
        $doktor = Auth::guard('doktor')->user();
        abort_unless($doktor, 401);

        if (! $doktor->klinikteMi() || ! $doktor->klinikSahibiMi()) {
            abort(403, 'Bu islem yalnizca klinik sahibi tarafindan yapilabilir.');
        }

        if (! $this->service->enabled()) {
            return redirect()->route('hekim.klinik.ayarlar')
                ->with('error', 'Google Takvim entegrasyonu su an aktif degil.');
        }

        $state = Str::random(40);
        Session::put('gcal_oauth_state', $state);
        Session::put('gcal_oauth_guard', 'klinik');

        return redirect()->away($this->service->authUrl('klinik', $state));
    }

    public function callback(Request $request): RedirectResponse
    {
        $doktor = Auth::guard('doktor')->user();
        abort_unless($doktor, 401);
        abort_unless($doktor->klinik, 403);

        $expected = Session::pull('gcal_oauth_state');
        $guard = Session::pull('gcal_oauth_guard');

        if (! $expected || $request->query('state') !== $expected || $guard !== 'klinik') {
            return redirect()->route('hekim.klinik.ayarlar')->with('error', 'Google baglanti dogrulamasi basarisiz.');
        }

        if ($request->query('error')) {
            return redirect()->route('hekim.klinik.ayarlar')
                ->with('error', 'Google izin vermediniz: '.$request->query('error'));
        }

        $code = (string) $request->query('code');
        if ($code === '') {
            return redirect()->route('hekim.klinik.ayarlar')->with('error', 'Google code alinamadi.');
        }

        try {
            $ayar = $this->service->exchangeCode('klinik', $code);
        } catch (Throwable $e) {
            Log::warning('Google Takvim (klinik) exchange hata', ['klinik_id' => $doktor->klinik_id, 'msg' => $e->getMessage()]);
            return redirect()->route('hekim.klinik.ayarlar')->with('error', 'Google token alinamadi: '.$e->getMessage());
        }

        $klinik = $doktor->klinik;
        $klinik->forceFill([
            'google_calendar_config' => $ayar,
            'google_calendar_baglandi_at' => now(),
        ])->save();

        // Klinik altindaki her aktif hekim icin pull tetikle (fallback zinciri ile hepsi klinik config'i gorur).
        foreach ($klinik->doktorlar()->where('aktif_mi', true)->pluck('id') as $doktorId) {
            PullGoogleBloklariJob::dispatch((int) $doktorId)->afterResponse();
        }

        return redirect()->route('hekim.klinik.ayarlar')
            ->with('success', 'Klinik Google Takvim baglandi: '.($ayar['email'] ?? '-'));
    }

    public function ayir(Request $request): RedirectResponse
    {
        $doktor = Auth::guard('doktor')->user();
        abort_unless($doktor, 401);
        abort_unless($doktor->klinik && $doktor->klinikSahibiMi(), 403);

        try {
            $this->service->disconnect($doktor->klinik);
        } catch (Throwable $e) {
            Log::warning('Google Takvim (klinik) ayirma hata', ['klinik_id' => $doktor->klinik_id, 'msg' => $e->getMessage()]);
        }

        return redirect()->route('hekim.klinik.ayarlar')->with('success', 'Klinik Google Takvim baglantisi kaldirildi.');
    }
}
