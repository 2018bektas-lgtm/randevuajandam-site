<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Paket;
use App\Support\MetaPixel;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Reklam / SEO landing sayfaları (hekim & klinik).
 * Instagram vb. trafiği ana sayfa yerine buraya yönlendirin.
 */
class LandingController extends Controller
{
    /**
     * Hekim randevu yazılımı — Instagram / Google Ads landing.
     * URL: /hekim-randevu-yazilimi  (+ /hekimler-icin alias)
     */
    public function hekim(Request $request): View
    {
        // UTM / ref izleme (kayıt linkine taşınır)
        $qs = array_filter([
            'ref' => $request->query('ref') ?: $request->cookie('ra_ref'),
            'utm_source' => $request->query('utm_source'),
            'utm_medium' => $request->query('utm_medium'),
            'utm_campaign' => $request->query('utm_campaign'),
            'utm_content' => $request->query('utm_content'),
            'src' => $request->query('src', 'landing_hekim'),
        ], fn ($v) => filled($v));

        if (! empty($qs['ref']) && is_string($qs['ref']) && preg_match('/^[A-Za-z0-9]{4,16}$/', $qs['ref'])) {
            session(['ra_ref' => strtoupper($qs['ref'])]);
        }

        // Vitrin / en ucuz ücretli paket — CTA’da örnek fiyat
        $ornekPaket = Paket::query()
            ->where('tur', 'bireysel')
            ->where('aktif_mi', true)
            ->where('aylik_fiyat', '>', 0)
            ->orderBy('aylik_fiyat')
            ->first(['id', 'ad', 'aylik_fiyat', 'aylik_indirimli_fiyat', 'deneme_gun']);

        $denemeGun = (int) ($ornekPaket?->deneme_gun ?? 14);

        MetaPixel::queue('ViewContent', MetaPixel::content(
            'Hekim Landing',
            'product',
            'landing-hekim',
            null,
            'TRY',
            ['content_category' => 'landing_hekim']
        ));

        return view('frontend.landing.hekim', [
            'kayitQuery' => http_build_query($qs),
            'ornekPaket' => $ornekPaket,
            'denemeGun' => $denemeGun > 0 ? $denemeGun : 14,
        ]);
    }
}
