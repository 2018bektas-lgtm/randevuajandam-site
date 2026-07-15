<?php

namespace App\Http\Controllers\Frontend;

use App\Events\RandevuDurumuDegisti;
use App\Http\Controllers\Controller;
use App\Models\Doktor;
use App\Models\Hasta;
use App\Services\AppointmentBookingService;
use App\Services\SlotService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use InvalidArgumentException;

class HekimRandevuController extends Controller
{
    /**
     * Display the doctor's calendar (list of appointments).
     */
    public function takvim(Request $request, SlotService $slotService)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        // 1. Determine selected date and start/end of the week
        $secilenTarih = $request->filled('tarih') ? Carbon::parse($request->tarih) : Carbon::today();
        $haftaBaslangic = $secilenTarih->copy()->startOfWeek(); // Pazartesi
        $haftaBitis = $secilenTarih->copy()->endOfWeek(); // Pazar

        // 2. Fetch all appointments of this week
        $randevular = $doktor->randevular()
            ->whereBetween('tarih', [$haftaBaslangic->toDateString(), $haftaBitis->toDateString()])
            ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi', 'iptal'])
            ->with('hasta', 'hizmet')
            ->get();

        // 3. Fetch leaves of this week
        $izinler = $doktor->izinler()
            ->where(function ($q) use ($haftaBaslangic, $haftaBitis) {
                $q->where('baslangic_zaman', '<=', $haftaBitis->toDateTimeString().' 23:59:59')
                    ->where('bitis_zaman', '>=', $haftaBaslangic->toDateTimeString().' 00:00:00');
            })->get();

        // 4. Fetch doctor working hours and settings
        $calismaSaatleri = $doktor->calismaSaatleri()->get()->keyBy('gun');
        $periyot = $slotService->getPeriyot($doktor);

        // 5. Generate daily slots list via SlotService
        $gunlukSlotlar = [];
        for ($i = 0; $i < 7; $i++) {
            $gunTarih = $haftaBaslangic->copy()->addDays($i);
            $gunIndeksi = $i + 1; // 1 = Pazartesi, 7 = Pazar
            $cs = $calismaSaatleri->get($gunIndeksi);
            $slots = $slotService->generateGunlukSlotlar($doktor, $gunTarih, $randevular, $izinler, $periyot);

            $gunlukSlotlar[$i] = [
                'tarih' => $gunTarih,
                'gun_adi' => $gunTarih->translatedFormat('l'),
                'aktif_mi' => $cs ? $cs->aktif_mi : false,
                'slotlar' => $slots,
            ];
        }

        // 6. Navigation links
        $oncekiHaftaUrl = route('hekim.randevu.takvim', ['tarih' => $haftaBaslangic->copy()->subWeek()->toDateString()]);
        $sonrakiHaftaUrl = route('hekim.randevu.takvim', ['tarih' => $haftaBaslangic->copy()->addWeek()->toDateString()]);
        $bugunUrl = route('hekim.randevu.takvim', ['tarih' => Carbon::today()->toDateString()]);

