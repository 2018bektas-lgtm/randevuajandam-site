<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brans;
use App\Models\Doktor;
use App\Models\Egitim;
use App\Models\Il;
use App\Models\Ilce;
use App\Services\EgitimBasvuruService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;
use InvalidArgumentException;

class PublicEgitimController extends Controller
{
    public function liste(string $il_slug, string $ilce_slug, string $brans_slug, string $doctor_slug): View
    {
        $doktor = $this->resolveDoktor($il_slug, $ilce_slug, $brans_slug, $doctor_slug);
        $egitimler = $doktor->egitimler()->yayinda()->orderBy('sira')->orderByDesc('baslangic_at')->get();

        return view('frontend.hekimler.egitimler', compact('doktor', 'egitimler'));
    }

    public function detay(
        string $il_slug,
        string $ilce_slug,
        string $brans_slug,
        string $doctor_slug,
        string $egitim_slug
    ): View {
        $doktor = $this->resolveDoktor($il_slug, $ilce_slug, $brans_slug, $doctor_slug);
        $egitim = $doktor->egitimler()
            ->yayinda()
            ->where('slug', $egitim_slug)
            ->with(['formAlanlari' => fn ($q) => $q->where('aktif_mi', true)])
            ->firstOrFail();

        return view('frontend.hekimler.egitim_detay', compact('doktor', 'egitim'));
    }

    public function basvur(Request $request, EgitimBasvuruService $service): RedirectResponse
    {
        $hp = config('randevu.honeypot_field', 'website_url');
        if ($request->filled($hp)) {
            return back()->with('hata', 'Geçersiz istek.');
        }

        $throttleKey = 'egitim-basvuru:'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 8)) {
            return back()->withInput()->with('hata', 'Çok fazla istek. Lütfen sonra tekrar deneyin.');
        }

        $validated = $request->validate([
            'egitim_id' => ['required', 'exists:egitimler,id'],
            'ad' => ['required', 'string', 'max:100'],
            'soyad' => ['required', 'string', 'max:100'],
            'telefon' => ['required', 'string', 'max:40'],
            'e_posta' => ['nullable', 'email', 'max:255'],
            'kvkk_onay' => ['accepted'],
            'alan' => ['nullable', 'array'],
        ], [
            'kvkk_onay.accepted' => 'KVKK onayını işaretlemelisiniz.',
        ]);

        $egitim = Egitim::with('formAlanlari')->findOrFail($validated['egitim_id']);
        if (! $egitim->basvuruAlinabilirMi()) {
            return back()->withInput()->with('hata', 'Bu eğitime başvuru kapalı.');
        }

        // dynamic field validation
        $cevaplar = [];
        foreach ($egitim->formAlanlari->where('aktif_mi', true) as $alan) {
            $val = data_get($validated, 'alan.'.$alan->id, $request->input('alan.'.$alan->id));
            if ($alan->zorunlu_mu && ($val === null || $val === '')) {
                return back()->withInput()->with('hata', $alan->etiket.' alanı zorunludur.');
            }
            if ($alan->tip === 'select' && $val && is_array($alan->secenekler) && ! in_array($val, $alan->secenekler, true)) {
                return back()->withInput()->with('hata', $alan->etiket.' için geçersiz seçim.');
            }
            if ($alan->tip === 'checkbox') {
                $val = $request->boolean('alan.'.$alan->id);
            }
            $cevaplar[(string) $alan->id] = $val;
        }

        try {
            $service->basvur($egitim, [
                'ad' => $validated['ad'],
                'soyad' => $validated['soyad'],
                'telefon' => $validated['telefon'],
                'e_posta' => $validated['e_posta'] ?? null,
                'cevaplar' => $cevaplar,
                'kvkk_onay' => true,
                'ip' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
            ]);
        } catch (InvalidArgumentException $e) {
            RateLimiter::hit($throttleKey, 300);

            return back()->withInput()->with('hata', $e->getMessage());
        }

        RateLimiter::hit($throttleKey, 300);

        return back()->with('basarili', 'Başvurunuz alındı. Hekim sizinle iletişime geçecektir. (Ödeme siteden alınmaz.)');
    }

    protected function resolveDoktor(string $il_slug, string $ilce_slug, string $brans_slug, string $doctor_slug): Doktor
    {
        $il = Il::where('slug', $il_slug)->firstOrFail();
        $ilce = Ilce::where('il_id', $il->id)->where('slug', $ilce_slug)->firstOrFail();
        $brans = Brans::where('slug', $brans_slug)->firstOrFail();

        $doktor = Doktor::where('aktif_mi', true)
            ->where('il_id', $il->id)
            ->where('ilce_id', $ilce->id)
            ->where('slug', $doctor_slug)
            ->whereHas('branslar', fn ($q) => $q->where('branslar.id', $brans->id))
            ->with(['il', 'ilce', 'branslar'])
            ->firstOrFail();

        if (! $doktor->isListedOnPlatform()) {
            abort(404, 'Bu hekim profili platform vitrininde yayınlanmıyor.');
        }

        return $doktor;
    }
}
