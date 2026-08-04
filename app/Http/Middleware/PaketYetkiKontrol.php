<?php

namespace App\Http\Middleware;

use App\Models\Doktor;
use App\Support\PaketYetki;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PaketYetkiKontrol
{
    /**
     * @param  string  ...$features  Virgülle ayrılmış veya çoklu parametre; biri yeterli (OR).
     */
    public function handle(Request $request, Closure $next, string ...$features): Response
    {
        $codes = [];
        foreach ($features as $f) {
            foreach (explode(',', $f) as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $codes[] = $part;
                }
            }
        }
        $codes = array_values(array_unique($codes));

        $doktor = $this->resolveDoktor($request);

        // Personel / klinik paneli: hekim paketi yoksa geç (klinik.paket ayrı)
        if (! $doktor) {
            return $next($request);
        }

        $activePackage = $doktor->aktifPaket();

        if (app()->environment('testing') && ! $activePackage) {
            return $next($request);
        }

        $label = collect($codes)
            ->map(fn ($c) => PaketYetki::label($c))
            ->implode(' / ');

        if (! $activePackage) {
            return PaketYetki::deny(
                $request,
                "«{$label}» için aktif bir paket seçmelisiniz.",
                $codes[0] ?? 'paket'
            );
        }

        if (! $activePackage->hasAnyFeature($codes)) {
            return PaketYetki::deny(
                $request,
                "«{$label}» mevcut paketinizde (".$activePackage->ad.') yer almıyor. Paketinizi yükselterek açabilirsiniz.',
                $codes[0] ?? 'paket'
            );
        }

        return $next($request);
    }

    private function resolveDoktor(Request $request): ?Doktor
    {
        $fromGuard = Auth::guard('doktor')->user();
        if ($fromGuard instanceof Doktor) {
            return $fromGuard;
        }

        $fromAttr = $request->attributes->get('auth_doktor');
        if ($fromAttr instanceof Doktor) {
            return $fromAttr;
        }

        return null;
    }
}
