<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\AsistanService;
use App\Support\PaketYetki;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HekimAsistanController extends Controller
{
    public function __construct(protected AsistanService $asistanService) {}

    public function mesaj(Request $request): JsonResponse
    {
        $request->validate([
            'mesaj'              => 'sometimes|string|max:500',
            'gecmis'             => 'sometimes|array|max:20',
            'onay'               => 'sometimes|array',
            'onay.fonksiyon'     => 'required_with:onay|string',
            'onay.parametreler'  => 'required_with:onay|array',
            'secim'              => 'sometimes|string|in:sadece_kapat,kapat_ve_iptal,kapat_iptal_sms,kapat_bekleme,vazgec',
            'secim_parametreler' => 'required_with:secim|array',
        ]);

        $doktor   = Auth::guard('doktor')->user();
        $doktorId = $doktor->id;

        if (! PaketYetki::has($doktor, 'ai_asistan')) {
            return response()->json(['yanit' => 'AI Asistan özelliği Profesyonel ve üzeri paketlerde kullanılabilir.', 'onay_gerekli' => null], 403);
        }

        // Confirmed write operation
        if ($request->filled('onay')) {
            $onay  = $request->input('onay');
            $sonuc = $this->asistanService->onayliIsleCalistir(
                $doktorId,
                $onay['fonksiyon'],
                $onay['parametreler'],
            );
            return response()->json(['yanit' => $sonuc['yanit'], 'onay_gerekli' => null]);
        }

        // Multi-choice selection (takvim_kapat conflict resolution)
        if ($request->filled('secim')) {
            $secim  = $request->input('secim');
            $params = (array) $request->input('secim_parametreler', []);

            if ($secim === 'vazgec') {
                return response()->json(['yanit' => 'İptal edildi.', 'onay_gerekli' => null]);
            }

            $fonksiyon = match ($secim) {
                'kapat_ve_iptal',
                'kapat_iptal_sms',
                'kapat_bekleme'  => 'takvim_kapat_ve_iptal',
                default          => 'takvim_kapat',
            };
            // SMS ve bekleme listesi ekstra işlemleri servis içinde ele alınır
            $params['_secim'] = $secim;
            $sonuc = $this->asistanService->onayliIsleCalistir($doktorId, $fonksiyon, $params);
            return response()->json(['yanit' => $sonuc['yanit'], 'onay_gerekli' => null]);
        }

        $mesaj  = trim((string) $request->input('mesaj', ''));
        if ($mesaj === '') {
            return response()->json(['yanit' => 'Mesajınızı yazın.', 'onay_gerekli' => null]);
        }

        $gecmis = (array) $request->input('gecmis', []);
        $sonuc  = $this->asistanService->isle($doktorId, $mesaj, $gecmis);

        return response()->json([
            'yanit'         => $sonuc['yanit'],
            'onay_gerekli'  => $sonuc['onay_gerekli']  ?? null,
            'secim_gerekli' => $sonuc['secim_gerekli'] ?? null,
        ]);
    }
}
