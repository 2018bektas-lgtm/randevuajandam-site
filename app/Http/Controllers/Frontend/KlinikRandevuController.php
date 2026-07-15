<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Randevu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KlinikRandevuController extends Controller
{
    /**
     * Show the clinic calendar view.
     */
    public function takvim()
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;
        if (!$klinik) {
            return redirect()->route('hekim.panel')->with('hata', 'Kliniğiniz bulunamadı.');
        }

        $doktorlar = $klinik->doktorlar()
            ->where('aktif_mi', true)
            ->where('klinik_aktif_mi', true)
            ->get();

        return view('klinik.randevular.takvim', compact('klinik', 'doktorlar'));
    }

    /**
     * Fetch calendar events for FullCalendar JSON endpoint.
     */
    public function takvimEvents(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;
        if (!$klinik) {
            return response()->json(['error' => 'Klinik bulunamadı.'], 403);
        }

        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
        ]);

        $doktorIds = $klinik->doktorlar()->pluck('id')->toArray();

        // Handle filtering by doctor
        if ($request->has('doktor_ids') && is_array($request->doktor_ids)) {
            $filteredIds = array_map('intval', $request->doktor_ids);
            $doktorIds = array_intersect($doktorIds, $filteredIds);
        }

        $randevular = Randevu::whereIn('doktor_id', $doktorIds)
            ->whereBetween('tarih', [
                \Carbon\Carbon::parse($request->start)->toDateString(),
                \Carbon\Carbon::parse($request->end)->toDateString(),
            ])
            ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi'])
            ->with(['doktor', 'hasta', 'hizmet'])
            ->get();

        $events = [];
        foreach ($randevular as $randevu) {
            // Generate distinct HSL color based on doctor ID to differentiate
            $colorHue = ($randevu->doktor_id * 67) % 360;
            $color = "hsl({$colorHue}, 65%, 45%)";
            $bgColor = "hsl({$colorHue}, 65%, 95%)";
            $borderColor = "hsl({$colorHue}, 65%, 45%)";

            $durumText = 'Beklemede';
            if ($randevu->durum === 'onaylandi') {
                $durumText = 'Onaylandı';
            } elseif ($randevu->durum === 'tamamlandi') {
                $durumText = 'Tamamlandı';
            }

            $events[] = [
                'id' => $randevu->id,
                'title' => ($randevu->doktor->unvan ? $randevu->doktor->unvan . ' ' : '') . $randevu->doktor->ad_soyad . ' - ' . ($randevu->hasta->ad_soyad ?? $randevu->ad),
                'start' => $randevu->tarih->format('Y-m-d') . 'T' . $randevu->saat,
                'end' => $randevu->tarih->format('Y-m-d') . 'T' . \Carbon\Carbon::parse($randevu->saat)->addMinutes($randevu->hizmet->sure ?? 30)->format('H:i:s'),
                'backgroundColor' => $bgColor,
                'borderColor' => $borderColor,
                'textColor' => $color,
                'extendedProps' => [
                    'doktor' => ($randevu->doktor->unvan ? $randevu->doktor->unvan . ' ' : '') . $randevu->doktor->ad_soyad,
                    'hasta' => $randevu->hasta->ad_soyad ?? $randevu->ad,
                    'telefon' => $randevu->hasta->telefon ?? $randevu->telefon,
                    'hizmet' => $randevu->hizmet->ad ?? 'Genel Randevu',
                    'sure' => ($randevu->hizmet->sure ?? 30) . ' dk',
                    'ucret' => '₺' . number_format($randevu->ucret ?? 0, 2, ',', '.'),
                    'durum' => $durumText,
                    'gorusme_tipi' => $randevu->gorusme_tipi ?? 'yuz_yuze',
                    'platform_join_url' => ($randevu->isOnline() && $randevu->durum === 'onaylandi')
                        ? $randevu->platformJoinUrl()
                        : null,
                ],
            ];
        }

        return response()->json($events);
    }

    /**
     * Show pending appointment requests across the clinic.
     */
    public function talepler(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;
        if (!$klinik) {
            return redirect()->route('hekim.panel')->with('hata', 'Kliniğiniz bulunamadı.');
        }

        $doktorlar = $klinik->doktorlar()
            ->where('aktif_mi', true)
            ->where('klinik_aktif_mi', true)
            ->get();

        $doktorIds = $doktorlar->pluck('id')->toArray();

        $seciliDoktorId = $request->input('doktor_id');
        $seciliTarih = $request->input('tarih');

        if (!empty($seciliDoktorId) && in_array((int)$seciliDoktorId, $doktorIds)) {
            $doktorIds = [(int)$seciliDoktorId];
        }

        $query = Randevu::whereIn('doktor_id', $doktorIds)
            ->where('durum', 'beklemede')
            ->with(['doktor', 'hasta', 'hizmet']);

        if (!empty($seciliTarih)) {
            $query->whereDate('tarih', $seciliTarih);
        }

        $talepler = $query->orderBy('tarih')->orderBy('saat')->paginate(20);

        return view('klinik.randevular.talepler', compact('klinik', 'doktorlar', 'talepler', 'seciliDoktorId', 'seciliTarih'));
    }

    /**
     * Approve selected pending appointments.
     */
    public function topluOnay(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;
        if (!$klinik) {
            return response()->json(['success' => false, 'message' => 'Yetkisiz erişim.'], 403);
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        $doktorIds = $klinik->doktorlar()->pluck('id')->toArray();

        $randevular = Randevu::whereIn('id', $request->ids)
            ->whereIn('doktor_id', $doktorIds)
            ->where('durum', 'beklemede')
            ->get();

        if ($randevular->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Onaylanacak uygun randevu bulunamadı.']);
        }

        foreach ($randevular as $randevu) {
            $randevu->update(['durum' => 'onaylandi']);
            // Dispatch events if needed: event(new RandevuOnaylandi($randevu));
        }

        return response()->json([
            'success' => true,
            'message' => count($randevular) . ' adet randevu talebi onaylandı.',
        ]);
    }

    /**
     * Cancel / Reject selected pending appointments.
     */
    public function topluRed(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;
        if (!$klinik) {
            return response()->json(['success' => false, 'message' => 'Yetkisiz erişim.'], 403);
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        $doktorIds = $klinik->doktorlar()->pluck('id')->toArray();

        $randevular = Randevu::whereIn('id', $request->ids)
            ->whereIn('doktor_id', $doktorIds)
            ->where('durum', 'beklemede')
            ->get();

        if ($randevular->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Reddedilecek uygun randevu bulunamadı.']);
        }

        foreach ($randevular as $randevu) {
            $randevu->update(['durum' => 'iptal']);
        }

        return response()->json([
            'success' => true,
            'message' => count($randevular) . ' adet randevu talebi reddedildi.',
        ]);
    }
}
