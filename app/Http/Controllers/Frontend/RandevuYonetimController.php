<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Hasta;
use App\Models\Randevu;
use App\Services\AppointmentBookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Token-based appointment management (guest cancel + soft account claim).
 */
class RandevuYonetimController extends Controller
{
    public function goster(string $token)
    {
        $randevu = Randevu::with(['doktor', 'hizmet', 'hasta'])
            ->where('yonetim_token', $token)
            ->firstOrFail();

        return view('frontend.randevu.yonet', compact('randevu', 'token'));
    }

    /**
     * Single-appointment iCal download for guest management link.
     */
    public function ical(string $token): Response
    {
        $randevu = Randevu::with(['doktor', 'hizmet'])
            ->where('yonetim_token', $token)
            ->firstOrFail();

        if (in_array($randevu->durum, ['iptal'], true)) {
            abort(410, 'İptal edilmiş randevu takvime eklenemez.');
        }

        $doktor = $randevu->doktor;
        $periyot = (int) ($doktor?->randevuAyari?->randevu_periyodu
            ?? $randevu->hizmet?->sure
            ?? 30);
        if ($periyot < 5) {
            $periyot = 30;
        }

        $tarih = $randevu->tarih instanceof \DateTimeInterface
            ? $randevu->tarih->format('Y-m-d')
            : Carbon::parse($randevu->tarih)->toDateString();
        $saat = substr((string) $randevu->saat, 0, 8);
        if (strlen($saat) === 5) {
            $saat .= ':00';
        }
        $start = Carbon::parse($tarih.' '.$saat);
        $end = $start->copy()->addMinutes($periyot);

        $doktorIsim = trim(($doktor?->unvan ? $doktor->unvan.' ' : '').($doktor?->ad_soyad ?? 'Hekim'));
        $hizmet = $randevu->hizmet?->ad ?? 'Randevu';
        $summary = $hizmet.' — '.$doktorIsim;
        $location = (string) ($doktor?->adres ?? '');
        $desc = 'Durum: '.$randevu->durum;
        if ($randevu->yonetim_token) {
            $desc .= '\\nYönetim: '.route('frontend.randevu.yonet', $token);
        }

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Randevu Ajandam//Hasta Randevu//TR',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:randevu-'.$randevu->id.'@randevuajandam',
            'DTSTAMP:'.gmdate('Ymd\THis\Z'),
            'DTSTART:'.$start->format('Ymd\THis'),
            'DTEND:'.$end->format('Ymd\THis'),
            'SUMMARY:'.$this->icalEscape($summary),
            'DESCRIPTION:'.$this->icalEscape($desc),
            'LOCATION:'.$this->icalEscape($location),
            'STATUS:CONFIRMED',
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        $ics = implode("\r\n", $lines)."\r\n";
        $filename = 'randevu-'.Str::slug($tarih.'-'.$saat).'.ics';

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

    public function iptal(Request $request, string $token, AppointmentBookingService $bookingService)
    {
        try {
            $bookingService->cancelByToken($token);
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('hata', $e->getMessage());
        }

        return redirect()
            ->route('frontend.randevu.yonet', $token)
            ->with('basarili', 'Randevunuz iptal edildi.');
    }

    public function hesapFormu(string $token)
    {
        $randevu = Randevu::with('hasta')->where('yonetim_token', $token)->firstOrFail();
        $hasta = $randevu->hasta;

        // Already has real account (non-placeholder email) and user may login
        $placeholder = $hasta && str_contains((string) $hasta->e_posta, '@randevu.local');

        return view('frontend.randevu.hesap', compact('randevu', 'token', 'hasta', 'placeholder'));
    }

    public function hesapOlustur(Request $request, string $token)
    {
        $randevu = Randevu::with('hasta')->where('yonetim_token', $token)->firstOrFail();
        $hasta = $randevu->hasta;

        if (! $hasta) {
            return redirect()->back()->with('hata', 'Hasta kaydı bulunamadı.');
        }

        $request->validate([
            'e_posta' => ['required', 'email', 'max:255'],
            'sifre' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'e_posta.required' => 'E-posta zorunludur.',
            'sifre.required' => 'Şifre zorunludur.',
            'sifre.confirmed' => 'Şifreler uyuşmuyor.',
        ]);

        $email = $request->e_posta;
        $exists = Hasta::where('e_posta', $email)->where('id', '!=', $hasta->id)->exists();
        if ($exists) {
            return redirect()->back()->with('hata', 'Bu e-posta adresi zaten kullanılıyor. Giriş yapmayı deneyin.');
        }

        $hasta->update([
            'e_posta' => $email,
            'sifre' => $request->sifre,
            'aktif_mi' => true,
        ]);

        Auth::guard('hasta')->login($hasta);

        return redirect()
            ->route('frontend.hasta.randevular')
            ->with('basarili', 'Hesabınız oluşturuldu. Randevularınız buradan yönetebilirsiniz.');
    }
}
