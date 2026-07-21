<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brans;
use App\Models\Doktor;
use App\Models\Hasta;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\KlinikDavetiye;
use App\Models\Randevu;
use App\Models\Unvan;
use App\Notifications\KlinikDavetBildirimi;
use App\Services\IyzicoSubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KlinikController extends Controller
{
    /**
     * Display the clinic owner administration dashboard.
     */
    public function yonetimPanel()
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        if (! $klinik) {
            return redirect()->route('hekim.panel')->with('hata', 'Kliniğiniz bulunamadı.');
        }

        // Stats calculations
        $doktorSayisi = $klinik->doktorlar()->count();
        $personelSayisi = $klinik->personeller()->count();
        $toplamHasta = $klinik->hastalar()->count();

        // Financial stats this month
        $gelirBuAy = DB::table('odemeler')
            ->whereIn('doktor_id', $klinik->doktorlar()->pluck('id'))
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('tutar');

        $giderBuAy = $klinik->giderler()
            ->whereMonth('tarih', now()->month)
            ->whereYear('tarih', now()->year)
            ->sum('tutar');

        $sonRandevular = Randevu::whereIn('doktor_id', $klinik->doktorlar()->pluck('id'))
            ->orderBy('tarih', 'desc')
            ->orderBy('saat', 'desc')
            ->take(5)
            ->get();

        return view('klinik.panel', compact('klinik', 'doktorSayisi', 'personelSayisi', 'toplamHasta', 'gelirBuAy', 'giderBuAy', 'sonRandevular'));
    }

    /**
     * Manage clinic doctors and invitations.
     */
    public function doktorlar()
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        $doktorlar = $klinik->doktorlar()->with('branslar')->get();
        $davetiyeler = $klinik->davetiyeler()->where('durum', 'beklemede')->get();

        return view('klinik.doktorlar', compact('klinik', 'doktorlar', 'davetiyeler'));
    }

    /**
     * Send clinic invitation to a doctor.
     */
    public function davetEt(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        // Check clinic doctor limit
        if ($klinik->doktorLimitiDolduMu()) {
            return back()->with('hata', 'Paketinizin hekim limitine ulaştınız. Limit arttırmak için lütfen paketinizi yükseltin.');
        }

        $request->validate([
            'e_posta' => 'required|email',
        ]);

        $eposta = $request->e_posta;

        // Check if doctor is already in this clinic
        $alreadyInClinic = $klinik->doktorlar()->where('e_posta', $eposta)->exists();
        if ($alreadyInClinic) {
            return back()->with('hata', 'Bu hekim zaten kliniğinizde kayıtlıdır.');
        }

        // Check if there is an active invitation for this email
        $activeInvite = $klinik->davetiyeler()->where('davet_edilen_eposta', $eposta)->where('durum', 'beklemede')->exists();
        if ($activeInvite) {
            return back()->with('hata', 'Bu hekime gönderilmiş beklemede olan bir davetiye zaten bulunuyor.');
        }

        // Check if invited doctor exists in our system
        $invitedDoktor = Doktor::where('e_posta', $eposta)->first();

        // Create Invitation
        $davetiye = KlinikDavetiye::create([
            'klinik_id' => $klinik->id,
            'davet_eden_id' => $doktor->id,
            'davet_edilen_eposta' => $eposta,
            'davet_edilen_doktor_id' => $invitedDoktor?->id,
            'durum' => 'beklemede',
            'son_kullanma_tarihi' => now()->addDays(7),
        ]);

        // Send notification: registered doctor → app + mail; otherwise mail only
        try {
            if ($invitedDoktor) {
                $invitedDoktor->notify(new KlinikDavetBildirimi($davetiye));
            } else {
                Notification::route('mail', $eposta)->notify(new KlinikDavetBildirimi($davetiye));
            }
        } catch (\Exception $e) {
            logger()->error('Klinik davet bildirimi gönderilemedi: '.$e->getMessage());
        }

        return back()->with('basari', 'Davetiye başarıyla gönderildi.');
    }

    /**
     * Cancel an active invitation.
     */
    public function davetiyeIptal($id)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        $davetiye = $klinik->davetiyeler()->findOrFail($id);
        $davetiye->update(['durum' => 'iptal']);

        return back()->with('basari', 'Davetiye iptal edildi.');
    }

    /**
     * Remove a doctor from the clinic.
     */
    public function doktorCikar($id)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        $cikarilacakDoktor = $klinik->doktorlar()->findOrFail($id);

        if ($cikarilacakDoktor->id === $klinik->sahip_doktor_id) {
            return back()->with('hata', 'Klinik sahibi hekim klinikten çıkarılamaz.');
        }

        DB::transaction(function () use ($cikarilacakDoktor) {
            $cikarilacakDoktor->update([
                'klinik_id' => null,
                'klinik_rolu' => null,
                'klinik_katilma_tarihi' => null,
                'klinik_aktif_mi' => null,
                'paket_id' => null, // package is managed by clinic now
            ]);
        });

        // Notify doctor
        $cikarilacakDoktor->notify(new \App\Notifications\KliniktenCikarildiBildirimi($klinik));

        return back()->with('basari', 'Hekim klinikten başarıyla çıkarıldı.');
    }

    /**
     * Manage clinic staff.
     */
    public function personeller()
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        $personeller = $klinik->personeller;

        return view('klinik.personeller', compact('klinik', 'personeller'));
    }

    /**
     * Add staff member to the clinic.
     */
    public function personelEkle(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        // Check limit
        if ($klinik->personelLimitiDolduMu()) {
            return back()->with('hata', 'Paketinizin personel limitine ulaştınız. Limit arttırmak için lütfen paketinizi yükseltin.');
        }

        $request->validate([
            'ad_soyad' => 'required|string|max:255',
            'e_posta' => 'required|email|unique:klinik_personelleri,e_posta',
            'telefon' => 'nullable|string',
            'sifre' => 'required|string|min:8',
            'rol' => 'required|in:sekreter,muhasebeci,resepsiyonist',
        ]);

        $klinik->personeller()->create([
            'ad_soyad' => $request->ad_soyad,
            'e_posta' => $request->e_posta,
            'telefon' => $request->telefon,
            'sifre' => Hash::make($request->sifre),
            'rol' => $request->rol,
            'yetkiler' => [
                'randevu' => true,
                'hasta' => true,
                'odeme' => $request->rol !== 'sekreter',
                'finans' => $request->rol === 'muhasebeci',
            ],
            'aktif_mi' => true,
        ]);

        return back()->with('basari', 'Klinik personeli başarıyla eklendi.');
    }

    /**
     * Toggle staff status (active/passive).
     */
    public function personelDurum($id)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        $personel = $klinik->personeller()->findOrFail($id);
        $personel->update(['aktif_mi' => ! $personel->aktif_mi]);

        return back()->with('basari', 'Personel durumu güncellendi.');
    }

    /**
     * Delete a staff member.
     */
    public function personelSil($id)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        $personel = $klinik->personeller()->findOrFail($id);
        $personel->delete();

        return back()->with('basari', 'Personel başarıyla silindi.');
    }

    /**
     * Manage clinic expenses.
     */
    public function giderler()
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        $giderler = $klinik->giderler()->orderBy('tarih', 'desc')->get();

        return view('klinik.giderler', compact('klinik', 'giderler'));
    }

    /**
     * Add clinic expense.
     */
    public function giderEkle(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        $request->validate([
            'baslik' => 'required|string|max:255',
            'kategori' => 'required|string',
            'tutar' => 'required|numeric|min:0.01',
            'tarih' => 'required|date',
            'aciklama' => 'nullable|string',
        ]);

        $klinik->giderler()->create($request->only(['baslik', 'kategori', 'tutar', 'tarih', 'aciklama']));

        return back()->with('basari', 'Gider başarıyla kaydedildi.');
    }

    /**
     * Delete clinic expense.
     */
    public function giderSil($id)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        $gider = $klinik->giderler()->findOrFail($id);
        $gider->delete();

        return back()->with('basari', 'Gider başarıyla silindi.');
    }

    /**
     * Show clinic commissions / doctor settlements.
     */
    public function hakedisler()
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        $doktorlar = $klinik->doktorlar;
        $hakedisler = $klinik->hakedisler()->orderBy('donem_baslangic', 'desc')->get();

        return view('klinik.hakedisler', compact('klinik', 'doktorlar', 'hakedisler'));
    }

    /**
     * Calculate and generate doctor settlements.
     */
    public function hakedisHesapla(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        $request->validate([
            'doktor_id' => 'required|exists:doktorlar,id',
            'donem_baslangic' => 'required|date',
            'donem_bitis' => 'required|date|after_or_equal:donem_baslangic',
            'komisyon_orani' => 'required|numeric|min:0|max:100',
        ]);

        $hesaplanacakDoktor = $klinik->doktorlar()->findOrFail($request->doktor_id);

        // Fetch total revenues for the doctor in period
        $toplamGelir = DB::table('odemeler')
            ->where('doktor_id', $hesaplanacakDoktor->id)
            ->whereBetween('created_at', [$request->donem_baslangic.' 00:00:00', $request->donem_bitis.' 23:59:59'])
            ->sum('tutar');

        $komisyonTutari = round(($toplamGelir * $request->komisyon_orani) / 100, 2);
        $netHakedis = $toplamGelir - $komisyonTutari;

        $klinik->hakedisler()->create([
            'doktor_id' => $hesaplanacakDoktor->id,
            'donem_baslangic' => $request->donem_baslangic,
            'donem_bitis' => $request->donem_bitis,
            'toplam_gelir' => $toplamGelir,
            'komisyon_orani' => $request->komisyon_orani,
            'komisyon_tutari' => $komisyonTutari,
            'net_hakedis' => $netHakedis,
            'durum' => 'hesaplandi',
        ]);

        return back()->with('basari', 'Hakediş başarıyla hesaplandı.');
    }

    /**
     * Update settlement payment status.
     */
    public function hakedisDurumGuncelle(Request $request, $id)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        $hakedis = $klinik->hakedisler()->findOrFail($id);

        $request->validate([
            'durum' => 'required|in:hesaplandi,onaylandi,odendi',
        ]);

        $hakedis->update(['durum' => $request->durum]);

        return back()->with('basari', 'Hakediş durumu güncellendi.');
    }

    /**
     * Manage clinic settings.
     */
    public function ayarlar()
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        if (!$klinik) {
            return redirect()->route('hekim.panel')->with('hata', 'Kliniğiniz bulunamadı.');
        }

        $iller = Il::orderBy('ad')->get();

        $defaultSaatler = [
            'pazartesi' => ['acilis' => '09:00', 'kapanis' => '18:00', 'kapali' => false],
            'sali' => ['acilis' => '09:00', 'kapanis' => '18:00', 'kapali' => false],
            'carsamba' => ['acilis' => '09:00', 'kapanis' => '18:00', 'kapali' => false],
            'persembe' => ['acilis' => '09:00', 'kapanis' => '18:00', 'kapali' => false],
            'cuma' => ['acilis' => '09:00', 'kapanis' => '18:00', 'kapali' => false],
            'cumartesi' => ['acilis' => '09:00', 'kapanis' => '13:00', 'kapali' => false],
            'pazar' => ['acilis' => '09:00', 'kapanis' => '18:00', 'kapali' => true],
        ];
        $calismaSaatleri = $klinik->calisma_saatleri ?: $defaultSaatler;

        return view('klinik.ayarlar', compact('klinik', 'iller', 'calismaSaatleri'));
    }

    /**
     * Update clinic settings.
     */
    public function ayarlarGuncelle(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        if (!$klinik) {
            return back()->with('hata', 'Kliniğiniz bulunamadı.');
        }

        $request->validate([
            'ad' => 'required|string|max:255',
            'telefon' => 'required|string',
            'e_posta' => 'nullable|email|max:255',
            'adres' => 'required|string',
            'il_id' => 'required|exists:iller,id',
            'ilce_id' => 'required|string|max:255|exists:ilceler,ad',
            'logo' => 'nullable|image|max:2048',
            'aciklama' => 'nullable|string',
            'meta_baslik' => 'nullable|string|max:255',
            'meta_aciklama' => 'nullable|string|max:255',
            'calisma_saatleri' => 'nullable|array',
        ]);

        $data = $request->only(['ad', 'telefon', 'e_posta', 'adres', 'il_id', 'aciklama', 'meta_baslik', 'meta_aciklama']);

        $ilModel = Il::find($request->il_id);
        $ilceModel = Ilce::where('il_id', $ilModel?->id)->where('ad', $request->ilce_id)->first();
        $data['ilce_id'] = $ilceModel?->id;

        // Handle clinic logo upload
        if ($request->hasFile('logo')) {
            if ($klinik->logo) {
                Storage::disk('public')->delete(str_replace('storage/', '', $klinik->logo));
            }
            $path = $request->file('logo')->store('uploads/klinikler', 'public');
            $data['logo'] = $path;
        }

        // Process calisma_saatleri JSON
        $calismaSaatleri = [];
        $gunler = ['pazartesi', 'sali', 'carsamba', 'persembe', 'cuma', 'cumartesi', 'pazar'];
        foreach ($gunler as $gun) {
            $inputGun = $request->input("calisma_saatleri.{$gun}", []);
            $calismaSaatleri[$gun] = [
                'acilis' => $inputGun['acilis'] ?? '09:00',
                'kapanis' => $inputGun['kapanis'] ?? '18:00',
                'kapali' => !isset($inputGun['aktif']),
            ];
        }
        $data['calisma_saatleri'] = $calismaSaatleri;

        $klinik->update($data);

        return back()->with('basari', 'Klinik ayarları başarıyla güncellendi.');
    }

    /**
     * Show accept/reject clinic invitation page.
     */
    public function davetGoster($token)
    {
        $davetiye = KlinikDavetiye::where('token', $token)->where('durum', 'beklemede')->firstOrFail();

        if ($davetiye->suresiDolduMu()) {
            $davetiye->update(['durum' => 'suresi_doldu']);
            abort(404, 'Bu davetiyenin süresi dolmuştur.');
        }

        $klinik = $davetiye->klinik;
        $davetEden = $davetiye->davetEden;

        // Check if there is an existing doctor matching the invitation email
        $existingDoctor = Doktor::where('e_posta', $davetiye->davet_edilen_eposta)->first();

        $iller = Il::orderBy('ad')->get();
        $branslar = Brans::orderBy('ad')->get();
        $unvanlar = Unvan::orderBy('ad')->get();

        return view('frontend.klinik.davet', compact('davetiye', 'klinik', 'davetEden', 'existingDoctor', 'iller', 'branslar', 'unvanlar'));
    }

    /**
     * Handle clinic invitation acceptance.
     */
    public function davetKabul(Request $request, $token)
    {
        $davetiye = KlinikDavetiye::where('token', $token)->where('durum', 'beklemede')->firstOrFail();

        if ($davetiye->suresiDolduMu()) {
            $davetiye->update(['durum' => 'suresi_doldu']);

            return redirect('/')->with('hata', 'Davetiye süresi dolmuş.');
        }

        $existingDoctor = Doktor::where('e_posta', $davetiye->davet_edilen_eposta)->first();

        if ($existingDoctor) {
            // Already registered doctor, verify they are logged in or prompt login
            if (! Auth::guard('doktor')->check()) {
                return redirect()->route('frontend.hekim.giris')
                    ->with('hata', 'Davetiye kabul etmek için lütfen önce hekim hesabınızla giriş yapın.');
            }

            $currentDoctor = Auth::guard('doktor')->user();

            if ($currentDoctor->id !== $existingDoctor->id) {
                return back()->with('hata', 'Bu davetiye bu e-posta adresiyle eşleşmiyor.');
            }

            if (! $currentDoctor->bireyselMi()) {
                return back()->with('hata', 'Zaten bir kliniğe üyesiniz.');
            }

            // Cancel any individual subscription
            if ($currentDoctor->iyzico_subscription_reference_code) {
                try {
                    $subscriptionService = app(IyzicoSubscriptionService::class);
                    $subscriptionService->cancelSubscription($currentDoctor->iyzico_subscription_reference_code);
                } catch (\Exception $e) {
                    logger()->error('Bireysel abonelik iptal hatası: '.$e->getMessage());
                }
            }

            // Accept and link doctor to clinic
            DB::transaction(function () use ($davetiye, $currentDoctor) {
                $davetiye->update(['durum' => 'kabul_edildi']);

                $currentDoctor->update([
                    'klinik_id' => $davetiye->klinik_id,
                    'klinik_rolu' => 'doktor',
                    'klinik_katilma_tarihi' => now(),
                    'klinik_aktif_mi' => true,
                    'paket_id' => null, // clinics pay the package
                    'iyzico_subscription_reference_code' => null,
                    'iyzico_subscription_status' => null,
                ]);

                // Copy doctor's patients to clinic patient pool
                $existingPatients = Hasta::whereHas('randevular', function ($query) use ($currentDoctor) {
                    $query->where('doktor_id', $currentDoctor->id);
                })->pluck('id')->toArray();

                if (! empty($existingPatients)) {
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

            // Send notification to clinic owner
            $sahip = $davetiye->klinik->sahipDoktor;
            if ($sahip) {
                $sahip->notify(new \App\Notifications\DoktorKatildiBildirimi($currentDoctor));
            }

            return redirect()->route('hekim.panel')->with('basari', 'Kliniğe başarıyla katıldınız.');
        } else {
            // New Doctor, validate and register
            $request->validate([
                'ad_soyad' => 'required|string|max:255',
                'sifre' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:~^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>_\-#\[\]\\\/]).+$~',
                    'confirmed',
                ],
                'telefon' => ['required', 'string', 'regex:/^0\s\(5[0-9]{2}\)\s[0-9]{3}\s[0-9]{2}\s[0-9]{2}$/'],
                'unvan' => 'required|string|exists:unvanlar,ad',
                'branslar' => 'required|array|min:1',
                'branslar.*' => 'exists:branslar,id',
                'il_id' => 'required|exists:iller,id',
                'ilce_id' => 'required|string|max:255|exists:ilceler,ad',
            ]);

            $ilModel = Il::find($request->il_id);
            $ilceModel = Ilce::where('il_id', $ilModel?->id)->where('ad', $request->ilce_id)->first();

            $bransIsimleri = Brans::whereIn('id', $request->branslar)->pluck('ad')->toArray();
            $uzmanlikAlaniString = implode(', ', $bransIsimleri);

            $doktor = DB::transaction(function () use ($request, $davetiye, $uzmanlikAlaniString, $ilModel, $ilceModel) {
                $davetiye->update(['durum' => 'kabul_edildi']);

                $doktor = Doktor::create([
                    'ad_soyad' => $request->ad_soyad,
                    'e_posta' => $davetiye->davet_edilen_eposta,
                    'sifre' => Hash::make($request->sifre),
                    'telefon' => $request->telefon,
                    'il_id' => $ilModel?->id,
                    'ilce_id' => $ilceModel?->id,
                    'unvan' => $request->unvan,
                    'uzmanlik_alani' => $uzmanlikAlaniString,
                    'tur' => 'klinik',
                    'klinik_id' => $davetiye->klinik_id,
                    'klinik_rolu' => 'doktor',
                    'klinik_katilma_tarihi' => now(),
                    'klinik_aktif_mi' => true,
                    'aktif_mi' => true,
                ]);

                $doktor->branslar()->attach($request->branslar);

                $doktor->randevuAyari()->create([
                    'aktif_mi' => true,
                    'sure' => 30,
                    'fiyat' => 0,
                ]);

                return $doktor;
            });

            // Send notification to clinic owner
            $sahip = $davetiye->klinik->sahipDoktor;
            if ($sahip) {
                $sahip->notify(new \App\Notifications\DoktorKatildiBildirimi($doktor));
            }

            Auth::guard('doktor')->login($doktor);

            return redirect()->route('hekim.panel')->with('basari', 'Kaydınız oluşturuldu ve kliniğe katıldınız.');
        }
    }

    /**
     * Handle clinic invitation rejection.
     */
    public function davetReddet($token)
    {
        $davetiye = KlinikDavetiye::where('token', $token)->where('durum', 'beklemede')->firstOrFail();

        $davetiye->update(['durum' => 'reddedildi']);

        // Send notification to clinic owner
        $sahip = $davetiye->klinik->sahipDoktor;
        if ($sahip) {
            $doktorUser = Auth::guard('doktor')->user();
            $sahip->notify(new \App\Notifications\DoktorDavetReddettiBildirimi($doktorUser, $davetiye->davet_edilen_eposta));
        }

        return redirect('/')->with('basari', 'Klinik daveti reddedildi.');
    }

    /**
     * Display the announcements for the clinic member.
     */
    public function uyeDuyurular()
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;
        $duyurular = $klinik->duyurular()->where('aktif_mi', true)->orderBy('created_at', 'desc')->get();

        return view('klinik.duyurular', compact('klinik', 'duyurular'));
    }

    /**
     * Display the shared clinic patient pool for clinic members.
     */
    public function uyeHastalar()
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;
        $hastalar = $klinik->hastalar()->paginate(25);

        return view('klinik.hastalar', compact('klinik', 'hastalar'));
    }

    /**
     * Display the clinic information for members.
     */
    public function uyeBilgiler()
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;
        $doktorlar = $klinik->doktorlar()->where('aktif_mi', true)->get();

        return view('klinik.bilgiler', compact('klinik', 'doktorlar'));
    }

    /**
     * Handle clinic member leaving the clinic.
     */
    public function uyeAyril()
    {
        $doktor = Auth::guard('doktor')->user();
        if ($doktor->klinikSahibiMi()) {
            return back()->with('hata', 'Klinik sahibi hekim kliniğinden ayrılamaz.');
        }

        $klinik = $doktor->klinik;

        DB::transaction(function () use ($doktor) {
            $doktor->update([
                'klinik_id' => null,
                'klinik_rolu' => null,
                'klinik_katilma_tarihi' => null,
                'klinik_aktif_mi' => null,
                'paket_id' => null,
            ]);
        });

        // Notify clinic owner
        if ($klinik && $klinik->sahipDoktor) {
            $klinik->sahipDoktor->notify(new \App\Notifications\DoktorAyrildiBildirimi($doktor));
        }

        return redirect()->route('frontend.paketler')->with('basari', 'Klinikten başarıyla ayrıldınız. Hizmet vermeye devam etmek için bireysel paket satın alabilirsiniz.');
    }

    /**
     * Show detailed profile and metrics of a clinic doctor.
     */
    public function doktorDetay($id)
    {
        $klinik = Auth::guard('doktor')->user()->klinik;
        $doc = $klinik->doktorlar()->findOrFail($id);

        $buAyRandevuSayisi = $doc->randevular()
            ->whereMonth('tarih', now()->month)
            ->whereYear('tarih', now()->year)
            ->where('durum', '!=', 'iptal')
            ->count();

        $buAyGelir = (float) $doc->odemeler()
            ->whereMonth('odeme_tarihi', now()->month)
            ->whereYear('odeme_tarihi', now()->year)
            ->where('durum', '!=', 'iptal')
            ->sum('odenen_tutar');

        $sonRandevular = $doc->randevular()
            ->with('hasta', 'hizmet')
            ->orderBy('tarih', 'desc')
            ->orderBy('saat', 'desc')
            ->take(10)
            ->get();

        // Gelir grafiği: son 6 ay
        $aylar = [];
        $gelirler = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $aylar[] = $date->translatedFormat('F Y');
            $gelirler[] = (float) $doc->odemeler()
                ->whereMonth('odeme_tarihi', $date->month)
                ->whereYear('odeme_tarihi', $date->year)
                ->where('durum', '!=', 'iptal')
                ->sum('odenen_tutar');
        }

        return view('klinik.doktorlar_detay', compact('klinik', 'doc', 'buAyRandevuSayisi', 'buAyGelir', 'sonRandevular', 'aylar', 'gelirler'));
    }

    /**
     * Show edit doctor form.
     */
    public function doktorDuzenleFormu($id)
    {
        $klinik = Auth::guard('doktor')->user()->klinik;
        $doc = $klinik->doktorlar()->findOrFail($id);

        return view('klinik.doktorlar_duzenle', compact('klinik', 'doc'));
    }

    /**
     * Update doctor clinic settings.
     */
    public function doktorGuncelle(Request $request, $id)
    {
        $klinik = Auth::guard('doktor')->user()->klinik;
        $doc = $klinik->doktorlar()->findOrFail($id);

        $request->validate([
            'klinik_rolu' => 'required|in:doktor,sahip,ortak',
            'komisyon_orani' => 'required|numeric|min:0|max:100',
            'yetkiler' => 'nullable|array',
        ]);

        if ($doc->id === $klinik->sahip_doktor_id && $request->klinik_rolu !== 'sahip') {
            return back()->with('hata', 'Klinik sahibi hekimin rolü düşürülemez.');
        }

        // Construct permissions array
        $yetkiler = [];
        if ($request->klinik_rolu === 'sahip' || $request->klinik_rolu === 'ortak') {
            $yetkiler = [
                'yonetim_paneli' => true,
                'klinik_ayarlari' => true,
                'hekim_yonetimi' => true,
                'personel_yonetimi' => true,
                'finans_yonetimi' => true,
                'hakedis_yonetimi' => true,
                'ortak_hasta_havuzu' => true,
                'duyuru_yonetimi' => true,
            ];
        } else {
            $yetkiler = [
                'yonetim_paneli' => $request->has('yetkiler.yonetim_paneli'),
                'klinik_ayarlari' => $request->has('yetkiler.klinik_ayarlari'),
                'hekim_yonetimi' => $request->has('yetkiler.hekim_yonetimi'),
                'personel_yonetimi' => $request->has('yetkiler.personel_yonetimi'),
                'finans_yonetimi' => $request->has('yetkiler.finans_yonetimi'),
                'hakedis_yonetimi' => $request->has('yetkiler.hakedis_yonetimi'),
                'ortak_hasta_havuzu' => $request->has('yetkiler.ortak_hasta_havuzu'),
                'duyuru_yonetimi' => $request->has('yetkiler.duyuru_yonetimi'),
            ];
        }

        $doc->update([
            'klinik_rolu' => $request->klinik_rolu,
            'komisyon_orani' => $request->komisyon_orani,
            'klinik_yetkileri' => $yetkiler,
        ]);

        return redirect()->route('hekim.klinik.doktorlar')->with('basari', 'Hekim klinik ayarları başarıyla güncellendi.');
    }

    /**
     * Toggle doctor status.
     */
    public function doktorDurumToggle($id)
    {
        $klinik = Auth::guard('doktor')->user()->klinik;
        $doc = $klinik->doktorlar()->findOrFail($id);

        if ($doc->id === $klinik->sahip_doktor_id) {
            return back()->with('hata', 'Klinik sahibi pasifleştirilemez.');
        }

        $doc->update([
            'klinik_aktif_mi' => ! $doc->klinik_aktif_mi,
        ]);

        return back()->with('basari', 'Hekim aktiflik durumu güncellendi.');
    }

    /**
     * Show working hours grid of all clinic doctors.
     */
    public function doktorlarCalismaSaatleri()
    {
        $klinik = Auth::guard('doktor')->user()->klinik;
        $doktorlar = $klinik->doktorlar()
            ->where('aktif_mi', true)
            ->where('klinik_aktif_mi', true)
            ->with('calismaSaatleri')
            ->get();

        return view('klinik.doktorlar_calisma_saatleri', compact('klinik', 'doktorlar'));
    }

    /**
     * Show edit staff form.
     */
    public function personelDuzenleFormu($id)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        $personel = $klinik->personeller()->findOrFail($id);

        return view('klinik.personeller_duzenle', compact('klinik', 'personel'));
    }

    /**
     * Update staff member settings.
     */
    public function personelGuncelle(Request $request, $id)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        $personel = $klinik->personeller()->findOrFail($id);

        $request->validate([
            'ad_soyad' => 'required|string|max:255',
            'telefon' => 'nullable|string',
            'rol' => 'required|in:sekreter,muhasebeci,resepsiyonist',
            'yetkiler' => 'required|array',
        ]);

        // Construct permissions array
        $yetkiler = [
            'randevu' => $request->has('yetkiler.randevu'),
            'hasta' => $request->has('yetkiler.hasta'),
            'odeme' => $request->has('yetkiler.odeme'),
            'finans' => $request->has('yetkiler.finans'),
        ];

        $personel->update([
            'ad_soyad' => $request->ad_soyad,
            'telefon' => $request->telefon,
            'rol' => $request->rol,
            'yetkiler' => $yetkiler,
        ]);

        return redirect()->route('hekim.klinik.personeller')->with('basari', 'Personel bilgileri başarıyla güncellendi.');
    }

    /**
     * Reset staff password to a temporary one.
     */
    public function personelSifreSifirla($id)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        $personel = $klinik->personeller()->findOrFail($id);

        $geciciSifre = Str::random(10);

        $personel->update([
            'sifre' => Hash::make($geciciSifre),
            'sifre_degistirildi_mi' => false,
        ]);

        // Şifreyi flash’ta bir kez göster; log/mail’e yazma.
        session()->flash('gecici_sifre_goster', $geciciSifre);

        return redirect()->route('hekim.klinik.personeller')->with(
            'basari',
            'Personel şifresi sıfırlandı. Geçici şifre bir kez aşağıda gösterilir; personel ilk girişte değiştirir.'
        );
    }

    /**
     * Display clinic-wide financial overview with charts and tables.
     */
    public function finansGenelBakis(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        if (!$klinik) {
            return redirect()->route('hekim.panel')->with('hata', 'Kliniğiniz bulunamadı.');
        }

        $doktorIds = $klinik->doktorlar()->pluck('id')->toArray();

        // Date range filtering
        $baslangic = $request->input('baslangic', now()->startOfMonth()->toDateString());
        $bitis = $request->input('bitis', now()->endOfMonth()->toDateString());

        // 1. Revenues (Gelirler)
        $gelirlerQuery = DB::table('odemeler')
            ->whereIn('doktor_id', $doktorIds)
            ->whereBetween('odeme_tarihi', [$baslangic, $bitis])
            ->where('durum', '!=', 'iptal');

        $toplamGelir = (float) $gelirlerQuery->sum('odenen_tutar');
        $bekleyenOdeme = (float) ($gelirlerQuery->sum('tutar') - $toplamGelir);
        if ($bekleyenOdeme < 0) $bekleyenOdeme = 0;

        // 2. Expenses (Giderler)
        $toplamGider = (float) $klinik->giderler()
            ->whereBetween('tarih', [$baslangic, $bitis])
            ->sum('tutar');

        $netKar = $toplamGelir - $toplamGider;

        // 3. Doctor Revenue Distribution (Doktora göre gelir dağılımı)
        $doktorGelirleri = DB::table('odemeler')
            ->select('doktor_id', DB::raw('SUM(odenen_tutar) as toplam_gelir'))
            ->whereIn('doktor_id', $doktorIds)
            ->whereBetween('odeme_tarihi', [$baslangic, $bitis])
            ->where('durum', '!=', 'iptal')
            ->groupBy('doktor_id')
            ->get();

        $doktorlarList = $klinik->doktorlar()->get()->keyBy('id');
        $dagilim = [];
        foreach ($doktorGelirleri as $dg) {
            $doc = $doktorlarList->get($dg->doktor_id);
            if ($doc) {
                $oran = $toplamGelir > 0 ? round(($dg->toplam_gelir / $toplamGelir) * 100, 1) : 0;
                $dagilim[] = [
                    'ad_soyad' => ($doc->unvan ? $doc->unvan . ' ' : '') . $doc->ad_soyad,
                    'tutar' => (float)$dg->toplam_gelir,
                    'oran' => $oran
                ];
            }
        }

        // Sort distribution by revenue desc
        usort($dagilim, function($a, $b) {
            return $b['tutar'] <=> $a['tutar'];
        });

        // 4. Monthly Trend (Son 6 Ayın Gelir ve Gider Grafiği)
        $aylikTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $m = $date->month;
            $y = $date->year;

            $mGelir = (float) DB::table('odemeler')
                ->whereIn('doktor_id', $doktorIds)
                ->whereMonth('odeme_tarihi', $m)
                ->whereYear('odeme_tarihi', $y)
                ->where('durum', '!=', 'iptal')
                ->sum('odenen_tutar');

            $mGider = (float) $klinik->giderler()
                ->whereMonth('tarih', $m)
                ->whereYear('tarih', $y)
                ->sum('tutar');

            $aylikTrend[] = [
                'ay' => $date->translatedFormat('F Y'),
                'gelir' => $mGelir,
                'gider' => $mGider
            ];
        }

        return view('klinik.finans.index', compact(
            'klinik', 'toplamGelir', 'toplamGider', 'netKar', 'bekleyenOdeme',
            'baslangic', 'bitis', 'dagilim', 'aylikTrend'
        ));
    }

    /**
     * Display clinic reporting/statistics page.
     */
    public function raporlar(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        if (!$klinik) {
            return redirect()->route('hekim.panel')->with('hata', 'Kliniğiniz bulunamadı.');
        }

        $doktorIds = $klinik->doktorlar()->pluck('id')->toArray();

        // Date range filtering
        $baslangic = $request->input('baslangic', \Carbon\Carbon::now()->startOfMonth()->toDateString());
        $bitis = $request->input('bitis', \Carbon\Carbon::now()->endOfMonth()->toDateString());

        // 1. Appointment Counts & Status Distribution
        $randevuSorgu = Randevu::whereIn('doktor_id', $doktorIds)
            ->whereBetween('tarih', [$baslangic, $bitis]);

        $toplamRandevu = $randevuSorgu->count();
        
        $durumDagilimi = Randevu::whereIn('doktor_id', $doktorIds)
            ->whereBetween('tarih', [$baslangic, $bitis])
            ->select('durum', DB::raw('count(*) as adet'))
            ->groupBy('durum')
            ->get()
            ->keyBy('durum')
            ->map(fn($item) => $item->adet)
            ->toArray();

        // Default statuses if not present
        $durumDagilimi = array_merge([
            'beklemede' => 0,
            'onaylandi' => 0,
            'reddedildi' => 0,
            'iptal' => 0,
            'gelmedi' => 0,
            'tamamlandi' => 0,
        ], $durumDagilimi);

        // 2. Doctor Appointment Counts
        $doktorlar = $klinik->doktorlar()->get();
        $doktorRandevuSayilari = [];
        foreach ($doktorlar as $doc) {
            $adet = Randevu::where('doktor_id', $doc->id)
                ->whereBetween('tarih', [$baslangic, $bitis])
                ->count();
            $doktorRandevuSayilari[] = [
                'ad_soyad' => ($doc->unvan ? $doc->unvan . ' ' : '') . $doc->ad_soyad,
                'adet' => $adet
            ];
        }
        usort($doktorRandevuSayilari, fn($a, $b) => $b['adet'] <=> $a['adet']);

        // 3. En Çok Talep Edilen Hizmetler
        $populerHizmetler = DB::table('randevular')
            ->join('hizmetler', 'randevular.hizmet_id', '=', 'hizmetler.id')
            ->whereIn('randevular.doktor_id', $doktorIds)
            ->whereBetween('randevular.tarih', [$baslangic, $bitis])
            ->whereNull('randevular.deleted_at')
            ->select('hizmetler.ad as hizmet_ad', DB::raw('count(*) as adet'), DB::raw('SUM(hizmetler.fiyat) as tahmini_gelir'))
            ->groupBy('hizmetler.ad')
            ->orderByDesc('adet')
            ->limit(5)
            ->get();

        // 4. Patient growth (last 12 months)
        $hastaBuyume = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = \Carbon\Carbon::now()->subMonths($i);
            $m = $date->month;
            $y = $date->year;

            $adet = DB::table('klinik_hastalari')
                ->where('klinik_id', $klinik->id)
                ->whereMonth('kayit_tarihi', $m)
                ->whereYear('kayit_tarihi', $y)
                ->count();

            $hastaBuyume[] = [
                'ay' => $date->translatedFormat('F Y'),
                'adet' => $adet
            ];
        }

        // 5. Monthly Revenue vs Expense comparison (last 6 months)
        $finansKarsilastirma = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = \Carbon\Carbon::now()->subMonths($i);
            $m = $date->month;
            $y = $date->year;

            $gelir = (float) DB::table('odemeler')
                ->whereIn('doktor_id', $doktorIds)
                ->whereMonth('odeme_tarihi', $m)
                ->whereYear('odeme_tarihi', $y)
                ->where('durum', '!=', 'iptal')
                ->sum('odenen_tutar');

            $gider = (float) $klinik->giderler()
                ->whereMonth('tarih', $m)
                ->whereYear('tarih', $y)
                ->sum('tutar');

            $finansKarsilastirma[] = [
                'ay' => $date->translatedFormat('F Y'),
                'gelir' => $gelir,
                'gider' => $gider
            ];
        }

        return view('klinik.raporlar.index', compact(
            'klinik', 'baslangic', 'bitis', 'toplamRandevu', 'durumDagilimi',
            'doktorRandevuSayilari', 'populerHizmetler', 'hastaBuyume', 'finansKarsilastirma'
        ));
    }

    /**
     * Export clinic reports to PDF.
     */
    public function raporPdf(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();
        $klinik = $doktor->klinik;

        if (!$klinik) {
            return redirect()->route('hekim.panel')->with('hata', 'Kliniğiniz bulunamadı.');
        }

        $doktorIds = $klinik->doktorlar()->pluck('id')->toArray();

        $baslangic = $request->input('baslangic', \Carbon\Carbon::now()->startOfMonth()->toDateString());
        $bitis = $request->input('bitis', \Carbon\Carbon::now()->endOfMonth()->toDateString());

        // Gather all reporting data
        $randevuSorgu = Randevu::whereIn('doktor_id', $doktorIds)
            ->whereBetween('tarih', [$baslangic, $bitis]);

        $toplamRandevu = $randevuSorgu->count();
        
        $durumDagilimi = Randevu::whereIn('doktor_id', $doktorIds)
            ->whereBetween('tarih', [$baslangic, $bitis])
            ->select('durum', DB::raw('count(*) as adet'))
            ->groupBy('durum')
            ->get()
            ->keyBy('durum')
            ->map(fn($item) => $item->adet)
            ->toArray();

        $durumDagilimi = array_merge([
            'beklemede' => 0,
            'onaylandi' => 0,
            'reddedildi' => 0,
            'iptal' => 0,
            'gelmedi' => 0,
            'tamamlandi' => 0,
        ], $durumDagilimi);

        $doktorlar = $klinik->doktorlar()->get();
        $doktorRandevuSayilari = [];
        foreach ($doktorlar as $doc) {
            $adet = Randevu::where('doktor_id', $doc->id)
                ->whereBetween('tarih', [$baslangic, $bitis])
                ->count();
            $doktorRandevuSayilari[] = [
                'ad_soyad' => ($doc->unvan ? $doc->unvan . ' ' : '') . $doc->ad_soyad,
                'adet' => $adet
            ];
        }
        usort($doktorRandevuSayilari, fn($a, $b) => $b['adet'] <=> $a['adet']);

        $populerHizmetler = DB::table('randevular')
            ->join('hizmetler', 'randevular.hizmet_id', '=', 'hizmetler.id')
            ->whereIn('randevular.doktor_id', $doktorIds)
            ->whereBetween('randevular.tarih', [$baslangic, $bitis])
            ->whereNull('randevular.deleted_at')
            ->select('hizmetler.ad as hizmet_ad', DB::raw('count(*) as adet'), DB::raw('SUM(hizmetler.fiyat) as tahmini_gelir'))
            ->groupBy('hizmetler.ad')
            ->orderByDesc('adet')
            ->limit(10)
            ->get();

        $data = compact(
            'klinik', 'baslangic', 'bitis', 'toplamRandevu', 'durumDagilimi',
            'doktorRandevuSayilari', 'populerHizmetler'
        );

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('klinik.raporlar.pdf', $data);
        
        $filename = Str::slug($klinik->ad) . '_raporu_' . \Carbon\Carbon::parse($baslangic)->format('d_m_Y') . '_' . \Carbon\Carbon::parse($bitis)->format('d_m_Y') . '.pdf';

        return $pdf->download($filename);
    }
}
