<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Hizmet;
use App\Models\Klinik;
use App\Models\Yorum;
use App\Support\MetaPixel;
use Illuminate\Http\RedirectResponse;

class KlinikProfilController extends Controller
{
    /**
     * Helper to load clinic and validate slugs (performs 301 redirect if necessary).
     */
    protected function getKlinikAndValidate(string $il_slug, string $ilce_slug, string $klinik_slug)
    {
        $klinik = Klinik::where('slug', $klinik_slug)->where('aktif_mi', true)->firstOrFail();

        $correctIl = $klinik->il?->slug ?? 'il';
        $correctIlce = $klinik->ilce?->slug ?? 'ilce';

        if ($il_slug !== $correctIl || $ilce_slug !== $correctIlce) {
            $routeName = request()->route()->getName();

            return redirect()->route($routeName, [
                'il_slug' => $correctIl,
                'ilce_slug' => $correctIlce,
                'klinik_slug' => $klinik_slug,
            ], 301);
        }

        return $klinik;
    }

    /**
     * Show clinic public profile dashboard.
     */
    public function profil($il_slug, $ilce_slug, $klinik_slug)
    {
        $response = $this->getKlinikAndValidate($il_slug, $ilce_slug, $klinik_slug);
        if ($response instanceof RedirectResponse) {
            return $response;
        }
        $klinik = $response;

        $doktorlar = $klinik->doktorlar()->where('aktif_mi', true)->get();
        $doktorIds = $doktorlar->pluck('id');

        $hizmetler = Hizmet::whereIn('doktor_id', $doktorIds)
            ->where('aktif_mi', true)
            ->get()
            ->unique('ad');

        $yorumlar = Yorum::whereIn('doktor_id', $doktorIds)
            ->where('aktif_mi', true)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Calculate average rating
        $ortalamaPuan = 0.0;
        if ($yorumlar->isNotEmpty()) {
            $ortalamaPuan = round($yorumlar->avg('puan'), 1);
        }

        MetaPixel::queue('ViewContent', MetaPixel::content(
            (string) $klinik->ad,
            'product',
            'klinik-'.$klinik->id,
            null,
            'TRY',
            ['content_category' => 'klinik']
        ));

        return view('frontend.klinik.profil', compact('klinik', 'doktorlar', 'hizmetler', 'yorumlar', 'ortalamaPuan'));
    }

    /**
     * Show clinic doctors kadrosu.
     */
    public function doktorlar($il_slug, $ilce_slug, $klinik_slug)
    {
        $response = $this->getKlinikAndValidate($il_slug, $ilce_slug, $klinik_slug);
        if ($response instanceof RedirectResponse) {
            return $response;
        }
        $klinik = $response;

        $doktorlar = $klinik->doktorlar()->where('aktif_mi', true)->get();

        return view('frontend.klinik.doktorlar', compact('klinik', 'doktorlar'));
    }

    /**
     * Show clinic services list.
     */
    public function hizmetler($il_slug, $ilce_slug, $klinik_slug)
    {
        $response = $this->getKlinikAndValidate($il_slug, $ilce_slug, $klinik_slug);
        if ($response instanceof RedirectResponse) {
            return $response;
        }
        $klinik = $response;

        $doktorlar = $klinik->doktorlar()->where('aktif_mi', true)->get();
        $hizmetler = Hizmet::whereIn('doktor_id', $doktorlar->pluck('id'))
            ->where('aktif_mi', true)
            ->with('doktor')
            ->get();

        return view('frontend.klinik.hizmetler', compact('klinik', 'hizmetler'));
    }

    /**
     * Show clinic contact page.
     */
    public function iletisim($il_slug, $ilce_slug, $klinik_slug)
    {
        $response = $this->getKlinikAndValidate($il_slug, $ilce_slug, $klinik_slug);
        if ($response instanceof RedirectResponse) {
            return $response;
        }
        $klinik = $response;

        return view('frontend.klinik.iletisim', compact('klinik'));
    }
}
