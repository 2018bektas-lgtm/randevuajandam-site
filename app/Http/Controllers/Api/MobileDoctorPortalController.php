<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\BeklemeListesi;
use App\Models\Brans;
use App\Models\Doktor;
use App\Models\Hasta;
use App\Models\HekimWebSitesi;
use App\Services\BeklemeListesiService;
use App\Services\HtmlSanitizer;
use App\Services\TwoFactorService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PragmaRX\Google2FA\Google2FA;

/**
 * Remaining hekim-panel modules for the doctor mobile app.
 * Auth / appointments / services / working-hours live in MobileDoctorController.
 */
class MobileDoctorPortalController extends Controller
{
    private function doktor(Request $request): Doktor
    {
        /** @var Doktor $doktor */
        $doktor = $request->attributes->get('auth_doktor');

        return $doktor;
    }

    // ── Dashboard ──────────────────────────────────────────────

    public function dashboard(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $bugun = Carbon::today()->toDateString();
        $haftaBaslangic = Carbon::today()->startOfWeek(Carbon::MONDAY)->toDateString();
        $haftaBitis = Carbon::today()->endOfWeek(Carbon::SUNDAY)->toDateString();
        $simdiSaat = Carbon::now()->format('H:i:s');

        $bugunAktif = $doktor->randevular()
            ->whereDate('tarih', $bugun)
            ->whereIn('durum', ['beklemede', 'onaylandi'])
            ->count();

        $bugunTamamlanan = $doktor->randevular()
            ->whereDate('tarih', $bugun)
            ->where('durum', 'tamamlandi')
            ->count();

        $bugunIptal = $doktor->randevular()
            ->whereDate('tarih', $bugun)
            ->where('durum', 'iptal')
            ->count();

        $haftaRandevu = $doktor->randevular()
            ->whereBetween('tarih', [$haftaBaslangic, $haftaBitis])
            ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi'])
            ->count();

        $sonraki = $doktor->randevular()
            ->with(['hasta:id,ad,soyad,telefon', 'hizmet:id,ad,sure'])
            ->where(function ($q) use ($bugun, $simdiSaat) {
                $q->whereDate('tarih', '>', $bugun)
                    ->orWhere(function ($q2) use ($bugun, $simdiSaat) {
                        $q2->whereDate('tarih', $bugun)->where('saat', '>=', $simdiSaat);
                    });
            })
            ->whereIn('durum', ['beklemede', 'onaylandi'])
            ->orderBy('tarih')
            ->orderBy('saat')
            ->first();

        // Yorum moderasyonu yalnızca platform yönetiminde.
        $yorumBekleyen = 0;

        return response()->json([
            'success' => true,
            'data' => [
                'toplam_randevu' => $doktor->randevular()->count(),
                'kayitli_hasta' => $doktor->randevular()->whereNotNull('hasta_id')->distinct('hasta_id')->count('hasta_id'),
                'bekleyen_talep' => $doktor->randevular()->where('durum', 'beklemede')->count(),
                'bugun_randevu' => $bugunAktif,
                'bugun_tamamlanan' => $bugunTamamlanan,
                'bugun_iptal' => $bugunIptal,
                'hafta_randevu' => $haftaRandevu,
                'bekleme_listesi' => BeklemeListesi::where('doktor_id', $doktor->id)->where('durum', 'beklemede')->count(),
                'yorum_bekleyen' => $yorumBekleyen,
                'randevuya_acik_mi' => (bool) ($doktor->randevuAyari?->aktif_mi ?? true),
                'sonraki_randevu' => $sonraki ? $this->simpleAppointment($sonraki) : null,
                'paket' => $doktor->paket ? [
                    'id' => $doktor->paket->id,
                    'ad' => $doktor->paket->ad ?? $doktor->paket->name ?? null,
                ] : null,
                'klinik' => $doktor->klinik ? [
                    'id' => $doktor->klinik->id,
                    'ad' => $doktor->klinik->ad,
                    'rol' => $doktor->klinik_rolu,
                ] : null,
                'bekleyen_davet' => \App\Models\KlinikDavetiye::query()
                    ->where('durum', 'beklemede')
                    ->where(function ($q) use ($doktor) {
                        $q->where('davet_edilen_eposta', $doktor->e_posta)
                            ->orWhere('davet_edilen_doktor_id', $doktor->id);
                    })
                    ->where(function ($q) {
                        $q->whereNull('son_kullanma_tarihi')->orWhere('son_kullanma_tarihi', '>', now());
                    })
                    ->count(),
            ],
        ]);
    }

    // ── Appointment requests ───────────────────────────────────

    public function requests(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $items = $doktor->randevular()
            ->with(['hasta:id,ad,soyad,telefon', 'hizmet:id,ad,sure'])
            ->where('durum', 'beklemede')
            ->orderBy('tarih')
            ->orderBy('saat')
            ->limit(100)
            ->get()
            ->map(fn ($r) => $this->simpleAppointment($r));

        return response()->json(['success' => true, 'data' => $items]);
    }

    // ── Leaves (izin) ──────────────────────────────────────────

    public function leaves(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $izinler = $doktor->izinler()
            ->where('bitis_zaman', '>=', now()->subDays(7))
            ->orderBy('baslangic_zaman')
            ->get()
            ->map(fn ($i) => [
                'id' => $i->id,
                'baslangic' => $i->baslangic_zaman?->toIso8601String(),
                'bitis' => $i->bitis_zaman?->toIso8601String(),
                'aciklama' => $i->aciklama,
            ]);

        return response()->json(['success' => true, 'data' => $izinler]);
    }