        return view('hekim.randevu.takvim', compact(
            'doktor',
            'haftaBaslangic',
            'secilenTarih',
            'gunlukSlotlar',
            'oncekiHaftaUrl',
            'sonrakiHaftaUrl',
            'bugunUrl',
            'calismaSaatleri',
            'periyot'
        ));
    }

    /**
     * Export appointments as iCalendar (.ics) for Google/Outlook.
     */
    public function ical(Request $request)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $from = now()->subMonths(1)->startOfDay();
        $to = now()->addMonths(6)->endOfDay();

        $randevular = $doktor->randevular()
            ->with(['hasta', 'hizmet'])
            ->whereBetween('tarih', [$from->toDateString(), $to->toDateString()])
            ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi'])
            ->orderBy('tarih')
            ->orderBy('saat')
            ->get();

        $periyot = (int) ($doktor->randevuAyari?->randevu_periyodu ?? 30);
        if ($periyot < 5) {
            $periyot = 30;
        }

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Randevu Ajandam//Hekim Takvim//TR',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:'. $this->icalEscape(($doktor->ad_soyad ?? 'Hekim').' Randevuları'),
        ];

        foreach ($randevular as $r) {
            $tarih = $r->tarih instanceof \DateTimeInterface
                ? $r->tarih->format('Y-m-d')
                : Carbon::parse($r->tarih)->toDateString();
            $saat = substr((string) $r->saat, 0, 8);
            if (strlen($saat) === 5) {
                $saat .= ':00';
            }
            $start = Carbon::parse($tarih.' '.$saat);
            $end = $start->copy()->addMinutes($periyot);

            $hastaAd = $r->hasta?->ad_soyad ?? 'Hasta';
            $hizmet = $r->hizmet?->ad ?? 'Randevu';
            $summary = $hizmet.' — '.$hastaAd;
            $desc = 'Durum: '.$r->durum;
            if ($r->not) {
                $desc .= '\\nNot: '.$r->not;
            }

            $uid = 'randevu-'.$r->id.'@randevuajandam';
            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:'.$uid;
            $lines[] = 'DTSTAMP:'.gmdate('Ymd\THis\Z');
            $lines[] = 'DTSTART:'.$start->format('Ymd\THis');
            $lines[] = 'DTEND:'.$end->format('Ymd\THis');
            $lines[] = 'SUMMARY:'.$this->icalEscape($summary);
            $lines[] = 'DESCRIPTION:'.$this->icalEscape($desc);
            $lines[] = 'STATUS:'.($r->durum === 'iptal' ? 'CANCELLED' : 'CONFIRMED');
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';
        $ics = implode("\r\n", $lines)."\r\n";

        $filename = 'randevular-'.Str::slug($doktor->ad_soyad ?? 'hekim').'.ics';

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    protected function icalEscape(string $text): string
    {
        $text = str_replace(["\r\n", "\n", "\r"], '\\n', $text);

        return addcslashes($text, ',;\\');
    }

    /**
     * Display pending appointment requests.
     */
    public function talepler()
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $talepler = $doktor->randevular()
            ->with('hasta', 'hizmet')
            ->where('durum', 'beklemede')
            ->orderBy('tarih', 'asc')
            ->orderBy('saat', 'asc')
            ->paginate(15);

        return view('hekim.randevu.talepler', compact('doktor', 'talepler'));
    }

    /**
     * Update appointment status.
     */
    public function durumGuncelle(Request $request, int $id)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $randevu = $doktor->randevular()->findOrFail($id);

        $request->validate([
            'durum' => 'required|in:onaylandi,iptal,tamamlandi,beklemede',
            'hekim_notu' => 'nullable|string|max:1000',
        ], [
            'durum.required' => 'Durum alanı zorunludur.',
            'durum.in' => 'Geçersiz randevu durumu.',
        ]);

        $eskiDurum = $randevu->durum;

        $randevu->update([
            'durum' => $request->durum,
            'hekim_notu' => $request->hekim_notu,
        ]);

        if ($eskiDurum !== $request->durum) {
            RandevuDurumuDegisti::dispatch($randevu, $eskiDurum, $request->durum);
        }

        $mesaj = 'Randevu durumu başarıyla güncellendi.';
        if ($request->durum === 'onaylandi') {
            $mesaj = 'Randevu başarıyla onaylandı.';
        } elseif ($request->durum === 'iptal') {
            $mesaj = 'Randevu başarıyla iptal edildi.';
        } elseif ($request->durum === 'tamamlandi') {
            $mesaj = 'Randevu tamamlandı olarak işaretlendi.';
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $mesaj,
                'randevu' => $randevu,
            ]);
        }

        return redirect()->back()->with('basarili', $mesaj);
    }

    /**
     * Display weekly working hours configuration.
     */
    public function calismaSaatleri()
    {
        return redirect()->route('hekim.randevu.ayarlar')->with('active_tab', 'calisma-saatleri');
    }

    /**
     * Update weekly working hours.
     */
    public function calismaSaatleriGuncelle(Request $request)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $request->validate([
            'saatler' => 'required|array|size:7',
            'saatler.*.gun' => 'required|integer|between:1,7',
            'saatler.*.mesai_baslangic' => 'required|date_format:H:i',
            'saatler.*.mesai_bitis' => 'required|date_format:H:i|after:saatler.*.mesai_baslangic',
            'saatler.*.ogle_baslangic' => 'nullable|date_format:H:i',
            'saatler.*.ogle_bitis' => 'nullable|date_format:H:i|after:saatler.*.ogle_baslangic',
        ], [
            'saatler.*.mesai_bitis.after' => 'Mesai bitiş saati başlangıç saatinden sonra olmalıdır.',
            'saatler.*.ogle_bitis.after' => 'Öğle arası bitiş saati başlangıç saatinden sonra olmalıdır.',
        ]);

        foreach ($request->saatler as $id => $saatData) {
            $calismaSaati = $doktor->calismaSaatleri()->findOrFail($id);

            $calismaSaati->update([
                'aktif_mi' => isset($saatData['aktif_mi']),
                'mesai_baslangic' => $saatData['mesai_baslangic'],
                'mesai_bitis' => $saatData['mesai_bitis'],
                'ogle_arasi_aktif_mi' => isset($saatData['ogle_arasi_aktif_mi']),
                'ogle_baslangic' => $saatData['ogle_baslangic'] ?: null,
                'ogle_bitis' => $saatData['ogle_bitis'] ?: null,
            ]);
        }

        return redirect()->route('hekim.randevu.ayarlar')->with([
            'basarili' => 'Çalışma saatleriniz başarıyla güncellendi.',
            'active_tab' => 'calisma-saatleri',
        ]);
    }

    /**
     * Display unique patient records list.
     */
    public function hastalar()
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        // Get unique patients that have booked appointments with this doctor
        $hastaIds = $doktor->randevular()->distinct()->pluck('hasta_id');

        $hastalar = Hasta::whereIn('id', $hastaIds)
            ->withCount(['randevular' => function ($query) use ($doktor) {
                $query->where('doktor_id', $doktor->id);
            }])
            ->orderBy('ad')
            ->orderBy('soyad')
            ->paginate(15);

        return view('hekim.randevu.hastalar', compact('doktor', 'hastalar'));
    }

    /**
     * Display appointment settings and leaves page.
     */
    public function ayarlar()
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $ayarlar = $doktor->randevuAyari;
        if (! $ayarlar) {
            $ayarlar = $doktor->randevuAyari()->create([
                'randevu_onay_tipi' => 'manuel',
                'en_erken_randevu_saati' => 2, // 2 hours early
                'en_gec_randevu_gunu' => 30, // 30 days maximum in advance
                'randevu_periyodu' => 30, // 30 mins interval
                'randevu_iptal_aktif_mi' => true,
                'iptal_saat_limiti' => 24,
                'gunluk_maksimum_randevu' => 0,
                'email_bildirimleri' => true,
                'sms_bildirimleri' => true,
                'aktif_mi' => true,
            ]);
        }

        $izinler = $doktor->izinler()->where('bitis_zaman', '>=', now())->orderBy('baslangic_zaman')->get();
        $calismaSaatleri = $doktor->calismaSaatleri()->orderBy('gun')->get();

        // If working hours do not exist, create defaults
        if ($calismaSaatleri->isEmpty()) {
            for ($gun = 1; $gun <= 7; $gun++) {
                $doktor->calismaSaatleri()->create([
                    'gun' => $gun,
                    'aktif_mi' => $gun <= 5, // Active Monday-Friday
                    'mesai_baslangic' => '09:00',
                    'mesai_bitis' => '17:00',
                    'ogle_arasi_aktif_mi' => true,
                    'ogle_baslangic' => '12:00',
                    'ogle_bitis' => '13:00',
                ]);
            }
            $calismaSaatleri = $doktor->calismaSaatleri()->orderBy('gun')->get();
        }

        return view('hekim.randevu.ayarlar', compact('doktor', 'ayarlar', 'izinler', 'calismaSaatleri'));
    }

    /**
     * Update appointment settings.
     */
    public function ayarlarGuncelle(Request $request)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $ayarlar = $doktor->randevuAyari;

        $request->validate([
            'randevu_onay_tipi' => 'required|in:manuel,otomatik',
            'en_erken_randevu_saati' => 'required|integer|min:0',
            'en_gec_randevu_gunu' => 'required|integer|min:1',
            'randevu_periyodu' => 'required|integer|in:15,20,30,45,60',
            'iptal_saat_limiti' => 'required|integer|min:0',
            'gunluk_maksimum_randevu' => 'required|integer|min:0',
        ]);

        $ayarlar->update([
            'aktif_mi' => $request->has('aktif_mi'),
            'randevu_onay_tipi' => $request->randevu_onay_tipi,
            'en_erken_randevu_saati' => $request->en_erken_randevu_saati,
            'en_gec_randevu_gunu' => $request->en_gec_randevu_gunu,
            'randevu_periyodu' => $request->randevu_periyodu,
            'randevu_iptal_aktif_mi' => $request->has('randevu_iptal_aktif_mi'),
            'iptal_saat_limiti' => $request->iptal_saat_limiti,
            'gunluk_maksimum_randevu' => $request->gunluk_maksimum_randevu,
            'email_bildirimleri' => $request->has('email_bildirimleri'),
            'sms_bildirimleri' => $request->has('sms_bildirimleri'),
        ]);

        return redirect()->route('hekim.randevu.ayarlar')->with([
            'basarili' => 'Randevu ayarlarınız başarıyla güncellendi.',
            'active_tab' => 'genel-ayarlar',
        ]);
    }

    /**
     * Add new holiday/leave.
     */
    public function izinEkle(Request $request)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $request->validate([
            'baslangic_tarih' => 'required|date|after_or_equal:today',
            'baslangic_saat' => 'required|date_format:H:i',
            'bitis_tarih' => 'required|date|after_or_equal:baslangic_tarih',
            'bitis_saat' => 'required|date_format:H:i',
            'aciklama' => 'nullable|string|max:500',
        ], [
            'baslangic_tarih.after_or_equal' => 'Başlangıç tarihi bugünden önce olmalıdır.',
            'bitis_tarih.after_or_equal' => 'Bitiş tarihi başlangıç tarihinden sonra olmalıdır.',
        ]);

        $baslangicZaman = $request->baslangic_tarih.' '.$request->baslangic_saat.':00';
        $bitisZaman = $request->bitis_tarih.' '.$request->bitis_saat.':00';

        if ($bitisZaman <= $baslangicZaman) {
            return redirect()->route('hekim.randevu.ayarlar')->withInput()->withErrors(['bitis_saat' => 'Bitiş zamanı başlangıç zamanından sonra olmalıdır.'])->with('active_tab', 'izinler-tatiller');
        }

        $doktor->izinler()->create([
            'baslangic_zaman' => $baslangicZaman,
            'bitis_zaman' => $bitisZaman,
            'aciklama' => $request->aciklama,
        ]);

        return redirect()->route('hekim.randevu.ayarlar')->with([
            'basarili' => 'İzin/Tatil dönemi başarıyla tanımlandı. Bu tarih aralığında online randevu alınamayacaktır.',
            'active_tab' => 'izinler-tatiller',
        ]);
    }

    /**
     * Delete holiday/leave.
     */
    public function izinSil(int $id)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $izin = $doktor->izinler()->findOrFail($id);

        $izin->delete();

        return redirect()->route('hekim.randevu.ayarlar')->with([
            'basarili' => 'İzin/Tatil dönemi başarıyla silindi.',
            'active_tab' => 'izinler-tatiller',
        ]);
    }

    /**
     * Get available appointment time slots for a specific date to quick lock them.
     */
    public function hizliKapatSlotlar(Request $request): JsonResponse
    {
        $request->validate([
            'tarih' => 'required|date',
        ]);

        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $tarih = Carbon::parse($request->tarih);
        $gunIndeksi = (int) $tarih->format('N'); // 1 = Pazartesi, 7 = Pazar

        $calismaSaati = $doktor->calismaSaatleri()->where('gun', $gunIndeksi)->first();
        if (! $calismaSaati || ! $calismaSaati->aktif_mi) {
            $ayarlar = $doktor->randevuAyari;
            $periyot = $ayarlar ? (int) $ayarlar->randevu_periyodu : 30;
            return response()->json([
                'aktif_mi' => false,
                'periyot' => $periyot,
                'mesaj' => 'Seçilen günde çalışma saati tanımlanmamış veya gün kapalı.',
                'slots' => [],
            ]);
        }

        $ayarlar = $doktor->randevuAyari;
        $periyot = $ayarlar ? (int) $ayarlar->randevu_periyodu : 30;
        if ($periyot <= 0) {
            $periyot = 30;
        }

        // Fetch existing appointments of this date
        $randevular = $doktor->randevular()
            ->whereDate('tarih', $tarih->toDateString())
            ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi'])
            ->get();

        // Fetch existing leaves of this date
        $izinler = $doktor->izinler()
            ->where('baslangic_zaman', '<=', $tarih->toDateString().' 23:59:59')
            ->where('bitis_zaman', '>=', $tarih->toDateString().' 00:00:00')
            ->get();

        $slots = [];
        $current = Carbon::parse($calismaSaati->mesai_baslangic);
        $end = Carbon::parse($calismaSaati->mesai_bitis);

        while ($current->lt($end)) {
            $slotStart = $current->format('H:i');
            $current = $current->addMinutes($periyot);
            $slotEnd = $current->format('H:i');

            if ($current->gt($end)) {
                break;
            }

            $slotTimeString = $slotStart;
            $slotDateTimeStr = $tarih->toDateString().' '.$slotTimeString.':00';

            // Check if slot overlaps with lunch break
            $isLunch = false;
            if ($calismaSaati->ogle_arasi_aktif_mi && $calismaSaati->ogle_baslangic && $calismaSaati->ogle_bitis) {
                $lunchStart = Carbon::parse($calismaSaati->ogle_baslangic)->format('H:i');
                $lunchEnd = Carbon::parse($calismaSaati->ogle_bitis)->format('H:i');
                if ($slotTimeString >= $lunchStart && $slotTimeString < $lunchEnd) {
                    $isLunch = true;
                }
            }

            // Check if slot overlaps with existing leaves
            $isIzin = false;
            foreach ($izinler as $izin) {
                if ($slotDateTimeStr >= $izin->baslangic_zaman->toDateTimeString() &&
                    $slotDateTimeStr < $izin->bitis_zaman->toDateTimeString()) {
                    $isIzin = true;
                    break;
                }
            }

            // Check if slot is booked
            $isDolu = $randevular->contains(function ($item) use ($slotTimeString) {
                return substr($item->saat, 0, 5) === $slotTimeString;
            });

            $slots[] = [
                'saat_baslangic' => $slotStart,
                'saat_bitis' => $slotEnd,
                'saat_string' => $slotTimeString,
                'ogle_mi' => $isLunch,
                'kapali_mi' => $isIzin,
                'dolu_mu' => $isDolu,
            ];
        }

        return response()->json([
            'aktif_mi' => true,
            'periyot' => $periyot,
            'slots' => $slots,
        ]);
    }

    /**
     * Save quick locks for specific slots on a date.
     */
    public function hizliKapatKaydet(Request $request): JsonResponse
    {
        $request->validate([
            'tarih' => 'required|date',
            'saatler' => 'nullable|array',
            'saatler.*' => 'required|date_format:H:i',
        ]);

        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $tarih = Carbon::parse($request->tarih);
        $ayarlar = $doktor->randevuAyari;
        $periyot = $ayarlar ? (int) $ayarlar->randevu_periyodu : 30;
        if ($periyot <= 0) {
            $periyot = 30;
        }

        $eklenenCount = 0;
        $silinenCount = 0;

        // Fetch existing leaves of this date for comparison
        $mevcutIzinler = $doktor->izinler()
            ->where('baslangic_zaman', '<=', $tarih->toDateString().' 23:59:59')
            ->where('bitis_zaman', '>=', $tarih->toDateString().' 00:00:00')
            ->get();

        // Identify which hours to lock and unlock
        $gonderilenSaatler = $request->input('saatler', []); // Array of selected hours e.g. ['09:00', '09:30']

        $gunIndeksi = (int) $tarih->format('N');
        $calismaSaati = $doktor->calismaSaatleri()->where('gun', $gunIndeksi)->first();

        if ($calismaSaati && $calismaSaati->aktif_mi) {
            $current = Carbon::parse($calismaSaati->mesai_baslangic);
            $end = Carbon::parse($calismaSaati->mesai_bitis);

            while ($current->lt($end)) {
                $slotStart = $current->format('H:i');
                $current = $current->addMinutes($periyot);
                $slotEnd = $current->format('H:i');

                if ($current->gt($end)) {
                    break;
                }

                $slotStartStr = $tarih->toDateString().' '.$slotStart.':00';
                $slotEndStr = $tarih->toDateString().' '.$slotEnd.':00';

                // Find matching leave in DB
                $mevcutIzin = $mevcutIzinler->first(function ($izin) use ($slotStartStr) {
                    return $izin->baslangic_zaman->toDateTimeString() === $slotStartStr;
                });

                $isSelected = in_array($slotStart, $gonderilenSaatler);

                if ($mevcutIzin && ! $isSelected) {
                    $mevcutIzin->delete();
                    $silinenCount++;
                } elseif (! $mevcutIzin && $isSelected) {
                    $doktor->izinler()->create([
                        'baslangic_zaman' => $slotStartStr,
                        'bitis_zaman' => $slotEndStr,
                        'aciklama' => 'Hızlı Randevu Kapatma',
                    ]);
                    $eklenenCount++;
                }
            }
        }

        return response()->json([
            'basarili' => true,
            'mesaj' => 'Seçilen saat dilimleri başarıyla güncellendi.',
            'eklenen' => $eklenenCount,
            'silinen' => $silinenCount,
        ]);
    }

    /**
     * Get appointments and leaves for FullCalendar.
     */
    public function takvimEvents(Request $request): JsonResponse
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
        ]);

        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
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
            $hizmetDuration = 30; // default
            if ($randevu->hizmet && $randevu->hizmet->sure) {
                $hizmetDuration = (int) $randevu->hizmet->sure;
            } elseif ($doktor->randevuAyari && $doktor->randevuAyari->randevu_periyodu) {
                $hizmetDuration = (int) $doktor->randevuAyari->randevu_periyodu;
            }

            // Ensure date column is a string or cast to string
            $tarihStr = $randevu->tarih instanceof Carbon ? $randevu->tarih->toDateString() : substr($randevu->tarih, 0, 10);
            $saatStr = strlen($randevu->saat) === 5 ? $randevu->saat.':00' : $randevu->saat;
            $startDateTime = Carbon::parse($tarihStr.' '.$saatStr);
            $endDateTime = $startDateTime->copy()->addMinutes($hizmetDuration);

            // Determine color based on status
            $color = '#C96A2B'; // beklemede (bakır)
            $textColor = '#ffffff';
            if ($randevu->durum === 'onaylandi') {
                $color = '#10B981'; // onaylandi (yeşil)
            } elseif ($randevu->durum === 'tamamlandi') {
                $color = '#3B82F6'; // tamamlandi (mavi)
            } elseif ($randevu->durum === 'iptal') {
                $color = '#EF4444'; // iptal (kırmızı)
            }

            $isOnline = $randevu->isOnline();
            $joinUrl = null;
            $canJoin = false;
            if ($isOnline && $randevu->durum === 'onaylandi') {
                try {
                    $meet = app(\App\Services\MeetingRoomService::class);
                    if (! $randevu->meeting_join_token) {
                        $randevu = $meet->ensureRoom($randevu);
                    }
                    $joinUrl = $meet->platformJoinUrl($randevu);
                    $canJoin = $meet->canJoin($randevu);
                } catch (\Throwable) {
                    //
                }
            }

            $titlePrefix = $isOnline ? '📹 ' : '';
            $events[] = [
                'id' => 'randevu_'.$randevu->id,
                'title' => $titlePrefix.$randevu->ad.' '.$randevu->soyad.' ('.($randevu->hizmet?->ad ?? 'Hizmet').')',
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
                    'gorusme_tipi' => $randevu->gorusme_tipi ?? 'yuz_yuze',
                    'platform_join_url' => $joinUrl,
                    'can_join' => $canJoin,
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

        // 3. Add lunch break background events for each working day
        $calismaSaatleri = $doktor->calismaSaatleri()->get()->keyBy('gun');
        $periyot = $doktor->randevuAyari?->randevu_periyodu ?? 30;
        if ($periyot <= 0) {
            $periyot = 30;
        }

        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            // gunIndeksi: 1=Pazartesi ... 7=Pazar (Carbon::dayOfWeekIso)
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

        foreach ($izinler as $izin) {
            $isHizliKapat = ($izin->aciklama === 'Hızlı Randevu Kapatma');

            if ($isHizliKapat) {
                $events[] = [
                    'id' => 'izin_'.$izin->id,
                    'start' => $izin->baslangic_zaman->toIso8601String(),
                    'end' => $izin->bitis_zaman->toIso8601String(),
                    'display' => 'background',
                    'backgroundColor' => '#F3F4F6', // Hafif gri arka plan (kapalı saatler)
                    'extendedProps' => [
                        'type' => 'izin',
                        'aciklama' => $izin->aciklama,
                    ],
                ];
            } else {
                $events[] = [
                    'id' => 'izin_'.$izin->id,
                    'title' => 'İzin: '.($izin->aciklama ?? 'İzin Dönemi'),
                    'start' => $izin->baslangic_zaman->toIso8601String(),
                    'end' => $izin->bitis_zaman->toIso8601String(),
                    'backgroundColor' => '#EF4444', // Kendi eklediği izinler normal kırmızı blok
                    'borderColor' => '#EF4444',
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'type' => 'izin',
                        'aciklama' => $izin->aciklama,
                    ],
                ];
            }
        }

        return response()->json($events);
    }

    /**
     * Reschedule appointment date and time via drag-and-drop.
     */
    public function reschedule(Request $request, int $id, AppointmentBookingService $bookingService): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $randevu = $doktor->randevular()->findOrFail($id);

        $request->validate([
            'tarih' => 'required|date',
            'saat' => 'required|date_format:H:i',
        ]);

        $newDateTime = Carbon::parse($request->tarih.' '.$request->saat);
        if ($newDateTime->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçmiş bir tarihe/saate randevu taşınamaz.',
            ], 422);
        }

        try {
            $bookingService->reschedule($randevu, $request->tarih, $request->saat);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Randevu tarihi ve saati başarıyla güncellendi.',
        ]);
    }

    /**
     * Store a new appointment from doctor calendar.
     */
    public function store(Request $request, AppointmentBookingService $bookingService): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        // Enforce package appointment limits (if defined)
        if ($doktor->paket && ! is_null($doktor->paket->max_randevu_sayisi)) {
            $currentAppointmentsCount = $doktor->randevular()->count();
            if ($currentAppointmentsCount >= $doktor->paket->max_randevu_sayisi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mevcut paketinizde maksimum '.$doktor->paket->max_randevu_sayisi.' randevu oluşturabilirsiniz. Lütfen paketinizi yükseltin.',
                ], 422);
            }
        }

        $request->validate([
            'hizmet_id' => 'required|exists:hizmetler,id',
            'danisan_id' => 'required|exists:hastalar,id',
            'tarih' => 'required|date',
            'saat' => 'required|date_format:H:i',
            'aciklama' => 'nullable|string|max:1000',
            'gorusme_tipi' => 'nullable|in:yuz_yuze,online',
        ]);

        $hasta = Hasta::findOrFail($request->danisan_id);

        try {
            $bookingService->create([
                'doktor' => $doktor,
                'hasta' => $hasta,
                'hizmet_id' => (int) $request->hizmet_id,
                'tarih' => Carbon::parse($request->tarih)->toDateString(),
                'saat' => $request->saat,
                'not' => $request->aciklama,
                'durum' => 'onaylandi',
                'gorusme_tipi' => ($request->input('gorusme_tipi') === 'online') ? 'online' : 'yuz_yuze',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Randevu başarıyla oluşturuldu.',
        ]);
    }

    /**
     * Update an appointment details.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $randevu = $doktor->randevular()->findOrFail($id);

        $request->validate([
            'hizmet_id' => 'required|exists:hizmetler,id',
            'aciklama' => 'nullable|string|max:1000',
        ]);

        $hizmet = $doktor->hizmetler()->where('id', $request->hizmet_id)->first();
        if (! $hizmet) {
            return response()->json([
                'success' => false,
                'message' => 'Seçilen hizmet size ait değil.',
            ], 422);
        }

        $randevu->update([
            'hizmet_id' => $hizmet->id,
            'not' => $request->aciklama,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Randevu başarıyla güncellendi.',
        ]);
    }

    /**
     * Delete/Cancel an appointment from doctor calendar.
     */
    public function destroy(int $id): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $randevu = $doktor->randevular()->findOrFail($id);

        $randevu->delete();

        return response()->json([
            'success' => true,
            'message' => 'Randevu başarıyla silindi.',
        ]);
    }

    /**
     * Search patients (hastalar) for Select2 — limited to doctor's own patients / clinic pool.
     */
    public function hastaAra(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $q = $request->get('q');
        if (empty($q) || strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $hastaIds = $doktor->randevular()
            ->whereNotNull('hasta_id')
            ->distinct()
            ->pluck('hasta_id');

        if ($doktor->klinik_id) {
            $klinikHastaIds = $doktor->klinik?->hastalar()->pluck('hastalar.id') ?? collect();
            $hastaIds = $hastaIds->merge($klinikHastaIds)->unique()->values();
        }

        if ($hastaIds->isEmpty()) {
            return response()->json(['results' => []]);
        }

        $hastalar = Hasta::query()
            ->whereIn('id', $hastaIds)
            ->where(function ($query) use ($q) {
                $query->where('ad', 'like', "%{$q}%")
                    ->orWhere('soyad', 'like', "%{$q}%")
                    ->orWhere('telefon', 'like', "%{$q}%")
                    ->orWhere('e_posta', 'like', "%{$q}%");
            })
            ->limit(20)
            ->get(['id', 'ad', 'soyad', 'e_posta']);

        $results = [];
        foreach ($hastalar as $hasta) {
            $results[] = [
                'id' => $hasta->id,
                'text' => $hasta->ad.' '.$hasta->soyad.' ('.$hasta->e_posta.')',
            ];
        }

        return response()->json(['results' => $results]);
    }

    /**
     * Create a new patient (hasta) from doctor calendar.
     */
    public function hastaEkle(Request $request): JsonResponse
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        // Enforce package patient limits (if defined)
        if ($doktor->paket && ! is_null($doktor->paket->max_hasta_sayisi)) {
            $currentPatientsCount = $doktor->randevular()->distinct('hasta_id')->count('hasta_id');
            if ($currentPatientsCount >= $doktor->paket->max_hasta_sayisi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mevcut paketinizde maksimum '.$doktor->paket->max_hasta_sayisi.' danışan ekleyebilirsiniz. Lütfen paketinizi yükseltin.',
                ], 422);
            }
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:hastalar,e_posta',
            'telefon' => 'required|string',
        ]);

        $parts = explode(' ', trim($request->name));
        $soyad = count($parts) > 1 ? array_pop($parts) : '';
        $ad = implode(' ', $parts);
        if (empty($ad)) {
            $ad = $request->name;
        }

        $tempPassword = Str::password(10);

        $hasta = Hasta::create([
            'ad' => $ad,
            'soyad' => $soyad,
            'e_posta' => $request->email,
            'telefon' => $request->telefon,
            'sifre' => $tempPassword,
            'aktif_mi' => true,
        ]);

        if ($doktor->klinik_id && $doktor->klinik) {
            $doktor->klinik->hastalar()->syncWithoutDetaching([
                $hasta->id => ['kayit_tarihi' => now()],
            ]);
        }

        return response()->json([
            'success' => true,
            'danisan' => [
                'id' => $hasta->id,
                'name' => $hasta->ad.' '.$hasta->soyad,
                'email' => $hasta->e_posta,
                'telefon' => $hasta->telefon,
            ],
            'gecici_sifre' => $tempPassword,
            'message' => 'Yeni danışan başarıyla oluşturuldu.',
        ]);
    }

    /**
     * Update doctor appointment period from calendar.
     */
    public function periyotGuncelle(Request $request)
    {
        $request->validate([
            'periyot' => 'required|integer|in:15,20,30,45,60'
        ]);

        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        if ($doktor) {
            $doktor->randevuAyari()->updateOrCreate(
                ['doktor_id' => $doktor->id],
                [
                    'randevu_periyodu' => (int)$request->periyot,
                    'randevu_onay_tipi' => 'manuel',
                    'en_erken_randevu_saati' => 2,
                    'en_gec_randevu_gunu' => 30,
                    'randevu_iptal_aktif_mi' => true,
                    'iptal_saat_limiti' => 24,
                    'gunluk_maksimum_randevu' => 0,
                    'email_bildirimleri' => true,
                    'sms_bildirimleri' => true,
                    'aktif_mi' => true,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Zaman dilimi periyodu başarıyla güncellendi.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Hekim oturumu bulunamadı.'
        ], 401);
    }
}
