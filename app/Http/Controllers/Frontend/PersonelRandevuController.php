<?php

namespace App\Http\Controllers\Frontend;

use App\Events\RandevuDurumuDegisti;
use App\Http\Controllers\Controller;
use App\Models\Hasta;
use App\Models\Randevu;
use App\Services\AppointmentBookingService;
use App\Services\SlotService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PersonelRandevuController extends Controller
{
    /**
     * Display the staff calendar view with doctor selection.
     */
    public function takvim(Request $request)
    {
        $personel = Auth::guard('personel')->user();
        $klinik = $personel->klinik;
        $doktorlar = $klinik->doktorlar()->where('aktif_mi', true)->get();

        // Selected doctor
        $secilenDoktorId = $request->input('doktor_id', $doktorlar->first()?->id);
        $doktor = $doktorlar->firstWhere('id', $secilenDoktorId);

        $calismaSaatleri = collect();
        $periyot = 30;

        if ($doktor) {
            $calismaSaatleri = $doktor->calismaSaatleri()->get()->keyBy('gun');
            $periyot = $doktor->randevuAyari?->randevu_periyodu ?? 30;
            if ($periyot <= 0) {
                $periyot = 30;
            }
        }

        return view('personel.randevular.takvim', compact(
            'personel',
            'klinik',
            'doktorlar',
            'secilenDoktorId',
            'doktor',
            'calismaSaatleri',
            'periyot'
        ));
    }

    /**
     * Fetch calendar events (appointments and leaves) for the selected doctor.
     */
    public function takvimEvents(Request $request): JsonResponse
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
            'doktor_id' => 'required|exists:doktorlar,id',
        ]);

        $personel = Auth::guard('personel')->user();
        $klinik = $personel->klinik;

        // Ensure doctor is in this clinic
        $doktor = $klinik->doktorlar()->where('aktif_mi', true)->findOrFail($request->doktor_id);

        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);

        $events = [];

        // 1. Fetch appointments
        $randevular = $doktor->randevular()
            ->whereBetween('tarih', [$start->toDateString(), $end->toDateString()])
            ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi', 'iptal'])
            ->with('hasta', 'hizmet')
            ->get();

        foreach ($randevular as $randevu) {
            $hizmetDuration = 30;
            if ($randevu->hizmet && $randevu->hizmet->sure) {
                $hizmetDuration = (int) $randevu->hizmet->sure;
            } elseif ($doktor->randevuAyari && $doktor->randevuAyari->randevu_periyodu) {
                $hizmetDuration = (int) $doktor->randevuAyari->randevu_periyodu;
            }

            $tarihStr = $randevu->tarih instanceof Carbon ? $randevu->tarih->toDateString() : substr($randevu->tarih, 0, 10);
            $saatStr = strlen($randevu->saat) === 5 ? $randevu->saat.':00' : $randevu->saat;
            $startDateTime = Carbon::parse($tarihStr.' '.$saatStr);
            $endDateTime = $startDateTime->copy()->addMinutes($hizmetDuration);

            $color = '#C96A2B';
            $textColor = '#ffffff';
            if ($randevu->durum === 'onaylandi') {
                $color = '#10B981';
            } elseif ($randevu->durum === 'tamamlandi') {
                $color = '#3B82F6';
            } elseif ($randevu->durum === 'iptal') {
                $color = '#EF4444';
            }

            $events[] = [
                'id' => 'randevu_'.$randevu->id,
                'title' => $randevu->ad.' '.$randevu->soyad.' ('.($randevu->hizmet?->ad ?? 'Hizmet').')',
                'start' => $startDateTime->toIso8601String(),
                'end' => $endDateTime->toIso8601String(),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => $textColor,
                'extendedProps' => [
                    'type' => 'randevu',
                    'randevu' => $randevu,
                    'hasta_ad' => $randevu->ad.' '.$randevu->soyad,
                    'hizmet_ad' => $randevu->hizmet?->ad ?? 'Genel Hizmet',
                    'durum' => $randevu->durum,
                    'hekim_notu' => $randevu->hekim_notu,
                ],
            ];
        }

        // 2. Fetch leaves
        $izinler = $doktor->izinler()
            ->where(function ($q) use ($start, $end) {
                $q->where('baslangic_zaman', '<=', $end->toDateTimeString().' 23:59:59')
                    ->where('bitis_zaman', '>=', $start->toDateTimeString().' 00:00:00');
            })
            ->get();

        foreach ($izinler as $izin) {
            $events[] = [
                'id' => 'izin_'.$izin->id,
                'start' => $izin->baslangic_zaman->toIso8601String(),
                'end' => $izin->bitis_zaman->toIso8601String(),
                'display' => 'background',
                'backgroundColor' => '#F3F4F6',
                'extendedProps' => [
                    'type' => 'izin',
                    'aciklama' => $izin->aciklama,
                ],
            ];
        }

        // 3. Add lunch break
        $calismaSaatleri = $doktor->calismaSaatleri()->get()->keyBy('gun');
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $gunIndeksi = $cursor->dayOfWeekIso;
            $cs = $calismaSaatleri->get($gunIndeksi);

            if ($cs && $cs->aktif_mi && $cs->ogle_arasi_aktif_mi && $cs->ogle_baslangic && $cs->ogle_bitis) {
                $dateStr = $cursor->toDateString();
                $events[] = [
                    'id' => 'ogle_'.$dateStr,
                    'title' => '🍽 Öğle Arası',
                    'start' => $dateStr.'T'.Carbon::parse($cs->ogle_baslangic)->format('H:i:s'),
                    'end' => $dateStr.'T'.Carbon::parse($cs->ogle_bitis)->format('H:i:s'),
                    'display' => 'background',
                    'backgroundColor' => '#FEF9C3',
                    'extendedProps' => [
                        'type' => 'ogle',
                    ],
                ];
            }
            $cursor->addDay();
        }

        return response()->json($events);
    }

    /**
     * Fetch doctor services/slots for appointment scheduling.
     */
    public function getDoktorVeri(Request $request, SlotService $slotService): JsonResponse
    {
        $request->validate([
            'doktor_id' => 'required|exists:doktorlar,id',
            'tarih' => 'nullable|date',
        ]);

        $personel = Auth::guard('personel')->user();
        $klinik = $personel->klinik;
        $doktor = $klinik->doktorlar()->where('aktif_mi', true)->findOrFail($request->doktor_id);

        $hizmetler = $doktor->hizmetler()->where('aktif_mi', true)->get(['id', 'ad', 'fiyat', 'sure']);

        $slots = [];
        if ($request->filled('tarih')) {
            $tarih = Carbon::parse($request->tarih);
            $periyot = $slotService->getPeriyot($doktor);

            $randevular = $doktor->randevular()
                ->whereDate('tarih', $tarih->toDateString())
                ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi'])
                ->get();

            $izinler = $doktor->izinler()
                ->where('baslangic_zaman', '<=', $tarih->toDateString().' 23:59:59')
                ->where('bitis_zaman', '>=', $tarih->toDateString().' 00:00:00')
                ->get();

            $gunluk = $slotService->generateGunlukSlotlar($doktor, $tarih, $randevular, $izinler, $periyot);
            $slots = collect($gunluk)
                ->where('durum', 'bos')
                ->pluck('saat_string')
                ->values()
                ->all();
        }

        return response()->json([
            'hizmetler' => $hizmetler,
            'slots' => $slots,
        ]);
    }

    /**
     * Search patient in the shared clinic pool.
     */
    public function hastalarAra(Request $request): JsonResponse
    {
        $q = $request->input('q');
        $personel = Auth::guard('personel')->user();
        $klinik = $personel->klinik;

        $hastalar = $klinik->hastalar()
            ->where(function ($query) use ($q) {
                $query->where('ad', 'like', "%{$q}%")
                    ->orWhere('soyad', 'like', "%{$q}%")
                    ->orWhere('e_posta', 'like', "%{$q}%")
                    ->orWhere('telefon', 'like', "%{$q}%");
            })
            ->take(15)
            ->get();

        $results = [];
        foreach ($hastalar as $hasta) {
            $results[] = [
                'id' => $hasta->id,
                'text' => $hasta->ad_soyad.' ('.$hasta->telefon.')',
            ];
        }

        return response()->json(['results' => $results]);
    }

    /**
     * Store new appointment.
     */
    public function randevuKaydet(Request $request, AppointmentBookingService $bookingService): JsonResponse
    {
        $request->validate([
            'doktor_id' => 'required|exists:doktorlar,id',
            'hasta_id' => 'required|exists:hastalar,id',
            'hizmet_id' => 'required|exists:hizmetler,id',
            'tarih' => 'required|date|after_or_equal:today',
            'saat' => 'required|date_format:H:i',
            'not' => 'nullable|string|max:500',
        ]);

        $personel = Auth::guard('personel')->user();
        $klinik = $personel->klinik;

        $doktor = $klinik->doktorlar()->where('aktif_mi', true)->findOrFail($request->doktor_id);
        $hasta = $klinik->hastalar()->findOrFail($request->hasta_id);

        try {
            $bookingService->create([
                'doktor' => $doktor,
                'hasta' => $hasta,
                'hizmet_id' => (int) $request->hizmet_id,
                'tarih' => $request->tarih,
                'saat' => $request->saat,
                'not' => $request->not,
                'durum' => 'onaylandi',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'message' => 'Randevu başarıyla oluşturuldu.']);
    }

    /**
     * Reschedule appointment via Drag & Drop.
     */
    public function randevuReschedule(Request $request, $id, AppointmentBookingService $bookingService): JsonResponse
    {
        $request->validate([
            'tarih' => 'required|date|after_or_equal:today',
            'saat' => 'required|date_format:H:i',
        ]);

        $personel = Auth::guard('personel')->user();
        $klinik = $personel->klinik;

        $randevu = Randevu::whereHas('doktor', function ($q) use ($klinik) {
            $q->where('klinik_id', $klinik->id);
        })->findOrFail($id);

        try {
            $bookingService->reschedule($randevu, $request->tarih, $request->saat);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        // Reschedule does not change status — do not fire RandevuDurumuDegisti
        return response()->json(['success' => true, 'message' => 'Randevu başarıyla güncellendi.']);
    }

    /**
     * Update appointment details (not, status).
     */
    public function randevuGuncelle(Request $request, $id): JsonResponse
    {
        $request->validate([
            'not' => 'nullable|string|max:500',
            'durum' => 'required|in:beklemede,onaylandi,tamamlandi,iptal',
        ]);

        $personel = Auth::guard('personel')->user();
        $klinik = $personel->klinik;

        $randevu = Randevu::whereHas('doktor', function ($q) use ($klinik) {
            $q->where('klinik_id', $klinik->id);
        })->findOrFail($id);

        $eskiDurum = $randevu->durum;

        $randevu->update([
            'not' => $request->not,
            'durum' => $request->durum,
        ]);

        if ($eskiDurum !== $request->durum) {
            event(new RandevuDurumuDegisti($randevu, $eskiDurum, $request->durum));
        }

        return response()->json(['success' => true, 'message' => 'Randevu başarıyla güncellendi.']);
    }

    /**
     * Cancel appointment.
     */
    public function randevuIptal($id): JsonResponse
    {
        $personel = Auth::guard('personel')->user();
        $klinik = $personel->klinik;

        $randevu = Randevu::whereHas('doktor', function ($q) use ($klinik) {
            $q->where('klinik_id', $klinik->id);
        })->findOrFail($id);

        $eskiDurum = $randevu->durum;
        $randevu->update(['durum' => 'iptal']);

        if ($eskiDurum !== 'iptal') {
            event(new RandevuDurumuDegisti($randevu, $eskiDurum, 'iptal'));
        }

        return response()->json(['success' => true, 'message' => 'Randevu başarıyla iptal edildi.']);
    }

    /**
     * Display appointment requests.
     */
    public function talepler(Request $request)
    {
        $personel = Auth::guard('personel')->user();
        $klinik = $personel->klinik;
        $doktorlar = $klinik->doktorlar()->where('aktif_mi', true)->get();

        $secilenDoktorId = $request->input('doktor_id');

        $talepler = Randevu::whereIn('durum', ['beklemede'])
            ->whereHas('doktor', function ($q) use ($klinik, $secilenDoktorId) {
                $q->where('klinik_id', $klinik->id);
                if ($secilenDoktorId) {
                    $q->where('id', $secilenDoktorId);
                }
            })
            ->orderBy('tarih', 'asc')
            ->orderBy('saat', 'asc')
            ->paginate(20);

        return view('personel.randevular.talepler', compact('personel', 'klinik', 'doktorlar', 'talepler', 'secilenDoktorId'));
    }

    /**
     * Approve appointment request.
     */
    public function talepOnayla($id)
    {
        $personel = Auth::guard('personel')->user();
        $klinik = $personel->klinik;

        $randevu = Randevu::whereHas('doktor', function ($q) use ($klinik) {
            $q->where('klinik_id', $klinik->id);
        })->findOrFail($id);

        $eskiDurum = $randevu->durum;
        $randevu->update(['durum' => 'onaylandi']);

        if ($eskiDurum !== 'onaylandi') {
            event(new RandevuDurumuDegisti($randevu, $eskiDurum, 'onaylandi'));
        }

        return back()->with('basari', 'Randevu talebi onaylandı.');
    }

    /**
     * Reject/cancel appointment request.
     */
    public function talepReddet($id)
    {
        $personel = Auth::guard('personel')->user();
        $klinik = $personel->klinik;

        $randevu = Randevu::whereHas('doktor', function ($q) use ($klinik) {
            $q->where('klinik_id', $klinik->id);
        })->findOrFail($id);

        $eskiDurum = $randevu->durum;
        $randevu->update(['durum' => 'iptal']);

        if ($eskiDurum !== 'iptal') {
            event(new RandevuDurumuDegisti($randevu, $eskiDurum, 'iptal'));
        }

        return back()->with('basari', 'Randevu talebi reddedildi.');
    }

    /**
     * Create a new patient (danisan) and add to clinic pool.
     */
    public function hastaEkle(Request $request): JsonResponse
    {
        $request->validate([
            'ad_soyad' => 'required|string|max:100',
            'e_posta' => 'required|email|unique:hastalar,e_posta',
            'telefon' => 'required|string',
            'sifre' => 'nullable|string|min:6',
        ]);

        $parts = preg_split('/\s+/', trim($request->ad_soyad), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $soyad = count($parts) > 1 ? array_pop($parts) : '';
        $ad = implode(' ', $parts);
        if ($ad === '') {
            $ad = $request->ad_soyad;
        }

        $geciciSifre = $request->filled('sifre') ? $request->sifre : Str::password(10);

        $hasta = Hasta::create([
            'ad' => $ad,
            'soyad' => $soyad,
            'e_posta' => $request->e_posta,
            'telefon' => $request->telefon,
            'sifre' => $geciciSifre,
            'aktif_mi' => true,
        ]);

        $personel = Auth::guard('personel')->user();
        $personel->klinik->hastalar()->syncWithoutDetaching([$hasta->id => ['kayit_tarihi' => now()]]);

        return response()->json([
            'success' => true,
            'message' => 'Danışan başarıyla eklendi.',
            'danisan' => [
                'id' => $hasta->id,
                'name' => $hasta->ad_soyad,
                'email' => $hasta->e_posta,
                'phone' => $hasta->telefon,
            ],
            'gecici_sifre' => $request->filled('sifre') ? null : $geciciSifre,
        ]);
    }
}
