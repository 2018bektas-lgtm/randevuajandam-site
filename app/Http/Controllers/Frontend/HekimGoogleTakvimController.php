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
 * Bireysel hekim -> Google Takvim OAuth akisi.
 * Klinik altindaki hekim buraya erisemez; klinik ayari uzerinden yapmali
 * (KlinikGoogleTakvimController).
 */
class HekimGoogleTakvimController extends Controller
{
    public function __construct(protected GoogleCalendarService $service) {}

    public function baglan(Request $request): RedirectResponse
    {
        $doktor = Auth::guard('doktor')->user();
        abort_unless($doktor, 401);

        if ($doktor->klinik_id) {
            return redirect()->route('hekim.profil')
                ->with('error', 'Klinigde calisan hekim Google Takvim baglantisini klinik ayarlarindan yapar.');
        }

        if (! $this->service->enabled()) {
            return redirect()->route('hekim.profil')
                ->with('error', 'Google Takvim entegrasyonu su an aktif degil.');
        }

        $state = Str::random(40);
        Session::put('gcal_oauth_state', $state);
        Session::put('gcal_oauth_guard', 'hekim');

        return redirect()->away($this->service->authUrl('hekim', $state));
    }

    public function callback(Request $request): RedirectResponse
    {
        $doktor = Auth::guard('doktor')->user();
        abort_unless($doktor, 401);

        $expected = Session::pull('gcal_oauth_state');
        $guard = Session::pull('gcal_oauth_guard');

        if (! $expected || $request->query('state') !== $expected || $guard !== 'hekim') {
            return redirect()->route('hekim.profil')->with('error', 'Google baglanti dogrulamasi basarisiz (state).');
        }

        if ($request->query('error')) {
            return redirect()->route('hekim.profil')
                ->with('error', 'Google izin vermediniz: '.$request->query('error'));
        }

        $code = (string) $request->query('code');
        if ($code === '') {
            return redirect()->route('hekim.profil')->with('error', 'Google code alinamadi.');
        }

        try {
            $ayar = $this->service->exchangeCode('hekim', $code);
        } catch (Throwable $e) {
            Log::warning('Google Takvim exchange hata', ['doktor_id' => $doktor->id, 'msg' => $e->getMessage()]);
            return redirect()->route('hekim.profil')->with('error', 'Google token alinamadi: '.$e->getMessage());
        }

        $doktor->forceFill([
            'google_calendar_config' => $ayar,
            'google_calendar_baglandi_at' => now(),
        ])->save();

        // Ilk pull + watch kanali — arka planda
        try {
            $this->service->watchChannel($doktor);
        } catch (Throwable $e) {
            Log::warning('Google watch acilamadi', ['doktor_id' => $doktor->id, 'msg' => $e->getMessage()]);
        }
        PullGoogleBloklariJob::dispatch($doktor->id)->afterResponse();

        return redirect()->route('hekim.profil')
            ->with('success', 'Google Takvim baglandi: '.($ayar['email'] ?? '-'));
    }

    public function ayir(Request $request): RedirectResponse
    {
        $doktor = Auth::guard('doktor')->user();
        abort_unless($doktor, 401);

        try {
            $this->service->disconnect($doktor);
        } catch (Throwable $e) {
            Log::warning('Google Takvim ayirma hata', ['doktor_id' => $doktor->id, 'msg' => $e->getMessage()]);
        }

        return redirect()->route('hekim.profil')->with('success', 'Google Takvim baglantisi kaldirildi.');
    }
}
