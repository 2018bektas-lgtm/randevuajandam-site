<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Doktor;
use App\Models\Hizmet;
use App\Models\Klinik;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate dynamic sitemap.xml.
     */
    public function index(): Response
    {
        $urls = [];

        // 1. Static Public Pages
        $urls[] = [
            'loc' => url('/'),
            'lastmod' => now()->startOfDay()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '1.0',
        ];

        $urls[] = [
            'loc' => route('frontend.hekimler'),
            'lastmod' => now()->startOfDay()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '0.9',
        ];

        $urls[] = [
            'loc' => route('frontend.paketler'),
            'lastmod' => now()->startOfMonth()->toAtomString(),
            'changefreq' => 'monthly',
            'priority' => '0.5',
        ];

        $urls[] = [
            'loc' => route('frontend.blog.index'),
            'lastmod' => now()->startOfDay()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '0.7',
        ];

        $urls[] = [
            'loc' => route('frontend.egitimler.index'),
            'lastmod' => now()->startOfDay()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '0.75',
        ];

        // 2. Doctor Profiles
        $doktorlar = Doktor::platformdaListelenen()->with(['il', 'ilce', 'branslar'])->get();
        foreach ($doktorlar as $doktor) {
            $urls[] = [
                'loc' => $doktor->profil_url,
                'lastmod' => $doktor->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        // Clinic Profiles and subpages
        $klinikler = Klinik::where('aktif_mi', true)->with(['il', 'ilce'])->get();
        foreach ($klinikler as $klinik) {
            $ilSlug = $klinik->il?->slug ?? 'il';
            $ilceSlug = $klinik->ilce?->slug ?? 'ilce';
            
            $urls[] = [
                'loc' => route('frontend.klinik.profil', [$ilSlug, $ilceSlug, $klinik->slug]),
                'lastmod' => $klinik->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
            $urls[] = [
                'loc' => route('frontend.klinik.doktorlar', [$ilSlug, $ilceSlug, $klinik->slug]),
                'lastmod' => $klinik->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
            $urls[] = [
                'loc' => route('frontend.klinik.hizmetler', [$ilSlug, $ilceSlug, $klinik->slug]),
                'lastmod' => $klinik->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
            $urls[] = [
                'loc' => route('frontend.klinik.iletisim', [$ilSlug, $ilceSlug, $klinik->slug]),
                'lastmod' => $klinik->updated_at->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }

        // 3. Blog Posts
        $bloglar = Blog::where('aktif_mi', true)->with('doktor')->get();
        foreach ($bloglar as $blog) {
            if ($blog->doktor && $blog->doktor->isListedOnPlatform()) {
                $urls[] = [
                    'loc' => $blog->url,
                    'lastmod' => $blog->updated_at->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ];
            }
        }

        // 4. Services
        $hizmetler = Hizmet::where('aktif_mi', true)->with('doktor')->get();
        foreach ($hizmetler as $hizmet) {
            if ($hizmet->doktor && $hizmet->doktor->isListedOnPlatform()) {
                $urls[] = [
                    'loc' => $hizmet->url,
                    'lastmod' => $hizmet->updated_at->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ];
            }
        }

        // Generate XML Content
        $xml = '<'.'?xml version="1.0" encoding="UTF-8"?'.'>';
        $xml .= "\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $xml .= "\n  <url>";
            $xml .= "\n    <loc>".htmlspecialchars($url['loc']).'</loc>';
            $xml .= "\n    <lastmod>".$url['lastmod'].'</lastmod>';
            $xml .= "\n    <changefreq>".$url['changefreq'].'</changefreq>';
            $xml .= "\n    <priority>".$url['priority'].'</priority>';
            $xml .= "\n  </url>";
        }

        $xml .= "\n</urlset>";

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
