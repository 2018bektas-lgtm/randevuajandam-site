<?php

namespace App\Http\Controllers\Api;

use App\Events\RandevuDurumuDegisti;
use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Doktor;
use App\Models\KlinikDavetiye;
use App\Models\KlinikWebSitesi;
use App\Models\Randevu;
use App\Notifications\KlinikDavetBildirimi;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Clinic admin + member operations for doctor mobile app.
 */
class MobileDoctorClinicController extends Controller
{
    private function doktor(Request $request): Doktor
    {
        /** @var Doktor $doktor */
        return $request->attributes->get('auth_doktor');
    }

    private function klinikOrFail(Doktor $doktor)
    {
        $klinik = $doktor->klinik;
        abort_unless($klinik, 404, 'Klinik bulunamadı.');

        return $klinik;
    }

    private function requireOwner(Doktor $doktor): void
    {
        abort_unless(method_exists($doktor, 'klinikSahibiMi') && $doktor->klinikSahibiMi(), 403, 'Bu işlem için klinik sahibi olmalısınız.');
    }

    // ── Overview ───────────────────────────────────────────────

    public function overview(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $klinik = $this->klinikOrFail($doktor);
        $doktorIds = $klinik->doktorlar()->pluck('id');

        $bekleyen = Randevu::whereIn('doktor_id', $doktorIds)->where('durum', 'beklemede')->count();
        $bugun = Randevu::whereIn('doktor_id', $doktorIds)
            ->whereDate('tarih', Carbon::today())
            ->whereIn('durum', ['beklemede', 'onaylandi'])
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'klinik' => [
                    'id' => $klinik->id,
                    'ad' => $klinik->ad,
                    'telefon' => $klinik->telefon ?? null,
                    'e_posta' => $klinik->e_posta ?? null,
                    'adres' => $klinik->adres ?? null,
                ],
                'rol' => $doktor->klinik_rolu,
                'sahip_mi' => method_exists($doktor, 'klinikSahibiMi') ? (bool) $doktor->klinikSahibiMi() : false,
                'stats' => [
                    'doktor_sayisi' => $klinik->doktorlar()->count(),
                    'personel_sayisi' => $klinik->personeller()->count(),
                    'hasta_sayisi' => $klinik->hastalar()->count(),
                    'bekleyen_talep' => $bekleyen,
                    'bugun_randevu' => $bugun,
                ],
            ],
        ]);
    }

    // ── Doctors & invitations ──────────────────────────────────

    public function doctors(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $klinik = $this->klinikOrFail($doktor);

        $doctors = $klinik->doktorlar()->with(['branslar:id,ad', 'calismaSaatleri'])->get()->map(fn ($d) => [
            'id' => $d->id,
            'ad_soyad' => $d->ad_soyad,
            'unvan' => $d->unvan,
            'e_posta' => $d->e_posta,
            'telefon' => $d->telefon,
            'rol' => $d->klinik_rolu,
            'komisyon_orani' => $d->komisyon_orani ?? null,
            'aktif_mi' => (bool) $d->aktif_mi,
            'klinik_aktif_mi' => (bool) ($d->klinik_aktif_mi ?? true),
            'branslar' => $d->branslar->pluck('ad')->values(),
            'calisma_saatleri' => $d->calismaSaatleri
                ->sortBy('gun')
                ->values()
                ->map(fn ($cs) => [
                    'gun' => $cs->gun,
                    'aktif_mi' => (bool) $cs->aktif_mi,
                    'mesai_baslangic' => substr((string) $cs->mesai_baslangic, 0, 5),
                    'mesai_bitis' => substr((string) $cs->mesai_bitis, 0, 5),
                ]),
        ]);

        $invites = $klinik->davetiyeler()->where('durum', 'beklemede')->latest()->get()->map(fn ($i) => [
            'id' => $i->id,
            'e_posta' => $i->davet_edilen_eposta,
            'son_kullanma' => $i->son_kullanma_tarihi?->toDateString(),
            'created_at' => $i->created_at?->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'doktorlar' => $doctors,
                'davetiyeler' => $invites,
            ],
        ]);
    }

    public function inviteDoctor(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);

        if (method_exists($klinik, 'doktorLimitiDolduMu') && $klinik->doktorLimitiDolduMu()) {
            return response()->json(['success' => false, 'message' => 'Hekim limitine ulaşıldı. Paketinizi yükseltin.'], 422);
        }

        $data = $request->validate(['e_posta' => ['required', 'email']]);
        $eposta = strtolower(trim($data['e_posta']));

        if ($klinik->doktorlar()->where('e_posta', $eposta)->exists()) {
            return response()->json(['success' => false, 'message' => 'Bu hekim zaten kliniğinizde.'], 422);
        }
        if ($klinik->davetiyeler()->where('davet_edilen_eposta', $eposta)->where('durum', 'beklemede')->exists()) {
            return response()->json(['success' => false, 'message' => 'Bekleyen davetiye zaten var.'], 422);
        }

        $invited = Doktor::where('e_posta', $eposta)->first();
        $davetiye = KlinikDavetiye::create([
            'klinik_id' => $klinik->id,
            'davet_eden_id' => $doktor->id,
            'davet_edilen_eposta' => $eposta,
            'davet_edilen_doktor_id' => $invited?->id,
            'durum' => 'beklemede',
            'son_kullanma_tarihi' => now()->addDays(7),
        ]);

        try {
            if ($invited) {
                $invited->notify(new KlinikDavetBildirimi($davetiye));
            } else {
                Notification::route('mail', $eposta)->notify(new KlinikDavetBildirimi($davetiye));
            }
        } catch (\Throwable $e) {
            logger()->error('Klinik davet bildirimi gönderilemedi: '.$e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Davetiye gönderildi.'], 201);
    }

    public function cancelInvite(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);
        $davetiye = $klinik->davetiyeler()->findOrFail($id);
        $davetiye->update(['durum' => 'iptal']);

        return response()->json(['success' => true, 'message' => 'Davetiye iptal edildi.']);
    }

    public function removeDoctor(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);
        $target = $klinik->doktorlar()->findOrFail($id);

        if ($target->id === $klinik->sahip_doktor_id) {
            return response()->json(['success' => false, 'message' => 'Klinik sahibi çıkarılamaz.'], 422);
        }

        DB::transaction(function () use ($target) {
            $target->update([
                'klinik_id' => null,
                'klinik_rolu' => null,
                'klinik_katilma_tarihi' => null,
                'klinik_aktif_mi' => null,
            ]);
        });

        try {
            $target->notify(new \App\Notifications\KliniktenCikarildiBildirimi($klinik));
        } catch (\Throwable) {
            //
        }

        return response()->json(['success' => true, 'message' => 'Hekim klinikten çıkarıldı.']);
    }

    public function toggleDoctorStatus(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);
        $target = $klinik->doktorlar()->findOrFail($id);
        $target->update(['klinik_aktif_mi' => ! (bool) ($target->klinik_aktif_mi ?? true)]);

        return response()->json([
            'success' => true,
            'message' => 'Hekim durumu güncellendi.',
            'data' => ['klinik_aktif_mi' => (bool) $target->klinik_aktif_mi],
        ]);
    }

    // ── Staff ──────────────────────────────────────────────────

    public function staff(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $klinik = $this->klinikOrFail($doktor);
        $items = $klinik->personeller()->orderBy('ad_soyad')->get()->map(fn ($p) => [
            'id' => $p->id,
            'ad_soyad' => $p->ad_soyad,
            'e_posta' => $p->e_posta,
            'telefon' => $p->telefon,
            'rol' => $p->rol,
            'aktif_mi' => (bool) $p->aktif_mi,
        ]);

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function storeStaff(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);

        if (method_exists($klinik, 'personelLimitiDolduMu') && $klinik->personelLimitiDolduMu()) {
            return response()->json(['success' => false, 'message' => 'Personel limitine ulaşıldı.'], 422);
        }

        $data = $request->validate([
            'ad_soyad' => ['required', 'string', 'max:255'],
            'e_posta' => ['required', 'email', 'unique:klinik_personelleri,e_posta'],
            'telefon' => ['nullable', 'string', 'max:50'],
            'sifre' => ['required', 'string', 'min:8'],
            'rol' => ['required', 'in:sekreter,muhasebeci,resepsiyonist'],
        ]);

        $personel = $klinik->personeller()->create([
            'ad_soyad' => $data['ad_soyad'],
            'e_posta' => $data['e_posta'],
            'telefon' => $data['telefon'] ?? null,
            'sifre' => Hash::make($data['sifre']),
            'rol' => $data['rol'],
            'yetkiler' => [
                'randevu' => true,
                'hasta' => true,
                'odeme' => $data['rol'] !== 'sekreter',
                'finans' => $data['rol'] === 'muhasebeci',
            ],
            'aktif_mi' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Personel eklendi.',
            'data' => [
                'id' => $personel->id,
                'ad_soyad' => $personel->ad_soyad,
                'e_posta' => $personel->e_posta,
                'rol' => $personel->rol,
                'aktif_mi' => true,
            ],
        ], 201);
    }

    public function updateStaff(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);
        $personel = $klinik->personeller()->findOrFail($id);

        $data = $request->validate([
            'ad_soyad' => ['required', 'string', 'max:255'],
            'e_posta' => ['required', 'email', 'unique:klinik_personelleri,e_posta,'.$personel->id],
            'telefon' => ['nullable', 'string', 'max:50'],
            'rol' => ['required', 'in:sekreter,muhasebeci,resepsiyonist'],
            'sifre' => ['nullable', 'string', 'min:8'],
            'aktif_mi' => ['nullable', 'boolean'],
        ]);

        $update = [
            'ad_soyad' => $data['ad_soyad'],
            'e_posta' => $data['e_posta'],
            'telefon' => $data['telefon'] ?? null,
            'rol' => $data['rol'],
            'yetkiler' => [
                'randevu' => true,
                'hasta' => true,
                'odeme' => $data['rol'] !== 'sekreter',
                'finans' => $data['rol'] === 'muhasebeci',
            ],
        ];
        if (! empty($data['sifre'])) {
            $update['sifre'] = Hash::make($data['sifre']);
        }
        if ($request->has('aktif_mi')) {
            $update['aktif_mi'] = $request->boolean('aktif_mi');
        }
        $personel->update($update);

        return response()->json([
            'success' => true,
            'message' => 'Personel güncellendi.',
            'data' => [
                'id' => $personel->id,
                'ad_soyad' => $personel->ad_soyad,
                'e_posta' => $personel->e_posta,
                'telefon' => $personel->telefon,
                'rol' => $personel->rol,
                'aktif_mi' => (bool) $personel->aktif_mi,
            ],
        ]);
    }

    public function toggleStaff(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);
        $personel = $klinik->personeller()->findOrFail($id);
        $personel->update(['aktif_mi' => ! $personel->aktif_mi]);

        return response()->json(['success' => true, 'message' => 'Personel durumu güncellendi.', 'data' => $personel->fresh()]);
    }

    public function destroyStaff(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);
        $klinik->personeller()->findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Personel silindi.']);
    }

    // ── Clinic calendar & requests ─────────────────────────────

    public function calendar(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $klinik = $this->klinikOrFail($doktor);

        $start = $request->filled('start')
            ? Carbon::parse($request->string('start')->value())->startOfDay()
            : Carbon::today()->startOfWeek();
        $end = $request->filled('end')
            ? Carbon::parse($request->string('end')->value())->startOfDay()
            : $start->copy()->endOfWeek()->startOfDay();

        $doktorIds = $klinik->doktorlar()->pluck('id')->toArray();
        if ($request->filled('doktor_id')) {
            $filterId = (int) $request->input('doktor_id');
            if (in_array($filterId, $doktorIds, true)) {
                $doktorIds = [$filterId];
            }
        }

        $randevular = Randevu::whereIn('doktor_id', $doktorIds)
            ->whereBetween('tarih', [$start->toDateString(), $end->toDateString()])
            ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi', 'iptal'])
            ->with(['doktor:id,ad_soyad,unvan', 'hasta:id,ad,soyad,telefon', 'hizmet:id,ad,sure'])
            ->orderBy('tarih')
            ->orderBy('saat')
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'tarih' => $r->tarih instanceof \DateTimeInterface ? $r->tarih->format('Y-m-d') : (string) $r->tarih,
                'saat' => substr((string) $r->saat, 0, 5),
                'durum' => $r->durum,
                'doktor_id' => $r->doktor_id,
                'doktor' => trim(($r->doktor->unvan ?? '').' '.($r->doktor->ad_soyad ?? '')),
                'hasta_adi' => trim(($r->hasta->ad ?? $r->ad).' '.($r->hasta->soyad ?? $r->soyad)),
                'telefon' => $r->hasta->telefon ?? $r->telefon,
                'hizmet' => $r->hizmet?->ad,
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
                'appointments' => $randevular,
            ],
        ]);
    }

    public function requests(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $klinik = $this->klinikOrFail($doktor);
        $doktorIds = $klinik->doktorlar()->pluck('id')->toArray();

        if ($request->filled('doktor_id') && in_array((int) $request->input('doktor_id'), $doktorIds, true)) {
            $doktorIds = [(int) $request->input('doktor_id')];
        }

        $query = Randevu::whereIn('doktor_id', $doktorIds)
            ->where('durum', 'beklemede')
            ->with(['doktor:id,ad_soyad,unvan', 'hasta:id,ad,soyad,telefon', 'hizmet:id,ad']);

        if ($request->filled('tarih')) {
            $query->whereDate('tarih', $request->string('tarih')->value());
        }

        $items = $query->orderBy('tarih')->orderBy('saat')->limit(100)->get()->map(fn ($r) => [
            'id' => $r->id,
            'tarih' => $r->tarih instanceof \DateTimeInterface ? $r->tarih->format('Y-m-d') : (string) $r->tarih,
            'saat' => substr((string) $r->saat, 0, 5),
            'doktor' => trim(($r->doktor->unvan ?? '').' '.($r->doktor->ad_soyad ?? '')),
            'hasta_adi' => trim(($r->hasta->ad ?? $r->ad).' '.($r->hasta->soyad ?? $r->soyad)),
            'telefon' => $r->hasta->telefon ?? $r->telefon,
            'hizmet' => $r->hizmet?->ad,
        ]);

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function bulkApprove(Request $request): JsonResponse
    {
        return $this->bulkStatus($request, 'onaylandi');
    }

    public function bulkReject(Request $request): JsonResponse
    {
        return $this->bulkStatus($request, 'iptal');
    }

    private function bulkStatus(Request $request, string $durum): JsonResponse
    {
        $doktor = $this->doktor($request);
        $klinik = $this->klinikOrFail($doktor);
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $doktorIds = $klinik->doktorlar()->pluck('id')->toArray();
        $randevular = Randevu::whereIn('id', $data['ids'])
            ->whereIn('doktor_id', $doktorIds)
            ->where('durum', 'beklemede')
            ->get();

        if ($randevular->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Uygun randevu bulunamadı.'], 422);
        }

        foreach ($randevular as $randevu) {
            $eski = $randevu->durum;
            $randevu->update(['durum' => $durum]);
            if ($eski !== $durum) {
                try {
                    RandevuDurumuDegisti::dispatch($randevu, $eski, $durum);
                } catch (\Throwable) {
                    //
                }
            }
        }

        $label = $durum === 'onaylandi' ? 'onaylandı' : 'reddedildi';

        return response()->json([
            'success' => true,
            'message' => count($randevular).' adet randevu '.$label.'.',
        ]);
    }

    // ── Clinic expenses ────────────────────────────────────────

    public function expenses(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $klinik = $this->klinikOrFail($doktor);
        $query = $klinik->giderler()->latest();
        if ($request->filled('baslangic')) {
            $query->whereDate('tarih', '>=', $request->string('baslangic')->value());
        }
        if ($request->filled('bitis')) {
            $query->whereDate('tarih', '<=', $request->string('bitis')->value());
        }
        $items = $query->limit(100)->get();
        $toplam = (float) $items->sum('tutar');

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'toplam_tutar' => $toplam,
                'adet' => $items->count(),
            ],
        ]);
    }

    public function storeExpense(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);

        $data = $request->validate([
            'tutar' => ['required', 'numeric', 'min:0.01'],
            'tarih' => ['required', 'date'],
            'kategori' => ['required', 'string', 'max:100'],
            'aciklama' => ['nullable', 'string', 'max:1000'],
        ]);

        $gider = $klinik->giderler()->create($data);

        return response()->json(['success' => true, 'message' => 'Klinik gideri eklendi.', 'data' => $gider], 201);
    }

    public function updateExpense(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);
        $gider = $klinik->giderler()->findOrFail($id);

        $data = $request->validate([
            'tutar' => ['required', 'numeric', 'min:0.01'],
            'tarih' => ['required', 'date'],
            'kategori' => ['required', 'string', 'max:100'],
            'aciklama' => ['nullable', 'string', 'max:1000'],
        ]);

        $gider->update($data);

        return response()->json(['success' => true, 'message' => 'Klinik gideri güncellendi.', 'data' => $gider->fresh()]);
    }

    public function destroyExpense(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);
        $klinik->giderler()->findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Gider silindi.']);
    }

    // ── Incoming invitations (for invited doctor) ───────────────

    public function myInvites(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);

        $items = KlinikDavetiye::query()
            ->where('durum', 'beklemede')
            ->where(function ($q) use ($doktor) {
                $q->where('davet_edilen_eposta', $doktor->e_posta)
                    ->orWhere('davet_edilen_doktor_id', $doktor->id);
            })
            ->where(function ($q) {
                $q->whereNull('son_kullanma_tarihi')->orWhere('son_kullanma_tarihi', '>', now());
            })
            ->with('klinik:id,ad')
            ->latest()
            ->get()
            ->map(fn ($i) => [
                'id' => $i->id,
                'token' => $i->token,
                'klinik' => $i->klinik?->ad,
                'klinik_id' => $i->klinik_id,
                'son_kullanma' => $i->son_kullanma_tarihi?->toDateString(),
                'created_at' => $i->created_at?->toIso8601String(),
            ]);

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function acceptInvite(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $davetiye = KlinikDavetiye::where('id', $id)->where('durum', 'beklemede')->firstOrFail();

        if (
            strcasecmp((string) $davetiye->davet_edilen_eposta, (string) $doktor->e_posta) !== 0
            && (int) $davetiye->davet_edilen_doktor_id !== (int) $doktor->id
        ) {
            return response()->json(['success' => false, 'message' => 'Bu davetiye size ait değil.'], 403);
        }

        if ($davetiye->son_kullanma_tarihi && $davetiye->son_kullanma_tarihi->isPast()) {
            $davetiye->update(['durum' => 'suresi_doldu']);

            return response()->json(['success' => false, 'message' => 'Davetiye süresi dolmuş.'], 422);
        }

        if (method_exists($doktor, 'bireyselMi') && ! $doktor->bireyselMi()) {
            return response()->json(['success' => false, 'message' => 'Zaten bir kliniğe üyesiniz.'], 422);
        }

        DB::transaction(function () use ($davetiye, $doktor) {
            $davetiye->update(['durum' => 'kabul_edildi']);
            $doktor->update([
                'klinik_id' => $davetiye->klinik_id,
                'klinik_rolu' => 'doktor',
                'klinik_katilma_tarihi' => now(),
                'klinik_aktif_mi' => true,
                'paket_id' => null,
            ]);

            $existingPatients = \App\Models\Hasta::whereHas('randevular', function ($query) use ($doktor) {
                $query->where('doktor_id', $doktor->id);
            })->pluck('id')->toArray();

            if ($existingPatients !== [] && $davetiye->klinik) {
                $syncData = [];
                foreach ($existingPatients as $pId) {
                    $syncData[$pId] = [
                        'kayit_tarihi' => now(),
                        'notlar' => 'Hekim kliniğe katıldığında aktarıldı.',
                    ];
                }
                $davetiye->klinik->hastalar()->syncWithoutDetaching($syncData);
            }
        });

        try {
            $sahip = $davetiye->klinik?->sahipDoktor;
            if ($sahip) {
                $sahip->notify(new \App\Notifications\DoktorKatildiBildirimi($doktor));
            }
        } catch (\Throwable) {
            //
        }

        return response()->json(['success' => true, 'message' => 'Kliniğe başarıyla katıldınız.']);
    }

    public function rejectInvite(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $davetiye = KlinikDavetiye::where('id', $id)->where('durum', 'beklemede')->firstOrFail();

        if (
            strcasecmp((string) $davetiye->davet_edilen_eposta, (string) $doktor->e_posta) !== 0
            && (int) $davetiye->davet_edilen_doktor_id !== (int) $doktor->id
        ) {
            return response()->json(['success' => false, 'message' => 'Bu davetiye size ait değil.'], 403);
        }

        $davetiye->update(['durum' => 'reddedildi']);

        try {
            $sahip = $davetiye->klinik?->sahipDoktor;
            if ($sahip) {
                $sahip->notify(new \App\Notifications\DoktorDavetReddettiBildirimi($doktor, $davetiye->davet_edilen_eposta));
            }
        } catch (\Throwable) {
            //
        }

        return response()->json(['success' => true, 'message' => 'Davet reddedildi.']);
    }

    // ── Settlements (hakediş) ───────────────────────────────────

    public function settlements(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);

        $items = $klinik->hakedisler()
            ->with('doktor:id,ad_soyad,unvan')
            ->orderByDesc('donem_baslangic')
            ->limit(100)
            ->get()
            ->map(fn ($h) => [
                'id' => $h->id,
                'doktor_id' => $h->doktor_id,
                'doktor' => trim(($h->doktor->unvan ?? '').' '.($h->doktor->ad_soyad ?? '')),
                'donem_baslangic' => $h->donem_baslangic instanceof \DateTimeInterface
                    ? $h->donem_baslangic->format('Y-m-d')
                    : (string) $h->donem_baslangic,
                'donem_bitis' => $h->donem_bitis instanceof \DateTimeInterface
                    ? $h->donem_bitis->format('Y-m-d')
                    : (string) $h->donem_bitis,
                'toplam_gelir' => (float) $h->toplam_gelir,
                'komisyon_orani' => (float) $h->komisyon_orani,
                'komisyon_tutari' => (float) $h->komisyon_tutari,
                'net_hakedis' => (float) $h->net_hakedis,
                'durum' => $h->durum,
            ]);

        $doctors = $klinik->doktorlar()->get(['id', 'ad_soyad', 'unvan'])->map(fn ($d) => [
            'id' => $d->id,
            'ad_soyad' => trim(($d->unvan ?? '').' '.$d->ad_soyad),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'doktorlar' => $doctors,
            ],
        ]);
    }

    public function calculateSettlement(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);

        $data = $request->validate([
            'doktor_id' => ['required', 'integer', 'exists:doktorlar,id'],
            'donem_baslangic' => ['required', 'date'],
            'donem_bitis' => ['required', 'date', 'after_or_equal:donem_baslangic'],
            'komisyon_orani' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $target = $klinik->doktorlar()->findOrFail($data['doktor_id']);

        $toplamGelir = (float) DB::table('odemeler')
            ->where('doktor_id', $target->id)
            ->whereBetween('created_at', [
                $data['donem_baslangic'].' 00:00:00',
                $data['donem_bitis'].' 23:59:59',
            ])
            ->sum('tutar');

        $komisyonTutari = round(($toplamGelir * (float) $data['komisyon_orani']) / 100, 2);
        $netHakedis = $toplamGelir - $komisyonTutari;

        $row = $klinik->hakedisler()->create([
            'doktor_id' => $target->id,
            'donem_baslangic' => $data['donem_baslangic'],
            'donem_bitis' => $data['donem_bitis'],
            'toplam_gelir' => $toplamGelir,
            'komisyon_orani' => $data['komisyon_orani'],
            'komisyon_tutari' => $komisyonTutari,
            'net_hakedis' => $netHakedis,
            'durum' => 'hesaplandi',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Hakediş hesaplandı.',
            'data' => $row,
        ], 201);
    }

    public function updateSettlementStatus(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);

        $data = $request->validate([
            'durum' => ['required', 'in:hesaplandi,onaylandi,odendi'],
        ]);

        $hakedis = $klinik->hakedisler()->findOrFail($id);
        $hakedis->update(['durum' => $data['durum']]);

        return response()->json([
            'success' => true,
            'message' => 'Hakediş durumu güncellendi.',
            'data' => $hakedis->fresh(),
        ]);
    }

    // ── Reports ────────────────────────────────────────────────

    public function reports(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);
        $doktorIds = $klinik->doktorlar()->pluck('id')->toArray();

        $baslangic = $request->input('baslangic', Carbon::now()->startOfMonth()->toDateString());
        $bitis = $request->input('bitis', Carbon::now()->endOfMonth()->toDateString());

        $toplamRandevu = Randevu::whereIn('doktor_id', $doktorIds)
            ->whereBetween('tarih', [$baslangic, $bitis])
            ->count();

        $durumDagilimi = Randevu::whereIn('doktor_id', $doktorIds)
            ->whereBetween('tarih', [$baslangic, $bitis])
            ->select('durum', DB::raw('count(*) as adet'))
            ->groupBy('durum')
            ->get()
            ->keyBy('durum')
            ->map(fn ($item) => (int) $item->adet)
            ->toArray();

        $durumDagilimi = array_merge([
            'beklemede' => 0,
            'onaylandi' => 0,
            'iptal' => 0,
            'tamamlandi' => 0,
        ], $durumDagilimi);

        $doktorRandevuSayilari = [];
        foreach ($klinik->doktorlar()->get() as $doc) {
            $doktorRandevuSayilari[] = [
                'ad_soyad' => trim(($doc->unvan ? $doc->unvan.' ' : '').$doc->ad_soyad),
                'adet' => Randevu::where('doktor_id', $doc->id)
                    ->whereBetween('tarih', [$baslangic, $bitis])
                    ->count(),
            ];
        }
        usort($doktorRandevuSayilari, fn ($a, $b) => $b['adet'] <=> $a['adet']);

        $populerHizmetler = DB::table('randevular')
            ->join('hizmetler', 'randevular.hizmet_id', '=', 'hizmetler.id')
            ->whereIn('randevular.doktor_id', $doktorIds)
            ->whereBetween('randevular.tarih', [$baslangic, $bitis])
            ->whereNull('randevular.deleted_at')
            ->select(
                'hizmetler.ad as hizmet_ad',
                DB::raw('count(*) as adet'),
                DB::raw('SUM(hizmetler.fiyat) as tahmini_gelir')
            )
            ->groupBy('hizmetler.ad')
            ->orderByDesc('adet')
            ->limit(5)
            ->get();

        $finansKarsilastirma = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $gelir = (float) DB::table('odemeler')
                ->whereIn('doktor_id', $doktorIds)
                ->whereMonth('odeme_tarihi', $date->month)
                ->whereYear('odeme_tarihi', $date->year)
                ->where('durum', '!=', 'iptal')
                ->sum('odenen_tutar');
            $gider = (float) $klinik->giderler()
                ->whereMonth('tarih', $date->month)
                ->whereYear('tarih', $date->year)
                ->sum('tutar');
            $finansKarsilastirma[] = [
                'ay' => $date->translatedFormat('F Y'),
                'gelir' => $gelir,
                'gider' => $gider,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'baslangic' => $baslangic,
                'bitis' => $bitis,
                'toplam_randevu' => $toplamRandevu,
                'durum_dagilimi' => $durumDagilimi,
                'doktor_randevu' => $doktorRandevuSayilari,
                'populer_hizmetler' => $populerHizmetler,
                'finans_karsilastirma' => $finansKarsilastirma,
            ],
        ]);
    }

    public function reportsPdf(Request $request)
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);
        $doktorIds = $klinik->doktorlar()->pluck('id')->toArray();

        $baslangic = $request->input('baslangic', Carbon::now()->startOfMonth()->toDateString());
        $bitis = $request->input('bitis', Carbon::now()->endOfMonth()->toDateString());

        $toplamRandevu = Randevu::whereIn('doktor_id', $doktorIds)
            ->whereBetween('tarih', [$baslangic, $bitis])
            ->count();

        $durumDagilimi = Randevu::whereIn('doktor_id', $doktorIds)
            ->whereBetween('tarih', [$baslangic, $bitis])
            ->select('durum', DB::raw('count(*) as adet'))
            ->groupBy('durum')
            ->get()
            ->keyBy('durum')
            ->map(fn ($item) => (int) $item->adet)
            ->toArray();

        $gelir = (float) DB::table('odemeler')
            ->whereIn('doktor_id', $doktorIds)
            ->whereBetween('odeme_tarihi', [$baslangic, $bitis])
            ->where('durum', '!=', 'iptal')
            ->sum('odenen_tutar');
        $gider = (float) $klinik->giderler()
            ->whereBetween('tarih', [$baslangic, $bitis])
            ->sum('tutar');

        $html = '<html><head><meta charset="utf-8"><style>
            body{font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#111;}
            h1{font-size:18px;} table{width:100%; border-collapse:collapse; margin-top:16px;}
            td,th{border:1px solid #ccc; padding:8px; text-align:left;}
            </style></head><body>';
        $html .= '<h1>Klinik Raporu</h1>';
        $html .= '<p><strong>Klinik:</strong> '.e($klinik->ad ?? '').'</p>';
        $html .= '<p><strong>Dönem:</strong> '.e($baslangic).' — '.e($bitis).'</p>';
        $html .= '<table><tr><th>Kalem</th><th>Değer</th></tr>';
        $html .= '<tr><td>Toplam randevu</td><td>'.$toplamRandevu.'</td></tr>';
        foreach ($durumDagilimi as $durum => $adet) {
            $html .= '<tr><td>'.e((string) $durum).'</td><td>'.$adet.'</td></tr>';
        }
        $html .= '<tr><td>Toplam gelir (ödenen)</td><td>'.number_format($gelir, 2, ',', '.').' TL</td></tr>';
        $html .= '<tr><td>Toplam gider</td><td>'.number_format($gider, 2, ',', '.').' TL</td></tr>';
        $html .= '<tr><td><strong>Net</strong></td><td><strong>'.number_format($gelir - $gider, 2, ',', '.').' TL</strong></td></tr>';
        $html .= '</table></body></html>';

        $pdf = Pdf::loadHTML($html)->setPaper('a4');
        $filename = 'klinik-raporu-'.$baslangic.'-'.$bitis.'.pdf';

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

    // ── Settings ───────────────────────────────────────────────

    public function settings(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);

        $defaultSaatler = [
            'pazartesi' => ['acilis' => '09:00', 'kapanis' => '18:00', 'kapali' => false],
            'sali' => ['acilis' => '09:00', 'kapanis' => '18:00', 'kapali' => false],
            'carsamba' => ['acilis' => '09:00', 'kapanis' => '18:00', 'kapali' => false],
            'persembe' => ['acilis' => '09:00', 'kapanis' => '18:00', 'kapali' => false],
            'cuma' => ['acilis' => '09:00', 'kapanis' => '18:00', 'kapali' => false],
            'cumartesi' => ['acilis' => '09:00', 'kapanis' => '13:00', 'kapali' => false],
            'pazar' => ['acilis' => '09:00', 'kapanis' => '18:00', 'kapali' => true],
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $klinik->id,
                'ad' => $klinik->ad,
                'telefon' => $klinik->telefon,
                'e_posta' => $klinik->e_posta,
                'adres' => $klinik->adres,
                'il_id' => $klinik->il_id,
                'ilce_id' => $klinik->ilce_id,
                'aciklama' => $klinik->aciklama,
                'meta_baslik' => $klinik->meta_baslik,
                'meta_aciklama' => $klinik->meta_aciklama,
                'logo' => $klinik->logo,
                'calisma_saatleri' => $klinik->calisma_saatleri ?: $defaultSaatler,
            ],
        ]);
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);

        $data = $request->validate([
            'ad' => ['required', 'string', 'max:255'],
            'telefon' => ['required', 'string', 'max:50'],
            'e_posta' => ['nullable', 'email', 'max:255'],
            'adres' => ['required', 'string', 'max:1000'],
            'aciklama' => ['nullable', 'string', 'max:5000'],
            'meta_baslik' => ['nullable', 'string', 'max:255'],
            'meta_aciklama' => ['nullable', 'string', 'max:255'],
            'calisma_saatleri' => ['nullable', 'array'],
        ]);

        if (isset($data['calisma_saatleri']) && is_array($data['calisma_saatleri'])) {
            $normalized = [];
            $gunler = ['pazartesi', 'sali', 'carsamba', 'persembe', 'cuma', 'cumartesi', 'pazar'];
            foreach ($gunler as $gun) {
                $inputGun = $data['calisma_saatleri'][$gun] ?? [];
                $normalized[$gun] = [
                    'acilis' => $inputGun['acilis'] ?? '09:00',
                    'kapanis' => $inputGun['kapanis'] ?? '18:00',
                    'kapali' => (bool) ($inputGun['kapali'] ?? false),
                ];
            }
            $data['calisma_saatleri'] = $normalized;
        }

        $klinik->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Klinik ayarları güncellendi.',
            'data' => $klinik->fresh(),
        ]);
    }

    // ── Clinic website ─────────────────────────────────────────

    /**
     * @return list<array{adim: int, baslik: string, aciklama: string}>
     */
    private function dnsSteps(?string $domain): array
    {
        $host = $domain ?: 'ornek-klinik.com';
        $aRecord = (string) config('services.hostinger.dns_a_record', '');
        $cnameTarget = (string) config('services.hostinger.dns_cname_target', 'proxy.randevuajandam.com');
        $ipHint = $aRecord !== '' ? $aRecord : '(sunucu IP — destekten öğrenin)';

        return [
            ['adim' => 1, 'baslik' => 'Alan adı paneline girin', 'aciklama' => 'Hostinger hPanel veya domain sağlayıcınızda DNS yönetimini açın.'],
            ['adim' => 2, 'baslik' => 'A kaydı (@)', 'aciklama' => "Tür: A · Ad: @ · Değer: {$ipHint}"],
            ['adim' => 3, 'baslik' => 'CNAME (www)', 'aciklama' => "Tür: CNAME · Ad: www · Değer: {$cnameTarget}"],
            ['adim' => 4, 'baslik' => 'Yayılma', 'aciklama' => 'DNS 5–60 dk içinde yayılır; bazen 24 saate kadar sürebilir.'],
            ['adim' => 5, 'baslik' => 'Webhook & API', 'aciklama' => "API key/secret’i siteye ekleyin. Webhook: https://{$host}/webhook/receiver"],
            ['adim' => 6, 'baslik' => 'Hostinger adımları', 'aciklama' => 'Domains → DNS Zone · A ve CNAME kayıtlarını kaydedin. SSL otomatik veya Let’s Encrypt ile açın.'],
        ];
    }

    public function website(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);

        $webSite = $klinik->webSite;
        $apiKey = ApiKey::query()->where('klinik_id', $klinik->id)->first();
        $domain = $webSite?->domain;

        return response()->json([
            'success' => true,
            'data' => [
                'kurulu_mu' => (bool) $webSite,
                'domain' => $domain,
                'durum' => $webSite?->durum,
                'tema' => $webSite?->tema,
                'api_key' => $apiKey?->api_key,
                'api_key_aktif' => (bool) ($apiKey?->durum),
                'feature_available' => method_exists($klinik, 'hasWebSitesiFeature')
                    ? (bool) $klinik->hasWebSitesiFeature()
                    : true,
                'dns_adimlari' => $this->dnsSteps($domain),
                'dns_a_record' => (string) config('services.hostinger.dns_a_record', ''),
                'dns_cname_target' => (string) config('services.hostinger.dns_cname_target', 'proxy.randevuajandam.com'),
            ],
        ]);
    }

    public function websiteSetup(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);

        if (method_exists($klinik, 'hasWebSitesiFeature') && ! $klinik->hasWebSitesiFeature()) {
            return response()->json([
                'success' => false,
                'message' => 'Klinik web sitesi bu pakette sunulmuyor.',
            ], 403);
        }

        if ($klinik->webSite) {
            return response()->json(['success' => false, 'message' => 'Zaten tanımlı bir klinik web sitesi var.'], 422);
        }

        $data = $request->validate([
            'domain' => ['required', 'string', 'max:150', 'unique:klinik_web_siteleri,domain'],
        ]);

        $domain = strtolower(trim($data['domain']));
        $domain = preg_replace('#^https?://(www\.)?#', '', $domain) ?? $domain;
        $domain = rtrim($domain, '/');
        if ($domain === '') {
            return response()->json(['success' => false, 'message' => 'Geçersiz alan adı.'], 422);
        }

        $apiKeyVal = 'rk_'.strtolower(Str::random(30));
        $secretKeyVal = strtolower(Str::random(60));

        DB::transaction(function () use ($klinik, $domain, $apiKeyVal, $secretKeyVal) {
            KlinikWebSitesi::create([
                'klinik_id' => $klinik->id,
                'domain' => $domain,
                'tema' => 'custom',
                'durum' => 'aktif',
            ]);

            ApiKey::issue([
                'klinik_id' => $klinik->id,
                'doktor_id' => null,
                'api_key' => $apiKeyVal,
                'durum' => true,
                'yetkiler' => ['*'],
            ], $secretKeyVal);

            $webhookUrl = (app()->environment('production') ? 'https://' : 'http://').$domain.'/webhook/receiver';
            DB::table('webhook_endpoints')->updateOrInsert(
                ['klinik_id' => $klinik->id, 'doktor_id' => null],
                [
                    'url' => $webhookUrl,
                    'secret_key' => $secretKeyVal,
                    'events' => json_encode(['*']),
                    'aktif' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Klinik web sitesi tanımlandı. Secret key yalnızca bir kez gösterilir.',
            'data' => [
                'domain' => $domain,
                'api_key' => $apiKeyVal,
                'plain_api_secret' => $secretKeyVal,
            ],
        ], 201);
    }

    public function websiteRegenerateApiKey(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);

        if (! $klinik->webSite) {
            return response()->json(['success' => false, 'message' => 'Önce domain tanımlayın.'], 422);
        }

        $apiKeyVal = 'rk_'.strtolower(Str::random(30));
        $secretKeyVal = strtolower(Str::random(60));

        ApiKey::issue([
            'klinik_id' => $klinik->id,
            'doktor_id' => null,
            'api_key' => $apiKeyVal,
            'durum' => true,
            'yetkiler' => ['*'],
        ], $secretKeyVal);

        DB::table('webhook_endpoints')
            ->where('klinik_id', $klinik->id)
            ->update([
                'secret_key' => $secretKeyVal,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'API anahtarları yenilendi.',
            'data' => [
                'api_key' => $apiKeyVal,
                'plain_api_secret' => $secretKeyVal,
            ],
        ]);
    }

    // ── Announcements CRUD (admin) ─────────────────────────────

    public function adminAnnouncements(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);

        $items = $klinik->duyurular()
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'baslik' => $d->baslik,
                'icerik' => $d->icerik,
                'onem_derecesi' => $d->onem_derecesi ?? 'genel',
                'aktif_mi' => (bool) $d->aktif_mi,
                'created_at' => $d->created_at?->toIso8601String(),
            ]);

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function storeAnnouncement(Request $request): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);

        $data = $request->validate([
            'baslik' => ['required', 'string', 'max:255'],
            'icerik' => ['required', 'string'],
            'onem_derecesi' => ['required', 'in:genel,onemli,acil'],
        ]);

        $duyuru = $klinik->duyurular()->create([
            'baslik' => $data['baslik'],
            'icerik' => $data['icerik'],
            'onem_derecesi' => $data['onem_derecesi'],
            'aktif_mi' => true,
        ]);

        if ($data['onem_derecesi'] === 'acil') {
            try {
                Notification::send(
                    $klinik->doktorlar()->get(),
                    new \App\Notifications\KlinikDuyuruBildirimi($duyuru)
                );
            } catch (\Throwable) {
                //
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Duyuru oluşturuldu.',
            'data' => $duyuru,
        ], 201);
    }

    public function updateAnnouncement(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);
        $duyuru = $klinik->duyurular()->findOrFail($id);

        $data = $request->validate([
            'baslik' => ['required', 'string', 'max:255'],
            'icerik' => ['required', 'string'],
            'onem_derecesi' => ['required', 'in:genel,onemli,acil'],
        ]);

        $duyuru->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Duyuru güncellendi.',
            'data' => $duyuru->fresh(),
        ]);
    }

    public function toggleAnnouncement(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);
        $duyuru = $klinik->duyurular()->findOrFail($id);
        $duyuru->update(['aktif_mi' => ! $duyuru->aktif_mi]);

        return response()->json([
            'success' => true,
            'message' => 'Duyuru durumu güncellendi.',
            'data' => $duyuru->fresh(),
        ]);
    }

    public function destroyAnnouncement(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);
        $klinik->duyurular()->findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Duyuru silindi.']);
    }

    // ── Clinic appointment actions ─────────────────────────────

    private function clinicAppointmentOrFail(Doktor $actor, int $id): Randevu
    {
        $klinik = $this->klinikOrFail($actor);
        $doktorIds = $klinik->doktorlar()->pluck('id')->toArray();
        $randevu = Randevu::whereIn('doktor_id', $doktorIds)->findOrFail($id);

        $isOwner = method_exists($actor, 'klinikSahibiMi') && $actor->klinikSahibiMi();
        $isSelf = (int) $randevu->doktor_id === (int) $actor->id;
        abort_unless($isOwner || $isSelf, 403, 'Bu randevuyu yönetme yetkiniz yok.');

        return $randevu;
    }

    public function updateAppointmentStatus(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $randevu = $this->clinicAppointmentOrFail($doktor, $id);

        $data = $request->validate([
            'durum' => ['required', 'in:beklemede,onaylandi,tamamlandi,iptal'],
        ]);

        $eski = $randevu->durum;
        $randevu->update(['durum' => $data['durum']]);
        if ($eski !== $data['durum']) {
            try {
                RandevuDurumuDegisti::dispatch($randevu, $eski, $data['durum']);
            } catch (\Throwable) {
                //
            }
        }

        return response()->json(['success' => true, 'message' => 'Randevu durumu güncellendi.']);
    }

    public function rescheduleAppointment(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $randevu = $this->clinicAppointmentOrFail($doktor, $id);

        $data = $request->validate([
            'tarih' => ['required', 'date'],
            'saat' => ['required', 'string', 'max:8'],
        ]);

        $saat = strlen($data['saat']) === 5 ? $data['saat'].':00' : $data['saat'];
        $randevu->update([
            'tarih' => $data['tarih'],
            'saat' => $saat,
        ]);

        return response()->json(['success' => true, 'message' => 'Randevu yeniden planlandı.']);
    }

    public function resetStaffPassword(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);
        $personel = $klinik->personeller()->findOrFail($id);

        $geciciSifre = Str::random(10);
        $personel->update([
            'sifre' => Hash::make($geciciSifre),
            'sifre_degistirildi_mi' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Personel şifresi sıfırlandı.',
            'data' => [
                'id' => $personel->id,
                'gecici_sifre' => $geciciSifre,
            ],
        ]);
    }

    public function updateDoctor(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $this->requireOwner($doktor);
        $klinik = $this->klinikOrFail($doktor);
        $doc = $klinik->doktorlar()->findOrFail($id);

        $data = $request->validate([
            'klinik_rolu' => ['required', 'in:doktor,sahip,ortak'],
            'komisyon_orani' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        if ((int) $doc->id === (int) ($klinik->sahip_doktor_id ?? 0) && $data['klinik_rolu'] !== 'sahip') {
            return response()->json([
                'success' => false,
                'message' => 'Klinik sahibi hekimin rolü düşürülemez.',
            ], 422);
        }

        $doc->update([
            'klinik_rolu' => $data['klinik_rolu'],
            'komisyon_orani' => $data['komisyon_orani'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Hekim klinik ayarları güncellendi.',
            'data' => [
                'id' => $doc->id,
                'klinik_rolu' => $doc->klinik_rolu,
                'komisyon_orani' => $doc->komisyon_orani,
            ],
        ]);
    }

    public function updateClinicPatientNote(Request $request, int $id): JsonResponse
    {
        $doktor = $this->doktor($request);
        $klinik = $this->klinikOrFail($doktor);
        $data = $request->validate([
            'notlar' => ['nullable', 'string', 'max:1000'],
        ]);

        abort_unless($klinik->hastalar()->where('hastalar.id', $id)->exists(), 404);

        $klinik->hastalar()->updateExistingPivot($id, [
            'notlar' => $data['notlar'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Hasta notu güncellendi.',
            'data' => [
                'hasta_id' => $id,
                'notlar' => $data['notlar'] ?? null,
            ],
        ]);
    }
}
