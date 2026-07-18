<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Hasta yorumları yalnızca platform yönetimi tarafından moderasyon edilir.
 * Hekim paneli üzerinden görüntüleme / yanıt / onay kapalıdır (adil puanlama).
 */
class HekimYorumController extends Controller
{
    public function index(Request $request)
    {
        return redirect()
            ->route('hekim.panel')
            ->with('basarili', 'Hasta yorumları platform yönetimi tarafından bağımsız denetlenir. Hekim panelinde yorum listesi veya onay yetkisi bulunmaz; onaylanan yorumlar yalnızca herkese açık profilde görünür.');
    }

    public function yanitla(Request $request, int $id)
    {
        return redirect()
            ->route('hekim.panel')
            ->with('basarili', 'Yorum yanıtı ve onayı hekim paneline kapalıdır. Moderasyon yalnızca site yöneticisi tarafından yapılır.');
    }
}
