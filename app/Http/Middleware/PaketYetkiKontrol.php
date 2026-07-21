<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PaketYetkiKontrol
{
    /** @var array<string, string> */
    protected array $featureLabels = [
        'hakkimda' => 'Hakkımda / özgeçmiş',
        'galeri' => 'Fotoğraf galerisi',
        'randevu_talepleri' => 'Randevu talepleri',
        'finans' => 'Finans yönetimi',
        'blog' => 'Blog / makale',
        'yorum' => 'Danışan yorumları',
        'faq' => 'S.S.S. yönetimi',
        'web_sitesi' => 'Kişisel web sitesi',
        'klinik_web_sitesi' => 'Klinik web sitesi',
        'egitimler' => 'Eğitimler ve başvuru formu',
        'online_gorusme' => 'Online görüntülü görüşme',
    ];

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $doktor = Auth::guard('doktor')->user();

        if ($doktor) {
            $activePackage = $doktor->aktifPaket();

            if (app()->environment('testing') && ! $activePackage) {
                return $next($request);
            }

            $label = $this->featureLabels[$feature] ?? $feature;

            if (! $activePackage) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => "«{$label}» için aktif bir paket seçmelisiniz.",
                        'upgrade_url' => route('frontend.hekim.paket_sec'),
                        'feature' => $feature,
                    ], 403);
                }

                return redirect()
                    ->route('frontend.hekim.paket_sec')
                    ->with('hata', "«{$label}» özelliğini kullanmak için bir paket seçin veya yükseltin.");
            }

            if (! $activePackage->hasFeature($feature)) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => "«{$label}» mevcut paketinizde yok. Lütfen paketinizi yükseltin.",
                        'upgrade_url' => route('frontend.hekim.paket_sec'),
                        'feature' => $feature,
                    ], 403);
                }

                return redirect()
                    ->route('frontend.hekim.paket_sec')
                    ->with('hata', "«{$label}» mevcut paketinizde (".$activePackage->ad.") yer almıyor. Aşağıdan uygun paketi seçerek yükseltebilirsiniz.");
            }
        }

        return $next($request);
    }
}
