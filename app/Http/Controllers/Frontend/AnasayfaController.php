<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Brans;
use App\Models\Doktor;
use App\Models\Randevu;
use App\Models\Yorum;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnasayfaController extends Controller
{
    /**
     * Ana sayfa — tüm veriler veritabanından dinamik olarak çekilir.
     */
    public function index()
    {
        // 1) Branşlar: en çok doktoru olan ilk 8 branş (doktor sayısı ile)
        $branslar = Cache::remember('anasayfa:branslar', now()->addMinutes(15), function () {
            return Brans::withCount(['doktorlar' => function ($q) {
                    $q->where('aktif_mi', true);
                }])
                ->whereHas('doktorlar', function ($q) {
                    $q->where('aktif_mi', true);
                })
                ->get()
                ->sortByDesc('doktorlar_count')
                ->take(8)
                ->values();
        });

        // 2) Öne çıkan doktorlar: aktif, platformda listelenen, en çok randevusu olan ve ortalama puanı >= 4.0 olanlar (Haftalık güncellenir)
        $oneCikanDoktorlar = Cache::remember('anasayfa:one_cikan_doktorlar', now()->addDays(7), function () {
            return Doktor::platformdaListelenen()
                ->withCount([
                    'randevular' => function ($q) {
                        $q->whereIn('durum', ['onaylandi', 'tamamlandi']);
                    },
                    'yorumlar' => function ($q) {
                        $q->onaylandi();
                    }
                ])
                ->withAvg(['yorumlar' => function ($q) {
                    $q->onaylandi();
                }], 'puan')
                ->with(['branslar', 'il', 'ilce'])
                ->get()
                ->filter(function ($doktor) {
                    $ortalamaPuan = $doktor->yorumlar_avg_puan;
                    return !is_null($ortalamaPuan) && $ortalamaPuan >= 4.0;
                })
                ->sortByDesc('randevular_count')
                ->take(6)
                ->values()
                ->map(function ($doktor) {
                    $doktor->ortalama_puan_cache = round($doktor->yorumlar_avg_puan, 1);
                    $doktor->yorum_sayisi_cache = $doktor->yorumlar_count;
                    return $doktor;
                });
        });

        // 3) Platform istatistikleri
        $istatistikler = Cache::remember('anasayfa:istatistikler', now()->addMinutes(30), function () {
            return [
                'doktor_sayisi'   => Doktor::where('aktif_mi', true)->count(),
                'randevu_sayisi'  => Randevu::whereIn('durum', ['onaylandi', 'tamamlandi'])->count(),
                'yorum_sayisi'    => Yorum::onaylandi()->count(),
                'brans_sayisi'    => Brans::count(),
            ];
        });

        // 4) Son blog yazıları (aktif, en yeniden)
        $sonBloglar = Cache::remember('anasayfa:son_bloglar', now()->addMinutes(15), function () {
            return Blog::where('aktif_mi', true)
                ->with(['doktor' => function ($q) {
                    $q->select('id', 'ad_soyad', 'unvan', 'slug', 'profil_resmi', 'il_id', 'ilce_id');
                }, 'doktor.branslar'])
                ->orderByDesc('created_at')
                ->limit(3)
                ->get();
        });

        // 5) Popüler arama etiketleri (en çok doktoru olan ilk 5 branş adı)
        $populerAramalar = $branslar->take(5)->pluck('ad');

        // 6) Son onaylanan yorumlar (testimonials)
        $sonYorumlar = Cache::remember('anasayfa:son_yorumlar', now()->addMinutes(15), function () {
            return Yorum::onaylandi()
                ->with(['hasta', 'doktor' => function ($q) {
                    $q->select('id', 'ad_soyad', 'unvan', 'slug', 'il_id', 'ilce_id');
                }, 'doktor.branslar'])
                ->where('puan', '>=', 4)
                ->orderByDesc('created_at')
                ->limit(6)
                ->get();
        });

        return view('frontend.index', compact(
            'branslar',
            'oneCikanDoktorlar',
            'istatistikler',
            'sonBloglar',
            'populerAramalar',
            'sonYorumlar'
        ));
    }
}
