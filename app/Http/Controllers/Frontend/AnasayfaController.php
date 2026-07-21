<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Brans;
use App\Models\Doktor;
use App\Models\Hizmet;
use App\Models\Klinik;
use App\Models\Randevu;
use App\Models\Yorum;
use Illuminate\Support\Facades\Cache;

class AnasayfaController extends Controller
{
    /**
     * Ana sayfa — tüm veriler veritabanından dinamik olarak çekilir.
     */
    public function index()
    {
        // Popüler arama etiketleri için branş adları
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

        // Öne çıkan doktorlar (haftalık cache)
        $oneCikanDoktorlar = Cache::remember('anasayfa:one_cikan_doktorlar', now()->addDays(7), function () {
            $list = Doktor::platformdaListelenen()
                ->withCount([
                    'randevular' => function ($q) {
                        $q->whereIn('durum', ['onaylandi', 'tamamlandi']);
                    },
                    'yorumlar' => function ($q) {
                        $q->onaylandi();
                    },
                ])
                ->withAvg(['yorumlar' => function ($q) {
                    $q->onaylandi();
                }], 'puan')
                ->with(['branslar', 'il', 'ilce'])
                ->get()
                ->filter(function ($doktor) {
                    $ortalamaPuan = $doktor->yorumlar_avg_puan;

                    return ! is_null($ortalamaPuan) && $ortalamaPuan >= 4.0;
                })
                ->sortByDesc('randevular_count')
                ->take(12)
                ->values();

            // Yeterli puanlı yoksa en aktif listelenenler
            if ($list->count() < 4) {
                $list = Doktor::platformdaListelenen()
                    ->withCount([
                        'randevular' => function ($q) {
                            $q->whereIn('durum', ['onaylandi', 'tamamlandi']);
                        },
                        'yorumlar' => function ($q) {
                            $q->onaylandi();
                        },
                    ])
                    ->withAvg(['yorumlar' => function ($q) {
                        $q->onaylandi();
                    }], 'puan')
                    ->with(['branslar', 'il', 'ilce'])
                    ->orderByDesc('id')
                    ->limit(12)
                    ->get();
            }

            return $list->map(function ($doktor) {
                $doktor->ortalama_puan_cache = $doktor->yorumlar_avg_puan
                    ? round((float) $doktor->yorumlar_avg_puan, 1)
                    : 0;
                $doktor->yorum_sayisi_cache = (int) ($doktor->yorumlar_count ?? 0);

                return $doktor;
            });
        });

        // Öne çıkan klinikler
        $oneCikanKlinikler = Cache::remember('anasayfa:one_cikan_klinikler', now()->addMinutes(30), function () {
            return Klinik::query()
                ->where('aktif_mi', true)
                ->withCount(['doktorlar' => function ($q) {
                    $q->where('aktif_mi', true);
                }])
                ->with(['il', 'ilce'])
                ->orderByDesc('doktorlar_count')
                ->orderByDesc('id')
                ->limit(12)
                ->get();
        });

        // Öne çıkan hizmetler
        $oneCikanHizmetler = Cache::remember('anasayfa:one_cikan_hizmetler_v2', now()->addMinutes(15), function () {
            return Hizmet::query()
                ->where('aktif_mi', true)
                ->whereHas('doktor', function ($q) {
                    $q->platformdaListelenen();
                })
                ->with(['doktor' => function ($q) {
                    $q->select('id', 'ad_soyad', 'unvan', 'slug', 'profil_resmi', 'il_id', 'ilce_id');
                }, 'doktor.branslar', 'doktor.il', 'doktor.ilce'])
                ->orderByDesc('id')
                ->limit(12)
                ->get();
        });

        // Platform istatistikleri
        $istatistikler = Cache::remember('anasayfa:istatistikler', now()->addMinutes(30), function () {
            return [
                'doktor_sayisi' => Doktor::platformdaListelenen()->count(),
                'randevu_sayisi' => Randevu::whereIn('durum', ['onaylandi', 'tamamlandi'])->count(),
                'yorum_sayisi' => Yorum::onaylandi()->count(),
                'brans_sayisi' => Brans::count(),
            ];
        });

        // Blog yazıları — yalnızca platformda listelenen hekimler
        $sonBloglar = Cache::remember('anasayfa:son_bloglar_v4', now()->addMinutes(15), function () {
            return Blog::where('aktif_mi', true)
                ->whereHas('doktor', function ($q) {
                    $q->platformdaListelenen();
                })
                ->with(['doktor' => function ($q) {
                    $q->select('id', 'ad_soyad', 'unvan', 'slug', 'profil_resmi', 'il_id', 'ilce_id');
                }, 'doktor.branslar', 'doktor.il', 'doktor.ilce'])
                ->orderByDesc('created_at')
                ->limit(12)
                ->get();
        });

        $populerAramalar = $branslar->take(5)->pluck('ad');

        // Yorumlar — gizli / üyeliksiz hekimler ana sayfada görünmesin
        $sonYorumlar = Cache::remember('anasayfa:son_yorumlar_v2', now()->addMinutes(15), function () {
            return Yorum::onaylandi()
                ->whereHas('doktor', function ($q) {
                    $q->platformdaListelenen();
                })
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
            'oneCikanKlinikler',
            'oneCikanHizmetler',
            'istatistikler',
            'sonBloglar',
            'populerAramalar',
            'sonYorumlar'
        ));
    }
}
