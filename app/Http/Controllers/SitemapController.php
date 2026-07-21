<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Brans;
use App\Models\Doktor;
use App\Models\Hizmet;
use App\Models\Il;
use App\Models\Klinik;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Dinamik sitemap.xml — listelenen hekim/klinik + SEO landing (il / branş) + statik sayfalar.
     */
    public function index(): Response
    {
        $xml = Cache::remember('sitemap:xml:v3', now()->addMinutes(30), function () {
            return $this->buildXml();
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=1800',
        ]);
    }

    protected function buildXml(): string
    {
        $urls = [];

        $add = function (string $loc, $lastmod = null, string $changefreq = 'weekly', string $priority = '0.7') use (&$urls) {
            if ($loc === '') {
                return;
            }
            $urls[] = [
                'loc' => $loc,
                'lastmod' => ($lastmod instanceof \DateTimeInterface
                    ? $lastmod
                    : now())->format('Y-m-d'),
                'changefreq' => $changefreq,
                'priority' => $priority,
            ];
        };

        // —— Statik yüksek öncelik ——
        $add(url('/'), now(), 'daily', '1.0');
        $add(route('frontend.hekimler'), now(), 'daily', '0.95');
        $add(route('frontend.blog.index'), now(), 'daily', '0.8');
        $add(route('frontend.egitimler.index'), now(), 'daily', '0.75');
        $add(route('frontend.paketler'), now(), 'weekly', '0.7');
        $add(route('frontend.legal.hakkimizda'), now(), 'monthly', '0.5');
        $add(route('frontend.legal.iletisim'), now(), 'monthly', '0.55');
        $add(route('frontend.legal.gizlilik'), now(), 'yearly', '0.3');
        $add(route('frontend.legal.kvkk'), now(), 'yearly', '0.3');
        $add(route('frontend.legal.kullanim'), now(), 'yearly', '0.3');
        $add(route('frontend.legal.mesafeli'), now(), 'yearly', '0.25');
        $add(route('frontend.legal.iade'), now(), 'yearly', '0.25');

        // —— İl landing (SEO: "İstanbul doktor randevu") ——
        $iller = Il::query()->orderBy('ad')->get(['id', 'ad', 'slug', 'updated_at']);
        foreach ($iller as $il) {
            if (! $il->slug) {
                continue;
            }
            $add(route('frontend.il.liste', ['il_slug' => $il->slug]), $il->updated_at ?? now(), 'weekly', '0.75');
        }

        // —— Branş landing (temiz URL: /doktorlar?brans=slug Google tarafından da taranır) ——
        $branslar = Brans::query()->orderBy('ad')->get(['id', 'ad', 'slug']);
        foreach ($branslar as $brans) {
            if (! $brans->slug) {
                continue;
            }
            $add(route('frontend.hekimler', ['brans' => $brans->slug]), now(), 'weekly', '0.72');
        }

        // —— Listelenen hekimlerin il/ilçe/branş path'leri (gerçek landing) ——
        $pathSeen = [];
        $listed = Doktor::platformdaListelenen()->with(['il', 'ilce', 'branslar'])->get();
        foreach ($listed as $d) {
            $ilSlug = $d->il?->slug;
            $ilceSlug = $d->ilce?->slug;
            if ($ilSlug && empty($pathSeen['il:'.$ilSlug])) {
                $pathSeen['il:'.$ilSlug] = true;
                $add(route('frontend.il.liste', ['il_slug' => $ilSlug]), $d->updated_at, 'weekly', '0.78');
            }
            if ($ilSlug && $ilceSlug && empty($pathSeen['ilce:'.$ilSlug.'/'.$ilceSlug])) {
                $pathSeen['ilce:'.$ilSlug.'/'.$ilceSlug] = true;
                $add(route('frontend.ilce.liste', ['il_slug' => $ilSlug, 'ilce_slug' => $ilceSlug]), $d->updated_at, 'weekly', '0.8');
            }
            foreach ($d->branslar as $b) {
                if (! $ilSlug || ! $ilceSlug || ! $b->slug) {
                    continue;
                }
                $k = 'b:'.$ilSlug.'/'.$ilceSlug.'/'.$b->slug;
                if (! empty($pathSeen[$k])) {
                    continue;
                }
                $pathSeen[$k] = true;
                $add(
                    route('frontend.brans.liste', [
                        'il_slug' => $ilSlug,
                        'ilce_slug' => $ilceSlug,
                        'brans_slug' => $b->slug,
                    ]),
                    $d->updated_at,
                    'weekly',
                    '0.82'
                );
            }
        }

        // —— Hekim profilleri ——
        $doktorlar = Doktor::platformdaListelenen()->with(['il', 'ilce', 'branslar'])->get();
        foreach ($doktorlar as $doktor) {
            if (! $doktor->profil_url) {
                continue;
            }
            $add($doktor->profil_url, $doktor->updated_at, 'weekly', '0.85');
        }

        // —— Klinik ——
        $klinikler = Klinik::where('aktif_mi', true)->with(['il', 'ilce'])->get();
        foreach ($klinikler as $klinik) {
            if (! $klinik->slug) {
                continue;
            }
            $ilSlug = $klinik->il?->slug ?? 'il';
            $ilceSlug = $klinik->ilce?->slug ?? 'ilce';
            $add(route('frontend.klinik.profil', [$ilSlug, $ilceSlug, $klinik->slug]), $klinik->updated_at, 'weekly', '0.8');
            $add(route('frontend.klinik.doktorlar', [$ilSlug, $ilceSlug, $klinik->slug]), $klinik->updated_at, 'weekly', '0.65');
            $add(route('frontend.klinik.hizmetler', [$ilSlug, $ilceSlug, $klinik->slug]), $klinik->updated_at, 'weekly', '0.65');
            $add(route('frontend.klinik.iletisim', [$ilSlug, $ilceSlug, $klinik->slug]), $klinik->updated_at, 'monthly', '0.5');
        }

        // —— Blog & hizmet (listelenen hekim) ——
        $bloglar = Blog::where('aktif_mi', true)->with(['doktor.il', 'doktor.ilce', 'doktor.branslar'])->get();
        foreach ($bloglar as $blog) {
            if ($blog->doktor && $blog->doktor->isListedOnPlatform()) {
                $add($blog->url, $blog->updated_at, 'weekly', '0.65');
            }
        }

        $hizmetler = Hizmet::where('aktif_mi', true)->with(['doktor.il', 'doktor.ilce', 'doktor.branslar'])->get();
        foreach ($hizmetler as $hizmet) {
            if ($hizmet->doktor && $hizmet->doktor->isListedOnPlatform()) {
                $add($hizmet->url, $hizmet->updated_at, 'weekly', '0.65');
            }
        }

        // Tekilleştir
        $seen = [];
        $unique = [];
        foreach ($urls as $u) {
            $key = rtrim($u['loc'], '/');
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $unique[] = $u;
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($unique as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.htmlspecialchars($url['loc'], ENT_XML1)."</loc>\n";
            $xml .= '    <lastmod>'.$url['lastmod']."</lastmod>\n";
            $xml .= '    <changefreq>'.$url['changefreq']."</changefreq>\n";
            $xml .= '    <priority>'.$url['priority']."</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return $xml;
    }
}