    public function storeLeave(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $data = $request->validate([
            'baslangic_tarih' => ['required', 'date'],
            'baslangic_saat' => ['required', 'date_format:H:i'],
            'bitis_tarih' => ['required', 'date', 'after_or_equal:baslangic_tarih'],
            'bitis_saat' => ['required', 'date_format:H:i'],
            'aciklama' => ['nullable', 'string', 'max:500'],
        ]);

        $baslangic = $data['baslangic_tarih'].' '.$data['baslangic_saat'].':00';
        $bitis = $data['bitis_tarih'].' '.$data['bitis_saat'].':00';
        if ($bitis <= $baslangic) {
            return response()->json(['success' => false, 'message' => 'Bitiş zamanı başlangıçtan sonra olmalıdır.'], 422);
        }

        $izin = $doktor->izinler()->create([
            'baslangic_zaman' => $baslangic,
            'bitis_zaman' => $bitis,
            'aciklama' => $data['aciklama'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'İzin/tatil eklendi.',
            'data' => [
                'id' => $izin->id,
                'baslangic' => $izin->baslangic_zaman?->toIso8601String(),
                'bitis' => $izin->bitis_zaman?->toIso8601String(),
                'aciklama' => $izin->aciklama,
            ],
        ], 201);
    }

    public function destroyLeave(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $doktor->izinler()->findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'İzin silindi.']);
    }

    /**
     * Day slots for quick-close (hizli kapat) — mirrors hekim panel.
     */
    public function quickCloseSlots(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $request->validate(['tarih' => ['required', 'date']]);

        $tarih = Carbon::parse($request->string('tarih')->value());
        $gunIndeksi = (int) $tarih->format('N');
        $calismaSaati = $doktor->calismaSaatleri()->where('gun', $gunIndeksi)->first();
        $periyot = (int) ($doktor->randevuAyari?->randevu_periyodu ?? 30);
        if ($periyot <= 0) {
            $periyot = 30;
        }

        if (! $calismaSaati || ! $calismaSaati->aktif_mi) {
            return response()->json([
                'success' => true,
                'data' => [
                    'aktif_mi' => false,
                    'periyot' => $periyot,
                    'mesaj' => 'Seçilen günde çalışma saati yok veya gün kapalı.',
                    'slots' => [],
                ],
            ]);
        }

        $randevular = $doktor->randevular()
            ->whereDate('tarih', $tarih->toDateString())
            ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi'])
            ->get();

        $izinler = $doktor->izinler()
            ->where('baslangic_zaman', '<=', $tarih->toDateString().' 23:59:59')
            ->where('bitis_zaman', '>=', $tarih->toDateString().' 00:00:00')
            ->get();

        $slots = [];
        $current = Carbon::parse($calismaSaati->mesai_baslangic);
        $end = Carbon::parse($calismaSaati->mesai_bitis);

        while ($current->lt($end)) {
            $slotStart = $current->format('H:i');
            $current = $current->copy()->addMinutes($periyot);
            $slotEnd = $current->format('H:i');
            if ($current->gt($end)) {
                break;
            }

            $slotDateTimeStr = $tarih->toDateString().' '.$slotStart.':00';
            $isLunch = false;
            if ($calismaSaati->ogle_arasi_aktif_mi && $calismaSaati->ogle_baslangic && $calismaSaati->ogle_bitis) {
                $lunchStart = Carbon::parse($calismaSaati->ogle_baslangic)->format('H:i');
                $lunchEnd = Carbon::parse($calismaSaati->ogle_bitis)->format('H:i');
                if ($slotStart >= $lunchStart && $slotStart < $lunchEnd) {
                    $isLunch = true;
                }
            }

            $isIzin = false;
            foreach ($izinler as $izin) {
                $bas = $izin->baslangic_zaman instanceof \DateTimeInterface
                    ? $izin->baslangic_zaman->format('Y-m-d H:i:s')
                    : (string) $izin->baslangic_zaman;
                $bit = $izin->bitis_zaman instanceof \DateTimeInterface
                    ? $izin->bitis_zaman->format('Y-m-d H:i:s')
                    : (string) $izin->bitis_zaman;
                if ($slotDateTimeStr >= $bas && $slotDateTimeStr < $bit) {
                    $isIzin = true;
                    break;
                }
            }

            $isDolu = $randevular->contains(fn ($item) => substr((string) $item->saat, 0, 5) === $slotStart);

            $slots[] = [
                'saat_baslangic' => $slotStart,
                'saat_bitis' => $slotEnd,
                'saat_string' => $slotStart,
                'ogle_mi' => $isLunch,
                'kapali_mi' => $isIzin,
                'dolu_mu' => $isDolu,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'aktif_mi' => true,
                'periyot' => $periyot,
                'slots' => $slots,
            ],
        ]);
    }

    public function quickCloseSave(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $request->validate([
            'tarih' => ['required', 'date'],
            'saatler' => ['nullable', 'array'],
            'saatler.*' => ['required', 'date_format:H:i'],
        ]);

        $tarih = Carbon::parse($request->string('tarih')->value());
        $periyot = (int) ($doktor->randevuAyari?->randevu_periyodu ?? 30);
        if ($periyot <= 0) {
            $periyot = 30;
        }

        $gonderilenSaatler = $request->input('saatler', []);
        $mevcutIzinler = $doktor->izinler()
            ->where('baslangic_zaman', '<=', $tarih->toDateString().' 23:59:59')
            ->where('bitis_zaman', '>=', $tarih->toDateString().' 00:00:00')
            ->get();

        $gunIndeksi = (int) $tarih->format('N');
        $calismaSaati = $doktor->calismaSaatleri()->where('gun', $gunIndeksi)->first();
        $eklenen = 0;
        $silinen = 0;

        if ($calismaSaati && $calismaSaati->aktif_mi) {
            $current = Carbon::parse($calismaSaati->mesai_baslangic);
            $end = Carbon::parse($calismaSaati->mesai_bitis);

            while ($current->lt($end)) {
                $slotStart = $current->format('H:i');
                $current = $current->copy()->addMinutes($periyot);
                $slotEnd = $current->format('H:i');
                if ($current->gt($end)) {
                    break;
                }

                $slotStartStr = $tarih->toDateString().' '.$slotStart.':00';
                $slotEndStr = $tarih->toDateString().' '.$slotEnd.':00';

                $mevcutIzin = $mevcutIzinler->first(function ($izin) use ($slotStartStr) {
                    $bas = $izin->baslangic_zaman instanceof \DateTimeInterface
                        ? $izin->baslangic_zaman->format('Y-m-d H:i:s')
                        : (string) $izin->baslangic_zaman;

                    return $bas === $slotStartStr || str_starts_with($bas, substr($slotStartStr, 0, 16));
                });

                $isSelected = in_array($slotStart, $gonderilenSaatler, true);

                if ($mevcutIzin && ! $isSelected && $mevcutIzin->aciklama === 'Hızlı Randevu Kapatma') {
                    $mevcutIzin->delete();
                    $silinen++;
                } elseif (! $mevcutIzin && $isSelected) {
                    $doktor->izinler()->create([
                        'baslangic_zaman' => $slotStartStr,
                        'bitis_zaman' => $slotEndStr,
                        'aciklama' => 'Hızlı Randevu Kapatma',
                    ]);
                    $eklenen++;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Saat dilimleri güncellendi.',
            'data' => ['eklenen' => $eklenen, 'silinen' => $silinen],
        ]);
    }

    // ── Waiting list ───────────────────────────────────────────

    public function waitlist(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $durum = $request->string('durum')->value() ?: 'aktif';

        $query = BeklemeListesi::query()
            ->where('doktor_id', $doktor->id)
            ->with(['hizmet:id,ad', 'hasta:id,ad,soyad'])
            ->orderByDesc('created_at');

        if ($durum === 'aktif') {
            $query->whereIn('durum', ['beklemede', 'bildirildi']);
        } elseif (in_array($durum, ['beklemede', 'bildirildi', 'randevu_alindi', 'iptal'], true)) {
            $query->where('durum', $durum);
        }

        $items = $query->limit(100)->get()->map(fn ($k) => [
            'id' => $k->id,
            'ad' => $k->ad,
            'soyad' => $k->soyad,
            'telefon' => $k->telefon,
            'e_posta' => $k->e_posta,
            'durum' => $k->durum,
            'tercih_tarih' => $k->tercih_tarih?->toDateString(),
            'tercih_saat' => $k->tercih_saat ? substr((string) $k->tercih_saat, 0, 5) : null,
            'not' => $k->not,
            'hizmet' => $k->hizmet?->ad,
            'created_at' => $k->created_at?->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'bekleyen' => BeklemeListesi::where('doktor_id', $doktor->id)->where('durum', 'beklemede')->count(),
            ],
        ]);
    }

    public function updateWaitlistStatus(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $data = $request->validate([
            'durum' => ['required', 'in:beklemede,bildirildi,randevu_alindi,iptal'],
        ]);

        $kayit = BeklemeListesi::where('doktor_id', $doktor->id)->findOrFail($id);
        $updates = ['durum' => $data['durum']];
        if ($data['durum'] === 'bildirildi' && ! $kayit->bildirildi_at) {
            $updates['bildirildi_at'] = now();
        }
        $kayit->update($updates);

        return response()->json(['success' => true, 'message' => 'Durum güncellendi.']);
    }

    public function notifyWaitlist(Request $request, int $id, BeklemeListesiService $service): JsonResponse
    {
        $doktor = $this->doktor($request);
        $kayit = BeklemeListesi::where('doktor_id', $doktor->id)->findOrFail($id);

        try {
            $service->notifyKayit($kayit);
            $kayit->update([
                'durum' => 'bildirildi',
                'bildirildi_at' => now(),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Bildirim gönderilemedi.'], 422);
        }

        return response()->json(['success' => true, 'message' => 'Danışan bilgilendirildi.']);
    }

    public function destroyWaitlist(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        BeklemeListesi::where('doktor_id', $doktor->id)->findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Kayıt silindi.']);
    }

    // ── Blogs ──────────────────────────────────────────────────

    public function blogs(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $blogs = $doktor->bloglar()->latest()->limit(100)->get()->map(fn ($b) => [
            'id' => $b->id,
            'baslik' => $b->baslik,
            'icerik' => $b->icerik,
            'resim' => $b->resim,
            'aktif_mi' => (bool) $b->aktif_mi,
            'meta_baslik' => $b->meta_baslik,
            'meta_aciklama' => $b->meta_aciklama,
            'created_at' => $b->created_at?->toIso8601String(),
        ]);

        return response()->json(['success' => true, 'data' => $blogs]);
    }

    public function storeBlog(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $data = $request->validate([
            'baslik' => ['required', 'string', 'max:255'],
            'icerik' => ['required', 'string'],
            'meta_baslik' => ['nullable', 'string', 'max:255'],
            'meta_aciklama' => ['nullable', 'string', 'max:255'],
            'meta_anahtar_kelimeler' => ['nullable', 'string', 'max:255'],
            'aktif_mi' => ['nullable'],
            'resim' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
        ]);

        $blog = $doktor->bloglar()->create([
            'baslik' => $data['baslik'],
            'icerik' => HtmlSanitizer::clean($data['icerik']),
            'meta_baslik' => $data['meta_baslik'] ?? null,
            'meta_aciklama' => $data['meta_aciklama'] ?? null,
            'meta_anahtar_kelimeler' => $data['meta_anahtar_kelimeler'] ?? null,
            'aktif_mi' => $request->boolean('aktif_mi', true),
        ]);

        if ($request->hasFile('resim')) {
            $path = $request->file('resim')->store('uploads/blog', 'public');
            $blog->update(['resim' => $path]);
        }

        return response()->json(['success' => true, 'message' => 'Blog eklendi.', 'data' => $blog->fresh()], 201);
    }

    public function updateBlog(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $blog = $doktor->bloglar()->findOrFail($id);
        $data = $request->validate([
            'baslik' => ['required', 'string', 'max:255'],
            'icerik' => ['required', 'string'],
            'meta_baslik' => ['nullable', 'string', 'max:255'],
            'meta_aciklama' => ['nullable', 'string', 'max:255'],
            'meta_anahtar_kelimeler' => ['nullable', 'string', 'max:255'],
            'aktif_mi' => ['nullable'],
            'resim' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
        ]);

        $blog->update([
            'baslik' => $data['baslik'],
            'icerik' => HtmlSanitizer::clean($data['icerik']),
            'meta_baslik' => $data['meta_baslik'] ?? null,
            'meta_aciklama' => $data['meta_aciklama'] ?? null,
            'meta_anahtar_kelimeler' => $data['meta_anahtar_kelimeler'] ?? null,
            'aktif_mi' => $request->has('aktif_mi') ? $request->boolean('aktif_mi') : $blog->aktif_mi,
        ]);

        if ($request->hasFile('resim')) {
            if ($blog->resim) {
                Storage::disk('public')->delete($blog->resim);
            }
            $blog->update(['resim' => $request->file('resim')->store('uploads/blog', 'public')]);
        }

        return response()->json(['success' => true, 'message' => 'Blog güncellendi.', 'data' => $blog->fresh()]);
    }

    public function destroyBlog(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $blog = $doktor->bloglar()->findOrFail($id);
        if ($blog->resim) {
            Storage::disk('public')->delete($blog->resim);
        }
        $blog->delete();

        return response()->json(['success' => true, 'message' => 'Blog silindi.']);
    }

    // ── Reviews (platform-moderated only; doctors cannot list / approve / reply) ──

    protected function reviewsForbidden(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Hasta yorumları platform yönetimi tarafından bağımsız denetlenir. Hekim uygulamasında yorum listesi, onay veya silme bulunmaz. Onaylanan yorumlar herkese açık profilde görünür.',
            'data' => [],
            'meta' => [
                'platform_moderated' => true,
                'toplam' => 0,
                'beklemede' => 0,
                'onaylandi' => 0,
            ],
        ], 403);
    }

    public function reviews(Request $request): JsonResponse
    {
        return $this->reviewsForbidden();
    }

    public function replyReview(Request $request, int $id): JsonResponse
    {
        return $this->reviewsForbidden();
    }

    public function moderateReview(Request $request, int $id): JsonResponse
    {
        return $this->reviewsForbidden();
    }

    public function destroyReview(Request $request, int $id): JsonResponse
    {
        return $this->reviewsForbidden();
    }

    // ── Gallery ────────────────────────────────────────────────

    public function gallery(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $items = $doktor->galeriler()->orderBy('sira')->get()->map(fn ($g) => [
            'id' => $g->id,
            'resim_yolu' => $g->resim_yolu,
            'baslik' => $g->baslik,
            'sira' => $g->sira,
        ]);

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function storeGallery(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $request->validate([
            'resim' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'baslik' => ['nullable', 'string', 'max:255'],
        ]);

        $file = $request->file('resim');
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $fileName = 'doktor_'.$doktor->id.'_'.Str::uuid().'.'.$ext;
        $file->storeAs('uploads/galeri', $fileName, 'public');
        $legacyPath = 'uploads/galeri/'.$fileName;
        $maxSira = $doktor->galeriler()->max('sira') ?? 0;

        $item = $doktor->galeriler()->create([
            'resim_yolu' => $legacyPath,
            'baslik' => $request->input('baslik'),
            'sira' => $maxSira + 1,
        ]);

        return response()->json(['success' => true, 'message' => 'Fotoğraf eklendi.', 'data' => $item], 201);
    }

    public function updateGallery(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $item = $doktor->galeriler()->findOrFail($id);
        $data = $request->validate([
            'baslik' => ['nullable', 'string', 'max:255'],
            'sira' => ['nullable', 'integer', 'min:0'],
        ]);
        $item->update($data);

        return response()->json(['success' => true, 'message' => 'Galeri güncellendi.', 'data' => $item->fresh()]);
    }

    public function destroyGallery(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $item = $doktor->galeriler()->findOrFail($id);
        $item->delete();

        return response()->json(['success' => true, 'message' => 'Fotoğraf silindi.']);
    }

    // ── FAQ ────────────────────────────────────────────────────

    public function faqs(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $items = $doktor->faqs()->orderBy('sira')->orderBy('id')->get();

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function storeFaq(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $data = $request->validate([
            'soru' => ['required', 'string', 'max:255'],
            'cevap' => ['required', 'string'],
            'sira' => ['nullable', 'integer', 'min:0'],
        ]);

        $faq = $doktor->faqs()->create([
            'soru' => $data['soru'],
            'cevap' => $data['cevap'],
            'sira' => $data['sira'] ?? 0,
            'aktif' => true,
        ]);

        return response()->json(['success' => true, 'message' => 'SSS eklendi.', 'data' => $faq], 201);
    }

    public function updateFaq(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $faq = $doktor->faqs()->findOrFail($id);
        $data = $request->validate([
            'soru' => ['required', 'string', 'max:255'],
            'cevap' => ['required', 'string'],
            'sira' => ['nullable', 'integer', 'min:0'],
            'aktif' => ['nullable', 'boolean'],
        ]);
        $faq->update([
            'soru' => $data['soru'],
            'cevap' => $data['cevap'],
            'sira' => $data['sira'] ?? $faq->sira,
            'aktif' => $data['aktif'] ?? $faq->aktif,
        ]);

        return response()->json(['success' => true, 'message' => 'SSS güncellendi.', 'data' => $faq->fresh()]);
    }

    public function destroyFaq(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $doktor->faqs()->findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'SSS silindi.']);
    }

    public function toggleFaq(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $faq = $doktor->faqs()->findOrFail($id);
        $faq->update(['aktif' => ! $faq->aktif]);

        return response()->json(['success' => true, 'data' => $faq->fresh()]);
    }

    // ── Education ──────────────────────────────────────────────

    public function educations(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $items = $doktor->egitimler()
            ->withCount([
                'basvurular',
                'basvurular as bekleyen_basvuru' => fn ($q) => $q->where('durum', 'beklemede'),
            ])
            ->latest()
            ->limit(50)
            ->get();

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function storeEducation(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $data = $this->validateEducation($request);
        $data['basvuru_acik_mi'] = $request->boolean('basvuru_acik_mi', true);
        $data['tip'] = $data['tip'] ?? 'yuz_yuze';
        $data['durum'] = $data['durum'] ?? 'taslak';
        if (isset($data['icerik'])) {
            $data['icerik'] = HtmlSanitizer::clean($data['icerik']);
        }
        unset($data['kapak']);
        $egitim = $doktor->egitimler()->create($data);
        if ($request->hasFile('kapak')) {
            $egitim->update([
                'kapak' => $request->file('kapak')->store('uploads/egitim', 'public'),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Eğitim eklendi.', 'data' => $egitim->fresh()], 201);
    }

    public function updateEducation(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $egitim = $doktor->egitimler()->findOrFail($id);
        $data = $this->validateEducation($request);
        $data['basvuru_acik_mi'] = $request->boolean('basvuru_acik_mi', $egitim->basvuru_acik_mi);
        unset($data['kapak']);
        if (isset($data['icerik'])) {
            $data['icerik'] = HtmlSanitizer::clean($data['icerik']);
        }
        $egitim->update($data);
        if ($request->hasFile('kapak')) {
            if ($egitim->kapak) {
                Storage::disk('public')->delete($egitim->kapak);
            }
            $egitim->update([
                'kapak' => $request->file('kapak')->store('uploads/egitim', 'public'),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Eğitim güncellendi.', 'data' => $egitim->fresh()]);
    }

    public function destroyEducation(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $egitim = $doktor->egitimler()->findOrFail($id);
        if ($egitim->kapak) {
            Storage::disk('public')->delete($egitim->kapak);
        }
        $egitim->delete();

        return response()->json(['success' => true, 'message' => 'Eğitim silindi.']);
    }

    public function educationFormFields(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $egitim = $doktor->egitimler()->findOrFail($id);
        $items = $egitim->formAlanlari()->orderBy('sira')->get()->map(fn ($a) => [
            'id' => $a->id,
            'etiket' => $a->etiket,
            'anahtar' => $a->anahtar,
            'tip' => $a->tip,
            'zorunlu_mu' => (bool) $a->zorunlu_mu,
            'secenekler' => $a->secenekler,
            'placeholder' => $a->placeholder,
            'sira' => $a->sira,
            'aktif_mi' => (bool) $a->aktif_mi,
        ]);

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function syncEducationFormFields(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $egitim = $doktor->egitimler()->findOrFail($id);
        $data = $request->validate([
            'alanlar' => ['nullable', 'array'],
            'alanlar.*.id' => ['nullable', 'integer'],
            'alanlar.*.etiket' => ['required_with:alanlar', 'string', 'max:255'],
            'alanlar.*.tip' => ['nullable', 'in:text,textarea,select,number,email,tel,checkbox'],
            'alanlar.*.zorunlu_mu' => ['nullable', 'boolean'],
            'alanlar.*.secenekler' => ['nullable'],
            'alanlar.*.placeholder' => ['nullable', 'string', 'max:255'],
        ]);

        $alanlar = $data['alanlar'] ?? [];
        $keep = [];
        $sira = 0;
        foreach ($alanlar as $row) {
            $etiket = trim((string) ($row['etiket'] ?? ''));
            if ($etiket === '') {
                continue;
            }
            $tip = (string) ($row['tip'] ?? 'text');
            $anahtar = Str::slug($etiket, '_');
            if ($anahtar === '') {
                $anahtar = 'alan_'.$sira;
            }
            $secenekler = null;
            if ($tip === 'select' && ! empty($row['secenekler'])) {
                if (is_array($row['secenekler'])) {
                    $secenekler = collect($row['secenekler'])->map(fn ($l) => trim((string) $l))->filter()->values()->all();
                } else {
                    $secenekler = collect(preg_split('/\r\n|\r|\n|,/', (string) $row['secenekler']))
                        ->map(fn ($l) => trim($l))
                        ->filter()
                        ->values()
                        ->all();
                }
            }

            $payload = [
                'egitim_id' => $egitim->id,
                'etiket' => $etiket,
                'anahtar' => $anahtar,
                'tip' => $tip,
                'zorunlu_mu' => ! empty($row['zorunlu_mu']),
                'secenekler' => $secenekler,
                'placeholder' => $row['placeholder'] ?? null,
                'sira' => $sira++,
                'aktif_mi' => true,
            ];

            if (! empty($row['id'])) {
                $alan = $egitim->formAlanlari()->where('id', $row['id'])->first();
                if ($alan) {
                    $alan->update($payload);
                    $keep[] = $alan->id;
                    continue;
                }
            }

            $alan = \App\Models\EgitimFormAlani::create($payload);
            $keep[] = $alan->id;
        }

        $egitim->formAlanlari()->whereNotIn('id', $keep ?: [0])->delete();

        $items = $egitim->formAlanlari()->orderBy('sira')->get();

        return response()->json([
            'success' => true,
            'message' => 'Form alanları kaydedildi.',
            'data' => $items,
        ]);
    }

    public function educationApplications(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $egitimId = $request->integer('egitim_id') ?: null;

        $query = \App\Models\EgitimBasvuru::query()
            ->whereHas('egitim', fn ($q) => $q->where('doktor_id', $doktor->id))
            ->with('egitim:id,baslik')
            ->latest();

        if ($egitimId) {
            $query->where('egitim_id', $egitimId);
        }

        $items = $query->limit(100)->get()->map(fn ($b) => [
            'id' => $b->id,
            'egitim_id' => $b->egitim_id,
            'egitim' => $b->egitim?->baslik,
            'ad' => $b->ad,
            'soyad' => $b->soyad,
            'telefon' => $b->telefon,
            'e_posta' => $b->e_posta,
            'durum' => $b->durum,
            'odeme_durumu' => $b->odeme_durumu,
            'created_at' => $b->created_at?->toIso8601String(),
        ]);

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function updateEducationApplication(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $data = $request->validate([
            'durum' => ['required', 'in:beklemede,onaylandi,reddedildi,iptal'],
        ]);

        $basvuru = \App\Models\EgitimBasvuru::query()
            ->whereHas('egitim', fn ($q) => $q->where('doktor_id', $doktor->id))
            ->findOrFail($id);

        $basvuru->update(['durum' => $data['durum']]);

        return response()->json(['success' => true, 'message' => 'Başvuru durumu güncellendi.']);
    }

    public function markEducationApplicationPaid(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $data = $request->validate([
            'odenen_tutar' => ['required', 'numeric', 'min:0.01'],
            'odeme_yontemi' => ['nullable', 'string', 'max:80'],
        ]);

        $basvuru = \App\Models\EgitimBasvuru::query()
            ->whereHas('egitim', fn ($q) => $q->where('doktor_id', $doktor->id))
            ->findOrFail($id);

        try {
            app(\App\Services\EgitimBasvuruService::class)->odemeAlindi(
                $basvuru,
                (float) $data['odenen_tutar'],
                $data['odeme_yontemi'] ?? null
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ödeme kaydedildi ve finans gelirlerine yansıtıldı.',
            'data' => [
                'id' => $basvuru->id,
                'odeme_durumu' => $basvuru->fresh()->odeme_durumu,
            ],
        ]);
    }

    // ── Finance ────────────────────────────────────────────────

    public function financeOverview(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $baslangic = Carbon::now()->startOfMonth();
        $bitis = Carbon::now()->endOfMonth();

        $buAyGelir = (float) $doktor->odemeler()
            ->whereBetween('odeme_tarihi', [$baslangic, $bitis])
            ->where('durum', '!=', 'iptal')
            ->sum('odenen_tutar');

        $buAyGider = (float) $doktor->giderler()
            ->whereBetween('tarih', [$baslangic, $bitis])
            ->sum('tutar');

        $toplamBorc = (float) ($doktor->odemeler()
            ->whereIn('durum', ['beklemede', 'kismi_odeme'])
            ->selectRaw('SUM(tutar - odenen_tutar) as bakiye')
            ->value('bakiye') ?? 0);

        $sonOdemeler = $doktor->odemeler()->with(['hasta:id,ad,soyad', 'hizmet:id,ad'])->latest()->take(8)->get()
            ->map(fn ($o) => $this->paymentPayload($o));
        $sonGiderler = $doktor->giderler()->latest()->take(8)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'bu_ay_gelir' => $buAyGelir,
                'bu_ay_gider' => $buAyGider,
                'bu_ay_net' => $buAyGelir - $buAyGider,
                'toplam_borc' => $toplamBorc,
                'son_odemeler' => $sonOdemeler,
                'son_giderler' => $sonGiderler,
            ],
        ]);
    }

    public function incomes(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $query = $doktor->odemeler()->with(['hasta:id,ad,soyad', 'hizmet:id,ad', 'finansKategori:id,ad', 'kalemler'])->latest();

        if ($request->filled('durum')) {
            $query->where('durum', $request->string('durum')->value());
        }
        if ($request->filled('baslangic')) {
            $query->whereDate('odeme_tarihi', '>=', $request->string('baslangic')->value());
        }
        if ($request->filled('bitis')) {
            $query->whereDate('odeme_tarihi', '<=', $request->string('bitis')->value());
        }

        $items = $query->limit(100)->get()->map(fn ($o) => $this->paymentPayload($o));

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function storeIncome(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $validated = $request->validate([
            'hasta_id' => ['nullable', 'integer', 'exists:hastalar,id'],
            'hizmet_id' => ['nullable', 'integer', 'exists:hizmetler,id'],
            'finans_kategori_id' => ['nullable', 'integer', 'exists:finans_kategoriler,id'],
            'tutar' => ['required', 'numeric', 'min:0.01'],
            'aciklama' => ['nullable', 'string', 'max:1000'],
            'odeme_tarihi' => ['required', 'date'],
            'ilk_odeme_tutar' => ['nullable', 'numeric', 'min:0'],
            'ilk_odeme_yontemi' => ['required', 'in:nakit,kredi_karti,havale,online'],
        ]);

        $ilk = (float) ($validated['ilk_odeme_tutar'] ?? 0);
        $durum = 'beklemede';
        if ($ilk >= (float) $validated['tutar']) {
            $durum = 'odendi';
        } elseif ($ilk > 0) {
            $durum = 'kismi_odeme';
        }

        $odeme = $doktor->odemeler()->create([
            'hasta_id' => $validated['hasta_id'] ?? null,
            'hizmet_id' => $validated['hizmet_id'] ?? null,
            'finans_kategori_id' => $validated['finans_kategori_id'] ?? null,
            'tutar' => $validated['tutar'],
            'odenen_tutar' => $ilk,
            'odeme_yontemi' => $validated['ilk_odeme_yontemi'],
            'durum' => $durum,
            'aciklama' => $validated['aciklama'] ?? null,
            'odeme_tarihi' => $validated['odeme_tarihi'],
        ]);

        if ($ilk > 0) {
            $odeme->kalemler()->create([
                'tutar' => $ilk,
                'tarih' => $validated['odeme_tarihi'],
                'odeme_yontemi' => $validated['ilk_odeme_yontemi'],
                'not' => 'İlk ödeme',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Gelir kaydı oluşturuldu.',
            'data' => $this->paymentPayload($odeme->load(['hasta', 'hizmet', 'kalemler'])),
        ], 201);
    }

    public function destroyIncome(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $odeme = $doktor->odemeler()->findOrFail($id);
        $odeme->kalemler()->delete();
        $odeme->delete();

        return response()->json(['success' => true, 'message' => 'Gelir silindi.']);
    }

    public function showIncome(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $odeme = $doktor->odemeler()->with(['hasta', 'hizmet', 'finansKategori', 'kalemler'])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $this->paymentPayload($odeme)]);
    }

    public function storeIncomeItem(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $odeme = $doktor->odemeler()->findOrFail($id);
        $validated = $request->validate([
            'tutar' => ['required', 'numeric', 'min:0.01'],
            'tarih' => ['required', 'date'],
            'odeme_yontemi' => ['required', 'in:nakit,kredi_karti,havale,online'],
            'not' => ['nullable', 'string', 'max:500'],
        ]);

        $odeme->kalemler()->create($validated);
        if (method_exists($odeme, 'odenenTutariGuncelle')) {
            $odeme->odenenTutariGuncelle();
        }

        return response()->json([
            'success' => true,
            'message' => 'Ödeme kalemi eklendi.',
            'data' => $this->paymentPayload($odeme->fresh(['hasta', 'hizmet', 'finansKategori', 'kalemler'])),
        ], 201);
    }

    public function destroyIncomeItem(Request $request, int $odemeId, int $kalemId): JsonResponse
    {
        $doktor = $this->doktor($request);
        $odeme = $doktor->odemeler()->findOrFail($odemeId);
        $odeme->kalemler()->findOrFail($kalemId)->delete();
        if (method_exists($odeme, 'odenenTutariGuncelle')) {
            $odeme->odenenTutariGuncelle();
        }

        return response()->json([
            'success' => true,
            'message' => 'Ödeme kalemi silindi.',
            'data' => $this->paymentPayload($odeme->fresh(['hasta', 'hizmet', 'finansKategori', 'kalemler'])),
        ]);
    }

    public function expenses(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $query = $doktor->giderler()->latest();
        if ($request->filled('baslangic')) {
            $query->whereDate('tarih', '>=', $request->string('baslangic')->value());
        }
        if ($request->filled('bitis')) {
            $query->whereDate('tarih', '<=', $request->string('bitis')->value());
        }
        $items = $query->limit(100)->get();

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function storeExpense(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $data = $request->validate([
            'tutar' => ['required', 'numeric', 'min:0.01'],
            'tarih' => ['required', 'date'],
            'kategori' => ['required', 'string', 'max:100'],
            'aciklama' => ['nullable', 'string', 'max:1000'],
            'finans_kategori_id' => ['nullable', 'integer', 'exists:finans_kategoriler,id'],
            'belge' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:4096'],
        ]);

        unset($data['belge']);
        if ($request->hasFile('belge')) {
            $ext = $request->file('belge')->getClientOriginalExtension() ?: 'bin';
            $fileName = 'belge_'.$doktor->id.'_'.time().'_'.Str::random(8).'.'.$ext;
            $request->file('belge')->storeAs('uploads/belgeler', $fileName, 'public');
            $data['belge_yolu'] = 'uploads/belgeler/'.$fileName;
        }

        $gider = $doktor->giderler()->create($data);

        return response()->json(['success' => true, 'message' => 'Gider eklendi.', 'data' => $gider], 201);
    }

    public function updateExpense(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $gider = $doktor->giderler()->findOrFail($id);
        $data = $request->validate([
            'tutar' => ['required', 'numeric', 'min:0.01'],
            'tarih' => ['required', 'date'],
            'kategori' => ['required', 'string', 'max:100'],
            'aciklama' => ['nullable', 'string', 'max:1000'],
            'finans_kategori_id' => ['nullable', 'integer', 'exists:finans_kategoriler,id'],
            'belge' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:4096'],
        ]);
        unset($data['belge']);
        if ($request->hasFile('belge')) {
            $ext = $request->file('belge')->getClientOriginalExtension() ?: 'bin';
            $fileName = 'belge_'.$doktor->id.'_'.time().'_'.Str::random(8).'.'.$ext;
            $request->file('belge')->storeAs('uploads/belgeler', $fileName, 'public');
            $data['belge_yolu'] = 'uploads/belgeler/'.$fileName;
        }
        $gider->update($data);

        return response()->json(['success' => true, 'message' => 'Gider güncellendi.', 'data' => $gider->fresh()]);
    }

    public function destroyExpense(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $doktor->giderler()->findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Gider silindi.']);
    }

    public function updateIncome(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $odeme = $doktor->odemeler()->findOrFail($id);
        $data = $request->validate([
            'tutar' => ['required', 'numeric', 'min:0.01'],
            'odeme_tarihi' => ['required', 'date'],
            'aciklama' => ['nullable', 'string', 'max:1000'],
            'hasta_id' => ['nullable', 'integer', 'exists:hastalar,id'],
            'hizmet_id' => ['nullable', 'integer', 'exists:hizmetler,id'],
            'finans_kategori_id' => ['nullable', 'integer', 'exists:finans_kategoriler,id'],
        ]);
        $odeme->update($data);
        if (method_exists($odeme, 'odenenTutariGuncelle')) {
            $odeme->odenenTutariGuncelle();
        }

        return response()->json([
            'success' => true,
            'message' => 'Gelir güncellendi.',
            'data' => $this->paymentPayload($odeme->fresh(['hasta', 'hizmet', 'finansKategori', 'kalemler'])),
        ]);
    }

    public function financeReport(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $baslangic = $request->input('baslangic', Carbon::now()->startOfMonth()->toDateString());
        $bitis = $request->input('bitis', Carbon::now()->endOfMonth()->toDateString());

        $gelir = (float) $doktor->odemeler()
            ->whereBetween('odeme_tarihi', [$baslangic, $bitis])
            ->where('durum', '!=', 'iptal')
            ->sum('odenen_tutar');
        $gider = (float) $doktor->giderler()
            ->whereBetween('tarih', [$baslangic, $bitis])
            ->sum('tutar');
        $gelirAdet = $doktor->odemeler()
            ->whereBetween('odeme_tarihi', [$baslangic, $bitis])
            ->where('durum', '!=', 'iptal')
            ->count();
        $giderAdet = $doktor->giderler()
            ->whereBetween('tarih', [$baslangic, $bitis])
            ->count();

        $text = "Finans Raporu\n"
            ."Hekim: {$doktor->ad_soyad}\n"
            ."Dönem: {$baslangic} - {$bitis}\n"
            .'Toplam gelir (ödenen): '.number_format($gelir, 2, ',', '.')." TL ({$gelirAdet} kayıt)\n"
            .'Toplam gider: '.number_format($gider, 2, ',', '.')." TL ({$giderAdet} kayıt)\n"
            .'Net: '.number_format($gelir - $gider, 2, ',', '.')." TL\n";

        return response()->json([
            'success' => true,
            'data' => [
                'baslangic' => $baslangic,
                'bitis' => $bitis,
                'gelir' => $gelir,
                'gider' => $gider,
                'net' => $gelir - $gider,
                'gelir_adet' => $gelirAdet,
                'gider_adet' => $giderAdet,
                'rapor_metni' => $text,
            ],
        ]);
    }

    public function financeReportPdf(Request $request)
    {
        $doktor = $this->doktor($request);
        $baslangic = $request->input('baslangic', Carbon::now()->startOfMonth()->toDateString());
        $bitis = $request->input('bitis', Carbon::now()->endOfMonth()->toDateString());

        $gelir = (float) $doktor->odemeler()
            ->whereBetween('odeme_tarihi', [$baslangic, $bitis])
            ->where('durum', '!=', 'iptal')
            ->sum('odenen_tutar');
        $gider = (float) $doktor->giderler()
            ->whereBetween('tarih', [$baslangic, $bitis])
            ->sum('tutar');

        $html = '<html><head><meta charset="utf-8"><style>
            body{font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#111;}
            h1{font-size:18px;} table{width:100%; border-collapse:collapse; margin-top:16px;}
            td,th{border:1px solid #ccc; padding:8px; text-align:left;}
            </style></head><body>';
        $html .= '<h1>Finans Raporu</h1>';
        $html .= '<p><strong>Hekim:</strong> '.e($doktor->ad_soyad).'</p>';
        $html .= '<p><strong>Dönem:</strong> '.e($baslangic).' — '.e($bitis).'</p>';
        $html .= '<table><tr><th>Kalem</th><th>Tutar (TL)</th></tr>';
        $html .= '<tr><td>Toplam gelir (ödenen)</td><td>'.number_format($gelir, 2, ',', '.').'</td></tr>';
        $html .= '<tr><td>Toplam gider</td><td>'.number_format($gider, 2, ',', '.').'</td></tr>';
        $html .= '<tr><td><strong>Net</strong></td><td><strong>'.number_format($gelir - $gider, 2, ',', '.').'</strong></td></tr>';
        $html .= '</table></body></html>';

        $pdf = Pdf::loadHTML($html)->setPaper('a4');
        $filename = 'finans-raporu-'.$baslangic.'-'.$bitis.'.pdf';

        if ($request->boolean('base64') || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'filename' => $filename,
                    'mime' => 'application/pdf',
                    'pdf_base64' => base64_encode($pdf->output()),
                ],
            ]);
        }

        return $pdf->download($filename);
    }

    public function updateFinanceCategory(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $kat = $doktor->finansKategoriler()->findOrFail($id);
        $data = $request->validate([
            'ad' => ['required', 'string', 'max:255'],
            'tur' => ['required', 'in:gelir,gider'],
            'aktif' => ['nullable', 'boolean'],
        ]);
        if ($request->has('aktif')) {
            $data['aktif'] = $request->boolean('aktif');
        }
        $kat->update($data);

        return response()->json(['success' => true, 'message' => 'Kategori güncellendi.', 'data' => $kat->fresh()]);
    }

    public function toggleFinanceCategory(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $kat = $doktor->finansKategoriler()->findOrFail($id);
        $kat->update(['aktif' => ! $kat->aktif]);

        return response()->json(['success' => true, 'message' => 'Kategori durumu güncellendi.', 'data' => $kat->fresh()]);
    }

    public function reorderGallery(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $order = 1;
        foreach ($data['ids'] as $gid) {
            $doktor->galeriler()->where('id', $gid)->update(['sira' => $order++]);
        }

        return response()->json(['success' => true, 'message' => 'Galeri sırası güncellendi.']);
    }

    public function financeCategories(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $items = $doktor->finansKategoriler()->orderBy('ad')->get();

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function storeFinanceCategory(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $data = $request->validate([
            'ad' => ['required', 'string', 'max:255'],
            'tur' => ['required', 'in:gelir,gider'],
            'aktif' => ['nullable', 'boolean'],
        ]);

        $kat = $doktor->finansKategoriler()->create([
            'ad' => $data['ad'],
            'tur' => $data['tur'],
            'aktif' => $data['aktif'] ?? true,
        ]);

        return response()->json(['success' => true, 'message' => 'Kategori eklendi.', 'data' => $kat], 201);
    }

    public function destroyFinanceCategory(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $doktor->finansKategoriler()->findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Kategori silindi.']);
    }

    public function patientBalances(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);

        $balances = $doktor->odemeler()
            ->whereIn('durum', ['beklemede', 'kismi_odeme'])
            ->whereNotNull('hasta_id')
            ->selectRaw('hasta_id, SUM(tutar - odenen_tutar) as bakiye, COUNT(*) as kayit_sayisi')
            ->groupBy('hasta_id')
            ->with('hasta:id,ad,soyad,telefon')
            ->having('bakiye', '>', 0)
            ->get()
            ->map(fn ($row) => [
                'hasta_id' => $row->hasta_id,
                'hasta_adi' => trim(($row->hasta->ad ?? '').' '.($row->hasta->soyad ?? '')),
                'telefon' => $row->hasta->telefon ?? null,
                'bakiye' => (float) $row->bakiye,
                'kayit_sayisi' => (int) $row->kayit_sayisi,
            ]);

        return response()->json(['success' => true, 'data' => $balances]);
    }

    /**
     * Full patient ledger for this doctor.
     */
    public function patientAccount(Request $request, int $hastaId): JsonResponse
    {
        $doktor = $this->doktor($request);
        $hasta = $this->resolveFinanceHasta($doktor, $hastaId);

        $odemeler = $doktor->odemeler()
            ->where('hasta_id', $hasta->id)
            ->with(['hizmet:id,ad', 'finansKategori:id,ad', 'kalemler'])
            ->orderByDesc('odeme_tarihi')
            ->orderByDesc('id')
            ->get();

        $aktif = $odemeler->where('durum', '!=', 'iptal');
        $toplamBorc = (float) $aktif->sum('tutar');
        $toplamOdenen = (float) $aktif->sum('odenen_tutar');

        $faturalar = $odemeler->map(function ($o) {
            $payload = $this->paymentPayload($o);
            $payload['kalan'] = max(0, (float) $o->tutar - (float) $o->odenen_tutar);
            $payload['hasta_id'] = $o->hasta_id;

            return $payload;
        })->values();

        $acik = $faturalar->filter(fn ($f) => in_array($f['durum'], ['beklemede', 'kismi_odeme'], true))->values();

        return response()->json([
            'success' => true,
            'data' => [
                'hasta' => [
                    'id' => $hasta->id,
                    'ad' => $hasta->ad,
                    'soyad' => $hasta->soyad,
                    'ad_soyad' => trim($hasta->ad.' '.$hasta->soyad),
                    'telefon' => $hasta->telefon,
                    'e_posta' => $hasta->e_posta,
                ],
                'ozet' => [
                    'toplam_borc' => $toplamBorc,
                    'toplam_odenen' => $toplamOdenen,
                    'kalan_bakiye' => $toplamBorc - $toplamOdenen,
                    'fatura_sayisi' => $odemeler->count(),
                    'acik_fatura_sayisi' => $acik->count(),
                ],
                'faturalar' => $faturalar,
                'acik_faturalar' => $acik,
            ],
        ]);
    }

    public function patientCollect(Request $request, int $hastaId): JsonResponse
    {
        $doktor = $this->doktor($request);
        $hasta = $this->resolveFinanceHasta($doktor, $hastaId);

        $validated = $request->validate([
            'odeme_id' => ['required', 'integer', 'exists:odemeler,id'],
            'tutar' => ['required', 'numeric', 'min:0.01'],
            'tarih' => ['required', 'date'],
            'odeme_yontemi' => ['required', 'in:nakit,kredi_karti,havale,online'],
            'not' => ['nullable', 'string', 'max:500'],
        ]);

        $odeme = $doktor->odemeler()
            ->where('hasta_id', $hasta->id)
            ->where('id', $validated['odeme_id'])
            ->firstOrFail();

        if (in_array($odeme->durum, ['iptal', 'odendi'], true)) {
            return response()->json(['success' => false, 'message' => 'Bu fatura tahsilata kapalı.'], 422);
        }

        $kalan = max(0, (float) $odeme->tutar - (float) $odeme->odenen_tutar);
        if ((float) $validated['tutar'] > $kalan + 0.001) {
            return response()->json([
                'success' => false,
                'message' => 'Tahsilat kalan bakiyeden büyük olamaz ('.number_format($kalan, 2, ',', '.').' ₺).',
            ], 422);
        }

        $odeme->kalemler()->create([
            'tutar' => $validated['tutar'],
            'tarih' => $validated['tarih'],
            'odeme_yontemi' => $validated['odeme_yontemi'],
            'not' => $validated['not'] ?? 'Hasta hesabından tahsilat',
        ]);
        if (method_exists($odeme, 'odenenTutariGuncelle')) {
            $odeme->odenenTutariGuncelle();
        }

        return response()->json([
            'success' => true,
            'message' => 'Tahsilat kaydedildi.',
            'data' => $this->paymentPayload($odeme->fresh(['hasta', 'hizmet', 'finansKategori', 'kalemler'])),
        ], 201);
    }

    public function patientAddDebt(Request $request, int $hastaId): JsonResponse
    {
        $doktor = $this->doktor($request);
        $hasta = $this->resolveFinanceHasta($doktor, $hastaId);

        $validated = $request->validate([
            'tutar' => ['required', 'numeric', 'min:0.01'],
            'odeme_tarihi' => ['required', 'date'],
            'hizmet_id' => ['nullable', 'integer', 'exists:hizmetler,id'],
            'finans_kategori_id' => ['nullable', 'integer', 'exists:finans_kategoriler,id'],
            'aciklama' => ['nullable', 'string', 'max:1000'],
            'ilk_odeme_tutar' => ['nullable', 'numeric', 'min:0'],
            'ilk_odeme_yontemi' => ['nullable', 'in:nakit,kredi_karti,havale,online'],
        ]);

        $ilk = (float) ($validated['ilk_odeme_tutar'] ?? 0);
        $durum = 'beklemede';
        if ($ilk >= (float) $validated['tutar']) {
            $durum = 'odendi';
        } elseif ($ilk > 0) {
            $durum = 'kismi_odeme';
        }

        $odeme = $doktor->odemeler()->create([
            'hasta_id' => $hasta->id,
            'hizmet_id' => $validated['hizmet_id'] ?? null,
            'finans_kategori_id' => $validated['finans_kategori_id'] ?? null,
            'tutar' => $validated['tutar'],
            'odenen_tutar' => $ilk,
            'odeme_yontemi' => $validated['ilk_odeme_yontemi'] ?? 'nakit',
            'durum' => $durum,
            'aciklama' => $validated['aciklama'] ?? null,
            'odeme_tarihi' => $validated['odeme_tarihi'],
        ]);

        if ($ilk > 0) {
            $odeme->kalemler()->create([
                'tutar' => $ilk,
                'tarih' => $validated['odeme_tarihi'],
                'odeme_yontemi' => $validated['ilk_odeme_yontemi'] ?? 'nakit',
                'not' => 'İlk ödeme',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Borç kaydı oluşturuldu.',
            'data' => $this->paymentPayload($odeme->load(['hasta', 'hizmet', 'kalemler'])),
        ], 201);
    }

    private function resolveFinanceHasta($doktor, int $hastaId): \App\Models\Hasta
    {
        $hasta = \App\Models\Hasta::query()
            ->whereKey($hastaId)
            ->where(function ($q) use ($doktor) {
                $q->whereHas('randevular', fn ($r) => $r->where('doktor_id', $doktor->id))
                    ->orWhereHas('odemeler', fn ($o) => $o->where('doktor_id', $doktor->id));
            })
            ->first();

        if (! $hasta) {
            abort(404, 'Hasta hesabı bulunamadı.');
        }

        return $hasta;
    }

    // ── Profile / password / about ─────────────────────────────

    public function updatePassword(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $data = $request->validate([
            'mevcut_sifre' => ['required', 'string'],
            'sifre' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($data['mevcut_sifre'], $doktor->sifre)) {
            return response()->json(['success' => false, 'message' => 'Mevcut şifreniz hatalı.'], 422);
        }

        $doktor->update(['sifre' => Hash::make($data['sifre'])]);

        return response()->json(['success' => true, 'message' => 'Şifreniz güncellendi.']);
    }

    public function about(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $doktor->load('branslar:id,ad');

        return response()->json([
            'success' => true,
            'data' => [
                'biyografi' => $doktor->biyografi,
                'mezuniyet' => $doktor->mezuniyet ?? [],
                'klinik_adi' => $doktor->klinik_adi,
                'uzmanlik_alani' => $doktor->uzmanlik_alani,
                'branslar' => $doktor->branslar->map(fn ($b) => ['id' => $b->id, 'ad' => $b->ad])->values(),
                'tum_branslar' => Brans::orderBy('ad')->get(['id', 'ad']),
            ],
        ]);
    }

    public function updateAbout(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $data = $request->validate([
            'branslar' => ['required', 'array', 'min:1'],
            'branslar.*' => ['integer', 'exists:branslar,id'],
            'mezuniyet' => ['nullable', 'array'],
            'mezuniyet.*' => ['nullable', 'string', 'max:255'],
            'biyografi' => ['nullable', 'string'],
            'klinik_adi' => ['nullable', 'string', 'max:255'],
        ]);

        $bransIsimleri = Brans::whereIn('id', $data['branslar'])->pluck('ad')->toArray();
        $mezuniyet = array_values(array_filter($data['mezuniyet'] ?? [], fn ($v) => $v !== null && trim((string) $v) !== ''));

        $doktor->update([
            'uzmanlik_alani' => implode(', ', $bransIsimleri),
            'mezuniyet' => $mezuniyet,
            'biyografi' => HtmlSanitizer::clean($data['biyografi'] ?? ''),
            'klinik_adi' => $data['klinik_adi'] ?? null,
        ]);
        $doktor->branslar()->sync($data['branslar']);

        return $this->about($request);
    }

    /**
     * DNS / Hostinger setup steps for a custom domain.
     *
     * @return list<array{adim: int, baslik: string, aciklama: string}>
     */
    private function dnsSteps(?string $domain): array
    {
        $host = $domain ?: 'ornek-domain.com';
        $aRecord = (string) config('services.hostinger.dns_a_record', env('DNS_A_RECORD', ''));
        $cnameTarget = (string) config('services.hostinger.dns_cname_target', env('DNS_CNAME_TARGET', 'proxy.randevuajandam.com'));
        $ipHint = $aRecord !== '' ? $aRecord : '(sunucu IP — destekten öğrenin)';

        return [
            [
                'adim' => 1,
                'baslik' => 'Alan adı paneline girin',
                'aciklama' => "Domain’i aldığınız yerde (Hostinger, GoDaddy, Cloudflare vb.) DNS yönetim ekranını açın.",
            ],
            [
                'adim' => 2,
                'baslik' => 'A kaydı (@)',
                'aciklama' => "Tür: A · Ad: @ · Değer: {$ipHint} · TTL: 3600 (veya Auto).",
            ],
            [
                'adim' => 3,
                'baslik' => 'CNAME (www)',
                'aciklama' => "Tür: CNAME · Ad: www · Değer: {$cnameTarget} (veya {$host}).",
            ],
            [
                'adim' => 4,
                'baslik' => 'Yayılma süresi',
                'aciklama' => 'DNS yayılması genelde 5–60 dakika sürer; nadiren 24 saate kadar uzayabilir.',
            ],
            [
                'adim' => 5,
                'baslik' => 'API & webhook',
                'aciklama' => "Site tarafında API key + secret kullanın. Webhook: https://{$host}/api/webhooks/randevuajandam",
            ],
            [
                'adim' => 6,
                'baslik' => 'Hostinger (isteğe bağlı)',
                'aciklama' => 'Hostinger hPanel → Domains → DNS / Nameservers. Veya Website → Connect domain ile A/CNAME değerlerini girin.',
            ],
        ];
    }

    public function website(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $webSite = $doktor->webSite;
        $apiKey = ApiKey::query()->where('doktor_id', $doktor->id)->first();
        $domain = $webSite?->domain;

        return response()->json([
            'success' => true,
            'data' => [
                'web_sitesi' => $doktor->web_sitesi,
                'platformda_listeleniyor_mu' => (bool) ($doktor->platformda_gorunur ?? true),
                'can_hide_from_platform' => method_exists($doktor, 'canHideFromPlatform') ? (bool) $doktor->canHideFromPlatform() : false,
                'slug' => $doktor->slug,
                'panel_url' => url('/hekim/web-sitesi/kurulum'),
                'domain' => $domain,
                'domain_durum' => $webSite?->durum,
                'tema' => $webSite?->tema,
                'api_key' => $apiKey?->api_key,
                'api_key_aktif' => (bool) ($apiKey?->durum),
                'kurulu_mu' => (bool) $webSite,
                'dns_adimlari' => $this->dnsSteps($domain),
                'dns_a_record' => (string) config('services.hostinger.dns_a_record', env('DNS_A_RECORD', '')),
                'dns_cname_target' => (string) config('services.hostinger.dns_cname_target', env('DNS_CNAME_TARGET', 'proxy.randevuajandam.com')),
            ],
        ]);
    }

    public function websiteSetup(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        if ($doktor->webSite) {
            return response()->json(['success' => false, 'message' => 'Zaten tanımlı bir web siteniz var.'], 422);
        }

        $data = $request->validate([
            'domain' => ['required', 'string', 'max:100', 'unique:hekim_web_siteleri,domain'],
        ]);

        $domain = strtolower(trim($data['domain']));
        $domain = preg_replace('#^https?://(www\.)?#', '', $domain) ?? $domain;
        $domain = rtrim($domain, '/');
        if ($domain === '') {
            return response()->json(['success' => false, 'message' => 'Geçersiz alan adı.'], 422);
        }

        HekimWebSitesi::create([
            'doktor_id' => $doktor->id,
            'domain' => $domain,
            'tema' => 'custom',
            'durum' => 'aktif',
        ]);

        $apiKeyVal = 'rk_'.strtolower(Str::random(30));
        $secretKeyVal = strtolower(Str::random(60));
        ApiKey::issue([
            'doktor_id' => $doktor->id,
            'klinik_id' => null,
            'api_key' => $apiKeyVal,
            'durum' => true,
            'yetkiler' => ['*'],
        ], $secretKeyVal);

        $webhookUrl = 'https://'.$domain.'/api/webhooks/randevuajandam';
        DB::table('webhook_endpoints')->updateOrInsert(
            ['doktor_id' => $doktor->id],
            [
                'url' => $webhookUrl,
                'secret_key' => $secretKeyVal,
                'events' => json_encode(['*']),
                'aktif' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Alan adı kaydedildi. Secret key yalnızca bir kez gösterilir.',
            'data' => [
                'domain' => $domain,
                'api_key' => $apiKeyVal,
                'plain_api_secret' => $secretKeyVal,
                'webhook_url' => $webhookUrl,
                'dns_adimlari' => $this->dnsSteps($domain),
            ],
        ], 201);
    }

    public function websiteRegenerateApiKey(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $apiKey = 'rk_'.strtolower(Str::random(30));
        $secretKey = strtolower(Str::random(60));

        ApiKey::issue([
            'doktor_id' => $doktor->id,
            'klinik_id' => null,
            'api_key' => $apiKey,
            'durum' => true,
            'yetkiler' => ['*'],
        ], $secretKey);

        DB::table('webhook_endpoints')
            ->where('doktor_id', $doktor->id)
            ->update(['secret_key' => $secretKey, 'updated_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Yeni API anahtarı oluşturuldu. Secret bir kez gösterilir.',
            'data' => [
                'api_key' => $apiKey,
                'plain_api_secret' => $secretKey,
            ],
        ]);
    }

    public function websitePlatformVisibility(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        if (method_exists($doktor, 'canHideFromPlatform') && ! $doktor->canHideFromPlatform()) {
            return response()->json([
                'success' => false,
                'message' => 'Ana sitede gizlenme yalnızca özel web sitesi paketinde kullanılabilir.',
            ], 422);
        }

        $data = $request->validate([
            'platformda_gorunur' => ['required', 'boolean'],
        ]);

        $doktor->forceFill(['platformda_gorunur' => $data['platformda_gorunur']])->save();

        return response()->json([
            'success' => true,
            'message' => $data['platformda_gorunur']
                ? 'Profiliniz platformda listeleniyor.'
                : 'Profiliniz ana platform vitrininden gizlendi.',
            'data' => ['platformda_listeleniyor_mu' => (bool) $data['platformda_gorunur']],
        ]);
    }

    // ── Two-factor auth (mobile cache-based setup) ─────────────

    public function twoFactorStatus(Request $request, TwoFactorService $twoFactor): JsonResponse
    {
        $doktor = $this->doktor($request);
        $enabled = $doktor->hasTwoFactorEnabled();

        return response()->json([
            'success' => true,
            'data' => [
                'enabled' => $enabled,
                'confirmed_at' => $doktor->two_factor_confirmed_at?->toIso8601String(),
                'recovery_codes_count' => is_array($doktor->two_factor_recovery_codes)
                    ? count($doktor->two_factor_recovery_codes)
                    : 0,
            ],
        ]);
    }

    public function twoFactorBeginSetup(Request $request, TwoFactorService $twoFactor): JsonResponse
    {
        $doktor = $this->doktor($request);
        if ($doktor->hasTwoFactorEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'İki adımlı doğrulama zaten açık.',
            ], 422);
        }

        $secret = $twoFactor->generateSecret();
        Cache::put($this->twoFactorSetupCacheKey($doktor->id), $secret, now()->addMinutes(15));

        $company = config('app.name', 'Randevu Ajandam');
        $email = (string) $doktor->e_posta;
        $otpauth = (new Google2FA)->getQRCodeUrl($company, $email, $secret);
        $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data='.urlencode($otpauth);

        return response()->json([
            'success' => true,
            'data' => [
                'secret' => $secret,
                'otpauth_url' => $otpauth,
                'qr_image_url' => $qrImageUrl,
            ],
        ]);
    }

    public function twoFactorConfirmSetup(Request $request, TwoFactorService $twoFactor): JsonResponse
    {
        $doktor = $this->doktor($request);
        $data = $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:12'],
        ]);

        $secret = (string) Cache::get($this->twoFactorSetupCacheKey($doktor->id), '');
        if ($secret === '' || ! $twoFactor->verify($secret, $data['code'])) {
            return response()->json([
                'success' => false,
                'message' => 'Kod doğrulanamadı. Authenticator uygulamasındaki 6 haneli kodu girin.',
            ], 422);
        }

        $recovery = $twoFactor->generateRecoveryCodes();
        $doktor->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $recovery,
            'two_factor_confirmed_at' => now(),
        ])->save();
        Cache::forget($this->twoFactorSetupCacheKey($doktor->id));

        return response()->json([
            'success' => true,
            'message' => 'İki adımlı doğrulama açıldı. Yedek kodları güvenli bir yere kaydedin.',
            'data' => ['recovery_codes' => $recovery],
        ]);
    }

    public function twoFactorDisable(Request $request, TwoFactorService $twoFactor): JsonResponse
    {
        $doktor = $this->doktor($request);
        $data = $request->validate([
            'sifre' => ['required', 'string'],
            'code' => ['required', 'string', 'min:6', 'max:20'],
        ]);

        if (! Hash::check($data['sifre'], $doktor->sifre)) {
            return response()->json(['success' => false, 'message' => 'Şifre hatalı.'], 422);
        }
        if (! $twoFactor->verifyUser($doktor, $data['code'])) {
            return response()->json(['success' => false, 'message' => 'Doğrulama kodu hatalı.'], 422);
        }

        $twoFactor->disable($doktor);

        return response()->json(['success' => true, 'message' => 'İki adımlı doğrulama kapatıldı.']);
    }

    public function twoFactorRegenerateRecovery(Request $request, TwoFactorService $twoFactor): JsonResponse
    {
        $doktor = $this->doktor($request);
        if (! $doktor->hasTwoFactorEnabled()) {
            return response()->json(['success' => false, 'message' => '2FA kapalı.'], 422);
        }

        $data = $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:20'],
        ]);

        $secret = (string) $doktor->two_factor_secret;
        if (! $twoFactor->verify($secret, $data['code'])) {
            return response()->json(['success' => false, 'message' => 'Authenticator kodu hatalı.'], 422);
        }

        $codes = $twoFactor->generateRecoveryCodes();
        $doktor->forceFill(['two_factor_recovery_codes' => $codes])->save();

        return response()->json([
            'success' => true,
            'message' => 'Yeni yedek kodlar oluşturuldu.',
            'data' => ['recovery_codes' => $codes],
        ]);
    }

    // ── Clinic (member) ────────────────────────────────────────

    public function clinicInfo(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $klinik = $doktor->klinik;
        if (! $klinik) {
            return response()->json([
                'success' => true,
                'data' => ['uye_mi' => false],
            ]);
        }

        $doktorlar = $klinik->doktorlar()
            ->where('aktif_mi', true)
            ->get(['id', 'ad_soyad', 'unvan', 'e_posta', 'klinik_rolu'])
            ->map(fn ($d) => [
                'id' => $d->id,
                'ad_soyad' => $d->ad_soyad,
                'unvan' => $d->unvan,
                'e_posta' => $d->e_posta,
                'rol' => $d->klinik_rolu,
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'uye_mi' => true,
                'klinik' => [
                    'id' => $klinik->id,
                    'ad' => $klinik->ad,
                    'telefon' => $klinik->telefon ?? null,
                    'e_posta' => $klinik->e_posta ?? null,
                    'adres' => $klinik->adres ?? null,
                ],
                'rol' => $doktor->klinik_rolu,
                'sahip_mi' => method_exists($doktor, 'klinikSahibiMi') ? (bool) $doktor->klinikSahibiMi() : false,
                'doktorlar' => $doktorlar,
                'stats' => [
                    'doktor_sayisi' => $klinik->doktorlar()->count(),
                    'personel_sayisi' => method_exists($klinik, 'personeller') ? $klinik->personeller()->count() : 0,
                    'hasta_sayisi' => method_exists($klinik, 'hastalar') ? $klinik->hastalar()->count() : 0,
                ],
            ],
        ]);
    }

    public function clinicAnnouncements(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $klinik = $doktor->klinik;
        if (! $klinik) {
            return response()->json(['success' => false, 'message' => 'Klinik üyeliği yok.'], 404);
        }

        $items = $klinik->duyurular()
            ->where('aktif_mi', true)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'baslik' => $d->baslik ?? $d->title ?? 'Duyuru',
                'icerik' => $d->icerik ?? $d->metin ?? $d->aciklama ?? '',
                'created_at' => $d->created_at?->toIso8601String(),
            ]);

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function clinicPatients(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $klinik = $doktor->klinik;
        if (! $klinik) {
            return response()->json(['success' => false, 'message' => 'Klinik üyeliği yok.'], 404);
        }

        $search = trim($request->string('q')->value() ?? '');
        $patients = $klinik->hastalar()
            ->when($search !== '', fn ($q) => $q->where(function ($inner) use ($search) {
                $inner->where('ad', 'like', "%{$search}%")
                    ->orWhere('soyad', 'like', "%{$search}%")
                    ->orWhere('telefon', 'like', "%{$search}%");
            }))
            ->orderBy('ad')
            ->limit(50)
            ->get(['hastalar.id', 'ad', 'soyad', 'telefon', 'e_posta']);

        return response()->json(['success' => true, 'data' => $patients]);
    }

    public function clinicLeave(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        if (! $doktor->klinik_id) {
            return response()->json(['success' => false, 'message' => 'Klinik üyeliği yok.'], 404);
        }
        if (method_exists($doktor, 'klinikSahibiMi') && $doktor->klinikSahibiMi()) {
            return response()->json([
                'success' => false,
                'message' => 'Klinik sahibi hekim kliniğinden ayrılamaz.',
            ], 422);
        }

        $klinik = $doktor->klinik;
        DB::transaction(function () use ($doktor) {
            $doktor->update([
                'klinik_id' => null,
                'klinik_rolu' => null,
                'klinik_katilma_tarihi' => null,
                'klinik_aktif_mi' => null,
            ]);
        });

        if ($klinik && $klinik->sahipDoktor) {
            try {
                $klinik->sahipDoktor->notify(new \App\Notifications\DoktorAyrildiBildirimi($doktor));
            } catch (\Throwable) {
                //
            }
        }

        return response()->json(['success' => true, 'message' => 'Klinikten ayrıldınız.']);
    }

    // ── Helpers ────────────────────────────────────────────────

    private function twoFactorSetupCacheKey(int $doktorId): string
    {
        return 'mobile-doktor-2fa-setup:'.$doktorId;
    }


    private function simpleAppointment($r): array
    {
        return [
            'id' => $r->id,
            'tarih' => $r->tarih instanceof \DateTimeInterface ? $r->tarih->format('Y-m-d') : (string) $r->tarih,
            'saat' => substr((string) $r->saat, 0, 5),
            'durum' => $r->durum,
            'hasta_adi' => trim(($r->hasta->ad ?? $r->ad).' '.($r->hasta->soyad ?? $r->soyad)),
            'telefon' => $r->hasta->telefon ?? $r->telefon,
            'hizmet' => $r->hizmet?->ad,
            'not' => $r->not,
            'gorusme_tipi' => $r->gorusme_tipi,
        ];
    }

    private function paymentPayload($o): array
    {
        return [
            'id' => $o->id,
            'tutar' => (float) $o->tutar,
            'odenen_tutar' => (float) $o->odenen_tutar,
            'durum' => $o->durum,
            'odeme_yontemi' => $o->odeme_yontemi,
            'odeme_tarihi' => $o->odeme_tarihi instanceof \DateTimeInterface
                ? $o->odeme_tarihi->format('Y-m-d')
                : (string) $o->odeme_tarihi,
            'aciklama' => $o->aciklama,
            'hasta_adi' => $o->hasta ? trim($o->hasta->ad.' '.$o->hasta->soyad) : null,
            'hizmet' => $o->hizmet?->ad,
            'kategori' => $o->finansKategori?->ad,
            'kalemler' => $o->relationLoaded('kalemler')
                ? $o->kalemler->map(fn ($k) => [
                    'id' => $k->id,
                    'tutar' => (float) $k->tutar,
                    'tarih' => $k->tarih instanceof \DateTimeInterface
                        ? $k->tarih->format('Y-m-d')
                        : (string) $k->tarih,
                    'odeme_yontemi' => $k->odeme_yontemi,
                ])->values()
                : [],
        ];
    }

    private function validateEducation(Request $request): array
    {
        return $request->validate([
            'baslik' => ['required', 'string', 'max:255'],
            'ozet' => ['nullable', 'string', 'max:2000'],
            'icerik' => ['nullable', 'string'],
            'tip' => ['nullable', 'in:yuz_yuze,online,hibrit'],
            'fiyat' => ['nullable', 'numeric', 'min:0'],
            'odeme_notu' => ['nullable', 'string', 'max:500'],
            'kontenjan' => ['nullable', 'integer', 'min:1'],
            'baslangic_at' => ['nullable', 'date'],
            'bitis_at' => ['nullable', 'date'],
            'basvuru_bitis_at' => ['nullable', 'date'],
            'durum' => ['nullable', 'in:taslak,yayinda,arsiv'],
            'mekan' => ['nullable', 'string', 'max:255'],
            'online_url' => ['nullable', 'string', 'max:500'],
            'meta_baslik' => ['nullable', 'string', 'max:255'],
            'meta_aciklama' => ['nullable', 'string', 'max:500'],
            'meta_anahtar_kelimeler' => ['nullable', 'string', 'max:500'],
            'kapak' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
        ]);
    }
}
