<?php

use App\Http\Controllers\Frontend\HastaController;
use App\Http\Controllers\Frontend\HekimBlogController;
use App\Http\Controllers\Frontend\HekimController;
use App\Http\Controllers\Frontend\HekimEgitimController;
use App\Http\Controllers\Frontend\PublicEgitimController;
use App\Http\Controllers\Frontend\HekimFaqController;
use App\Http\Controllers\Frontend\HekimFinansController;
use App\Http\Controllers\Frontend\HekimFinansKategoriController;
use App\Http\Controllers\Frontend\HekimGaleriController;
use App\Http\Controllers\Frontend\HekimHizmetController;
use App\Http\Controllers\Frontend\HekimRandevuController;
use App\Http\Controllers\Frontend\HekimYorumController;
use App\Http\Controllers\Frontend\KlinikController;
use App\Http\Controllers\Frontend\KlinikWebSitesiController;
use App\Http\Controllers\Frontend\KlinikDuyuruController;
use App\Http\Controllers\Frontend\KlinikHastaController;
use App\Http\Controllers\Frontend\KlinikProfilController;
use App\Http\Controllers\Frontend\KlinikRandevuController;
use App\Http\Controllers\Frontend\LegalController;
use App\Http\Controllers\Frontend\PaketController;
use App\Http\Controllers\Frontend\PersonelAuthController;
use App\Http\Controllers\Frontend\PersonelHastaController;
use App\Http\Controllers\Frontend\PersonelOdemeController;
use App\Http\Controllers\Frontend\PersonelRandevuController;
use App\Models\Ilce;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Frontend Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your frontend application.
|
*/

// Password Reset Routes
Route::get('/sifremi-unuttum', [\App\Http\Controllers\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/sifremi-unuttum', [\App\Http\Controllers\ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->middleware(['throttle:5,1', 'recaptcha:sifre_sifirlama'])
    ->name('password.email');
Route::get('/sifre-sifirla/{token}', [\App\Http\Controllers\ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/sifre-sifirla', [\App\Http\Controllers\ForgotPasswordController::class, 'reset'])
    ->middleware('recaptcha:sifre_guncelleme')
    ->name('password.update');

// 2FA challenge (şifre sonrası, henüz oturum yok)
Route::get('/2fa', [\App\Http\Controllers\TwoFactorController::class, 'challengeForm'])->name('two-factor.challenge');
Route::post('/2fa', [\App\Http\Controllers\TwoFactorController::class, 'challengeVerify'])
    ->middleware('throttle:10,1')
    ->name('two-factor.challenge.post');
Route::post('/2fa/iptal', [\App\Http\Controllers\TwoFactorController::class, 'challengeCancel'])->name('two-factor.challenge.cancel');

Route::get('/paketler', [PaketController::class, 'index'])->name('frontend.paketler');
Route::get('/doktorlar', [HekimController::class, 'doktorlarListesi'])->name('frontend.hekimler');
Route::get('/doktorlar/arama', [HekimController::class, 'spotlightArama'])->name('frontend.doktorlar.arama');
Route::get('/doktorlar/en-yakin-slotlar', [HekimController::class, 'nextSlotsBatch'])
    ->middleware('throttle:60,1')
    ->name('frontend.doktorlar.next_slots');
Route::get('/doktorlar/{id}/slotlar', [HekimController::class, 'publicSlots'])
    ->whereNumber('id')
    ->middleware('throttle:60,1')
    ->name('frontend.doktorlar.slotlar');
Route::get('/doktorlar/{id}/musait-gunler', [HekimController::class, 'publicAvailableDays'])
    ->whereNumber('id')
    ->middleware('throttle:60,1')
    ->name('frontend.doktorlar.musait-gunler');
// Eski /hekimler URL'leri (query string korunur) → /doktorlar
Route::get('/hekimler', function (\Illuminate\Http\Request $request) {
    return redirect()->route('frontend.hekimler', $request->query(), 301);
});
Route::get('/hekimler/arama', [HekimController::class, 'spotlightArama']);
Route::get('/blog', [HekimController::class, 'bloglarListesi'])->name('frontend.blog.index');
Route::get('/egitimler', [PublicEgitimController::class, 'platformListe'])->name('frontend.egitimler.index');

// Yasal sayfalar (mobil + mağaza incelemesi)
Route::get('/gizlilik-politikasi', [LegalController::class, 'gizlilik'])->name('frontend.legal.gizlilik');
Route::get('/kullanim-kosullari', [LegalController::class, 'kullanim'])->name('frontend.legal.kullanim');
Route::get('/kvkk', [LegalController::class, 'kvkk'])->name('frontend.legal.kvkk');

// Patient Guest Routes (Hasta Ziyaretçi Rotaları)
Route::middleware('guest:hasta')->group(function () {
    Route::get('/kayit-ol', [HastaController::class, 'kayitFormu'])->name('frontend.hasta.kayit');
    Route::post('/kayit-ol', [HastaController::class, 'kayitOl'])
        ->middleware('recaptcha:hasta_kayit')
        ->name('frontend.hasta.kayit.post');
    Route::get('/giris', [HastaController::class, 'girisFormu'])->name('frontend.hasta.giris');
    Route::post('/giris', [HastaController::class, 'girisYap'])
        ->middleware('recaptcha:hasta_giris')
        ->name('frontend.hasta.giris.post');
});

// Misafir randevu (kayıt zorunlu değil)
Route::post('/randevu-misafir', [HastaController::class, 'randevuMisafirKaydet'])
    ->middleware('throttle:10,1')
    ->name('frontend.hasta.randevu.misafir');

// SMS OTP (misafir randevu + hasta kaydı)
Route::post('/sms-otp/gonder', [\App\Http\Controllers\Frontend\SmsOtpController::class, 'gonder'])
    ->middleware('throttle:12,1')
    ->name('frontend.sms-otp.gonder');
Route::post('/sms-otp/dogrula', [\App\Http\Controllers\Frontend\SmsOtpController::class, 'dogrula'])
    ->middleware('throttle:20,1')
    ->name('frontend.sms-otp.dogrula');

// Bekleme listesi (misafir + üye)
Route::post('/bekleme-listesi', [\App\Http\Controllers\Frontend\BeklemeListesiController::class, 'katil'])
    ->middleware('throttle:10,1')
    ->name('frontend.bekleme-listesi.katil');

// Eğitim başvurusu (misafir + üye)
Route::post('/egitim-basvuru', [PublicEgitimController::class, 'basvur'])
    ->middleware('throttle:10,1')
    ->name('frontend.egitim.basvuru');

// Token ile randevu yönetimi (girişsiz iptal / hesap oluştur / iCal)
// Platform online görüşme (WebRTC — hesap/Jitsi girişi yok)
Route::get('/gorusme/{token}', [\App\Http\Controllers\Frontend\GorusmeJoinController::class, 'join'])
    ->middleware('throttle:60,1')
    ->name('frontend.gorusme.join');
Route::match(['get', 'post'], '/gorusme/{token}/signal', [\App\Http\Controllers\Frontend\GorusmeJoinController::class, 'signalByToken'])
    ->middleware('throttle:120,1')
    ->name('frontend.gorusme.signal');

Route::get('/randevu-yonet/{token}', [\App\Http\Controllers\Frontend\RandevuYonetimController::class, 'goster'])
    ->name('frontend.randevu.yonet');
Route::get('/randevu-yonet/{token}/ical', [\App\Http\Controllers\Frontend\RandevuYonetimController::class, 'ical'])
    ->middleware('throttle:30,1')
    ->name('frontend.randevu.yonet.ical');
Route::post('/randevu-yonet/{token}/iptal', [\App\Http\Controllers\Frontend\RandevuYonetimController::class, 'iptal'])
    ->middleware('throttle:10,1')
    ->name('frontend.randevu.yonet.iptal');
Route::get('/randevu-yonet/{token}/hesap', [\App\Http\Controllers\Frontend\RandevuYonetimController::class, 'hesapFormu'])
    ->name('frontend.randevu.yonet.hesap');
Route::post('/randevu-yonet/{token}/hesap', [\App\Http\Controllers\Frontend\RandevuYonetimController::class, 'hesapOlustur'])
    ->middleware('throttle:10,1')
    ->name('frontend.randevu.yonet.hesap.post');

// Patient Auth Routes (Hasta Üye Rotaları)
Route::middleware('auth:hasta')->group(function () {
    Route::get('/profil', [HastaController::class, 'profil'])->name('frontend.hasta.profil');
    Route::post('/profil', [HastaController::class, 'profilGuncelle'])->name('frontend.hasta.profil.post');
    Route::get('/profil/randevular', [HastaController::class, 'randevular'])->name('frontend.hasta.randevular');
    Route::post('/profil/randevular/{randevu}/iptal', [HastaController::class, 'randevuIptal'])->name('frontend.hasta.randevu.iptal');
    Route::post('/randevu-kaydet', [HastaController::class, 'randevuKaydet'])->name('frontend.hasta.randevu.kaydet');
    Route::post('/yorum-kaydet', [HastaController::class, 'yorumKaydet'])->name('frontend.hasta.yorum.kaydet');
    Route::post('/cikis', [HastaController::class, 'cikisYap'])->name('frontend.hasta.cikis');
});

// Doctor Guest Routes
Route::middleware('guest:doktor')->group(function () {
    // Registration
    Route::get('/hekim/kayit-ol', [PaketController::class, 'kayitFormu'])->name('frontend.hekim.kayit');
    Route::post('/hekim/kayit-ol', [PaketController::class, 'kayitOl'])
        ->middleware('recaptcha:hekim_kayit')
        ->name('frontend.hekim.kayit.post');
    Route::get('/hekim/klinik/kayit-ol', function () {
        return redirect()->route('frontend.hekim.kayit');
    });

    // Login
    Route::get('/hekim/giris', [HekimController::class, 'girisFormu'])->name('frontend.hekim.giris');
    Route::post('/hekim/giris', [HekimController::class, 'girisYap'])
        ->middleware('recaptcha:hekim_giris')
        ->name('frontend.hekim.giris.post');
});

// Doctor Auth Routes (Without membership check) — kayıt / paket / domain / ödeme
Route::middleware(['auth:doktor'])->group(function () {
    Route::get('/hekim/paket-sec', [PaketController::class, 'paketSecFormu'])->name('frontend.hekim.paket_sec');
    Route::get('/hekim/paket-ode', [PaketController::class, 'paketOdeFormu'])->name('frontend.hekim.paket_ode');
    Route::post('/hekim/paket-ode', [PaketController::class, 'paketOde'])->name('frontend.hekim.paket_ode.post');
    Route::post('/hekim/paket-deneme', [PaketController::class, 'paketDenemeBaslat'])->name('frontend.hekim.paket_deneme');

    // Domain: ödeme ÖNCESİ (paket query) + ödeme sonrası (üyelik varken)
    Route::get('/hekim/onboarding/domain', [\App\Http\Controllers\Frontend\HekimOnboardingController::class, 'domain'])
        ->name('frontend.hekim.onboarding.domain');
    Route::post('/hekim/onboarding/domain/check', [\App\Http\Controllers\Frontend\HekimOnboardingController::class, 'domainCheck'])
        ->middleware('throttle:20,1')
        ->name('frontend.hekim.onboarding.domain.check');
    Route::post('/hekim/onboarding/domain/save', [\App\Http\Controllers\Frontend\HekimOnboardingController::class, 'domainSave'])
        ->name('frontend.hekim.onboarding.domain.save');
    Route::post('/hekim/onboarding/domain/skip-pre', [\App\Http\Controllers\Frontend\HekimOnboardingController::class, 'domainSkipPre'])
        ->name('frontend.hekim.onboarding.domain.skip_pre');
    Route::post('/hekim/onboarding/domain/byod', [\App\Http\Controllers\Frontend\HekimOnboardingController::class, 'domainByod'])
        ->name('frontend.hekim.onboarding.domain.byod');
    Route::post('/hekim/onboarding/domain/claim', [\App\Http\Controllers\Frontend\HekimOnboardingController::class, 'domainClaim'])
        ->name('frontend.hekim.onboarding.domain.claim');
    Route::post('/hekim/onboarding/domain/skip', [\App\Http\Controllers\Frontend\HekimOnboardingController::class, 'domainSkip'])
        ->name('frontend.hekim.onboarding.domain.skip');
});

// Doctor Auth Routes
Route::middleware(['auth:doktor', 'uyelik.kontrol'])->group(function () {
    // Success page
    Route::get('/hekim/basarili', [PaketController::class, 'basarili'])->name('frontend.hekim.basarili');

    // Clinic Upgrade
    Route::get('/hekim/klinik/gecis', [PaketController::class, 'gecisFormu'])->name('frontend.hekim.klinik.gecis');
    Route::post('/hekim/klinik/gecis', [PaketController::class, 'gecisYap'])->name('frontend.hekim.klinik.gecis.post');

    // Doctor Dashboard
    Route::get('/hekim/panel', [HekimController::class, 'panel'])->name('hekim.panel');
    Route::get('/hekim/gorusme/{id}', [\App\Http\Controllers\Frontend\GorusmeJoinController::class, 'hekimJoin'])
        ->whereNumber('id')
        ->name('hekim.gorusme.join');
    // Mobil uygulama içi WebView (access_token ile, tarayıcıya çıkmadan)
    Route::get('/hekim/gorusme/{id}/app', [\App\Http\Controllers\Frontend\GorusmeJoinController::class, 'hekimJoinApp'])
        ->whereNumber('id')
        ->name('hekim.gorusme.app');
    Route::match(['get', 'post'], '/hekim/gorusme/{id}/signal', [\App\Http\Controllers\Frontend\GorusmeJoinController::class, 'signalById'])
        ->whereNumber('id')
        ->middleware('throttle:120,1')
        ->name('hekim.gorusme.signal');

    // Profile Settings
    Route::get('/hekim/profil', [HekimController::class, 'profilDuzenle'])->name('hekim.profil');
    Route::post('/hekim/profil', [HekimController::class, 'profilGuncelle'])->name('hekim.profil.post');

    // Üyelik / abonelik iptal (dönem sonuna kadar erişim)
    Route::get('/hekim/uyelik', [\App\Http\Controllers\Frontend\HekimUyelikController::class, 'index'])->name('hekim.uyelik');
    Route::post('/hekim/uyelik/iptal', [\App\Http\Controllers\Frontend\HekimUyelikController::class, 'iptal'])->name('hekim.uyelik.iptal');

    // Change Password
    Route::get('/hekim/sifre-degistir', [HekimController::class, 'sifreFormu'])->name('hekim.sifre');
    Route::post('/hekim/sifre-degistir', [HekimController::class, 'sifreGuncelle'])->name('hekim.sifre.post');

    // 2FA kurulum (hekim)
    Route::get('/hekim/2fa', [\App\Http\Controllers\TwoFactorController::class, 'setupForm'])->name('hekim.two-factor');
    Route::post('/hekim/2fa/onayla', [\App\Http\Controllers\TwoFactorController::class, 'setupConfirm'])->name('hekim.two-factor.confirm');
    Route::post('/hekim/2fa/kapat', [\App\Http\Controllers\TwoFactorController::class, 'disable'])->name('hekim.two-factor.disable');
    Route::post('/hekim/2fa/yedek-kodlar', [\App\Http\Controllers\TwoFactorController::class, 'regenerateRecovery'])->name('hekim.two-factor.recovery');

    // About Me (Hakkımda) - Feature check
    Route::middleware(['paket.yetki:hakkimda'])->group(function () {
        Route::get('/hekim/hakkimda', [HekimController::class, 'hakkimdaFormu'])->name('hekim.hakkimda');
        Route::post('/hekim/hakkimda', [HekimController::class, 'hakkimdaGuncelle'])->name('hekim.hakkimda.post');
    });

    // Blog Management - Feature check
    Route::middleware(['paket.yetki:blog'])->group(function () {
        Route::get('/hekim/bloglar', [HekimBlogController::class, 'index'])->name('hekim.bloglar.index');
        Route::get('/hekim/bloglar/olustur', [HekimBlogController::class, 'create'])->name('hekim.bloglar.create');
        Route::post('/hekim/bloglar', [HekimBlogController::class, 'store'])->name('hekim.bloglar.store');
        Route::get('/hekim/bloglar/{bloglar}/duzenle', [HekimBlogController::class, 'edit'])->name('hekim.bloglar.edit');
        Route::put('/hekim/bloglar/{bloglar}', [HekimBlogController::class, 'update'])->name('hekim.bloglar.update');
        Route::delete('/hekim/bloglar/{bloglar}', [HekimBlogController::class, 'destroy'])->name('hekim.bloglar.destroy');
    });

    // Eğitimler - Feature check
    Route::middleware(['paket.yetki:egitimler'])->group(function () {
        Route::get('/hekim/egitimler', [HekimEgitimController::class, 'index'])->name('hekim.egitimler.index');
        Route::get('/hekim/egitimler/olustur', [HekimEgitimController::class, 'create'])->name('hekim.egitimler.create');
        Route::post('/hekim/egitimler', [HekimEgitimController::class, 'store'])->name('hekim.egitimler.store');
        // Tüm eğitim başvuruları (id rotasından önce)
        Route::get('/hekim/egitimler/basvurular', [HekimEgitimController::class, 'basvurularTumu'])->name('hekim.egitimler.basvurular.tumu');
        Route::get('/hekim/egitimler/{id}/duzenle', [HekimEgitimController::class, 'edit'])->name('hekim.egitimler.edit');
        Route::put('/hekim/egitimler/{id}', [HekimEgitimController::class, 'update'])->name('hekim.egitimler.update');
        Route::delete('/hekim/egitimler/{id}', [HekimEgitimController::class, 'destroy'])->name('hekim.egitimler.destroy');
        Route::get('/hekim/egitimler/{id}/basvurular', [HekimEgitimController::class, 'basvurular'])->name('hekim.egitimler.basvurular');
        Route::post('/hekim/egitimler/{id}/basvurular/{basvuruId}/durum', [HekimEgitimController::class, 'basvuruDurum'])->name('hekim.egitimler.basvuru.durum');
        Route::post('/hekim/egitimler/{id}/basvurular/{basvuruId}/odeme', [HekimEgitimController::class, 'basvuruOdeme'])->name('hekim.egitimler.basvuru.odeme');
    });

    // Service Management
    Route::get('/hekim/hizmetler', [HekimHizmetController::class, 'index'])->name('hekim.hizmetler.index');
    Route::get('/hekim/hizmetler/olustur', [HekimHizmetController::class, 'create'])->name('hekim.hizmetler.create');
    Route::post('/hekim/hizmetler', [HekimHizmetController::class, 'store'])->name('hekim.hizmetler.store');
    Route::get('/hekim/hizmetler/{hizmet}/duzenle', [HekimHizmetController::class, 'edit'])->name('hekim.hizmetler.edit');
    Route::put('/hekim/hizmetler/{hizmet}', [HekimHizmetController::class, 'update'])->name('hekim.hizmetler.update');
    Route::delete('/hekim/hizmetler/{hizmet}', [HekimHizmetController::class, 'destroy'])->name('hekim.hizmetler.destroy');

    // Appointment Management
    Route::get('/hekim/takvim', [HekimRandevuController::class, 'takvim'])->name('hekim.randevu.takvim');
    Route::get('/hekim/takvim/events', [HekimRandevuController::class, 'takvimEvents'])->name('hekim.randevu.takvim.events');
    Route::get('/hekim/takvim/ical', [HekimRandevuController::class, 'ical'])->name('hekim.randevu.ical');
    Route::post('/hekim/randevular/ekle', [HekimRandevuController::class, 'store'])->name('hekim.randevu.store');
    Route::post('/hekim/randevular/{id}/reschedule', [HekimRandevuController::class, 'reschedule'])->name('hekim.randevu.reschedule');
    Route::put('/hekim/randevular/{id}/guncelle', [HekimRandevuController::class, 'update'])->name('hekim.randevu.update');
    Route::delete('/hekim/randevular/{id}/sil', [HekimRandevuController::class, 'destroy'])->name('hekim.randevu.destroy');
    Route::get('/hekim/hastalar/ara', [HekimRandevuController::class, 'hastaAra'])->name('hekim.randevu.hastalar-ara');
    Route::post('/hekim/hastalar/ekle', [HekimRandevuController::class, 'hastaEkle'])->name('hekim.randevu.hasta-ekle');
    Route::post('/hekim/randevular/{id}/durum', [HekimRandevuController::class, 'durumGuncelle'])->name('hekim.randevu.durum-guncelle');
    Route::post('/hekim/randevular/periyot-guncelle', [HekimRandevuController::class, 'periyotGuncelle'])->name('hekim.randevu.update-period');

    // Appointment Requests - Feature check
    Route::middleware(['paket.yetki:randevu_talepleri'])->group(function () {
        Route::get('/hekim/talepler', [HekimRandevuController::class, 'talepler'])->name('hekim.randevu.talepler');
    });

    // Bekleme listesi (hekim paneli)
    Route::get('/hekim/bekleme-listesi', [\App\Http\Controllers\Frontend\BeklemeListesiController::class, 'index'])->name('hekim.randevu.bekleme-listesi');
    Route::post('/hekim/bekleme-listesi/{id}/durum', [\App\Http\Controllers\Frontend\BeklemeListesiController::class, 'durumGuncelle'])->name('hekim.randevu.bekleme-listesi.durum');
    Route::post('/hekim/bekleme-listesi/{id}/bildir', [\App\Http\Controllers\Frontend\BeklemeListesiController::class, 'bildir'])->name('hekim.randevu.bekleme-listesi.bildir');
    Route::delete('/hekim/bekleme-listesi/{id}', [\App\Http\Controllers\Frontend\BeklemeListesiController::class, 'sil'])->name('hekim.randevu.bekleme-listesi.sil');

    // Working Hours
    Route::get('/hekim/calisma-saatleri', [HekimRandevuController::class, 'calismaSaatleri'])->name('hekim.randevu.calisma-saatleri');
    Route::post('/hekim/calisma-saatleri', [HekimRandevuController::class, 'calismaSaatleriGuncelle'])->name('hekim.randevu.calisma-saatleri.post');

    // Patients
    Route::get('/hekim/hastalar', [HekimRandevuController::class, 'hastalar'])->name('hekim.randevu.hastalar');

    // Settings & Leaves
    Route::get('/hekim/randevu-ayarlari', [HekimRandevuController::class, 'ayarlar'])->name('hekim.randevu.ayarlar');
    Route::post('/hekim/randevu-ayarlari', [HekimRandevuController::class, 'ayarlarGuncelle'])->name('hekim.randevu.ayarlar.post');
    Route::post('/hekim/randevu-ayarlari/izin-ekle', [HekimRandevuController::class, 'izinEkle'])->name('hekim.randevu.izin-ekle');
    Route::delete('/hekim/randevu-ayarlari/izin-sil/{id}', [HekimRandevuController::class, 'izinSil'])->name('hekim.randevu.izin-sil');
    Route::get('/hekim/randevu-ayarlari/hizli-kapat-slotlar', [HekimRandevuController::class, 'hizliKapatSlotlar'])->name('hekim.randevu.hizli-kapat-slotlar');
    Route::post('/hekim/randevu-ayarlari/hizli-kapat', [HekimRandevuController::class, 'hizliKapatKaydet'])->name('hekim.randevu.hizli-kapat.post');

    // Yorum moderasyonu yalnızca site yönetimi — hekim paneli kapalı (yönlendirme)
    Route::get('/hekim/yorumlar', [HekimYorumController::class, 'index'])->name('hekim.yorumlar.index');
    Route::post('/hekim/yorumlar/{id}/yanitla', [HekimYorumController::class, 'yanitla'])->name('hekim.yorumlar.yanitla');

    // Finance Management - Feature check
    Route::middleware(['paket.yetki:finans'])->prefix('hekim/finans')->name('hekim.finans.')->group(function () {
        Route::get('/', [HekimFinansController::class, 'index'])->name('index');

        // Gelirler
        Route::get('/gelirler', [HekimFinansController::class, 'gelirler'])->name('gelirler');
        Route::post('/gelirler', [HekimFinansController::class, 'gelirKaydet'])->name('gelirler.store');
        Route::post('/gelirler/{id}/guncelle', [HekimFinansController::class, 'gelirGuncelle'])->name('gelirler.update');
        Route::delete('/gelirler/{id}', [HekimFinansController::class, 'gelirSil'])->name('gelirler.destroy');
        // Ödeme kalemleri
        Route::post('/gelirler/{id}/kalem', [HekimFinansController::class, 'gelirKalemEkle'])->name('gelirler.kalem.store');
        Route::delete('/gelirler/{odemeId}/kalem/{kalemId}', [HekimFinansController::class, 'gelirKalemSil'])->name('gelirler.kalem.destroy');

        // Giderler
        Route::get('/giderler', [HekimFinansController::class, 'giderler'])->name('giderler');
        Route::post('/giderler', [HekimFinansController::class, 'giderKaydet'])->name('giderler.store');
        Route::post('/giderler/{id}/guncelle', [HekimFinansController::class, 'giderGuncelle'])->name('giderler.update');
        Route::delete('/giderler/{id}', [HekimFinansController::class, 'giderSil'])->name('giderler.destroy');

        // Finans Kategorileri
        Route::get('/kategoriler', [HekimFinansKategoriController::class, 'index'])->name('kategoriler');
        Route::post('/kategoriler', [HekimFinansKategoriController::class, 'store'])->name('kategoriler.store');
        Route::post('/kategoriler/{id}/guncelle', [HekimFinansKategoriController::class, 'update'])->name('kategoriler.update');
        Route::delete('/kategoriler/{id}', [HekimFinansKategoriController::class, 'destroy'])->name('kategoriler.destroy');
        Route::post('/kategoriler/{id}/toggle', [HekimFinansKategoriController::class, 'toggleAktif'])->name('kategoriler.toggle');

        Route::get('/hasta-bakiyeleri', [HekimFinansController::class, 'hastaBakiyeleri'])->name('hasta-bakiyeleri');
        Route::get('/hasta/{hastaId}', [HekimFinansController::class, 'hastaHesap'])->name('hasta-hesap')->whereNumber('hastaId');
        Route::post('/hasta/{hastaId}/tahsilat', [HekimFinansController::class, 'hastaTahsilat'])->name('hasta-tahsilat')->whereNumber('hastaId');
        Route::post('/hasta/{hastaId}/borc', [HekimFinansController::class, 'hastaBorcEkle'])->name('hasta-borc')->whereNumber('hastaId');
        Route::get('/rapor/pdf', [HekimFinansController::class, 'raporPdf'])->name('rapor-pdf');
    });

    // SSS (FAQ) Yönetimi - Feature check
    Route::middleware(['paket.yetki:faq'])->group(function () {
        Route::get('/hekim/sss', [HekimFaqController::class, 'index'])->name('hekim.faqs.index');
        Route::post('/hekim/sss', [HekimFaqController::class, 'store'])->name('hekim.faqs.store');
        Route::post('/hekim/sss/{id}/guncelle', [HekimFaqController::class, 'update'])->name('hekim.faqs.update');
        Route::delete('/hekim/sss/{id}', [HekimFaqController::class, 'destroy'])->name('hekim.faqs.destroy');
        Route::post('/hekim/sss/{id}/toggle', [HekimFaqController::class, 'toggleAktif'])->name('hekim.faqs.toggle');
    });

    // Galeri Yönetimi - Feature check
    Route::middleware(['paket.yetki:galeri'])->group(function () {
        Route::get('/hekim/galeri', [HekimGaleriController::class, 'index'])->name('hekim.galeriler.index');
        Route::post('/hekim/galeri', [HekimGaleriController::class, 'store'])->name('hekim.galeriler.store');
        Route::post('/hekim/galeri/{id}/guncelle', [HekimGaleriController::class, 'update'])->name('hekim.galeriler.update');
        Route::delete('/hekim/galeri/{id}', [HekimGaleriController::class, 'destroy'])->name('hekim.galeriler.destroy');
        Route::post('/hekim/galeri/sirala', [HekimGaleriController::class, 'sirala'])->name('hekim.galeriler.sirala');
    });

    // Özel Web Sitesi Entegrasyonu - Feature check
    Route::middleware(['paket.yetki:web_sitesi'])->group(function () {
        Route::get('/hekim/web-sitesi/kurulum', [\App\Http\Controllers\Frontend\HekimWebSitesiController::class, 'kurulumFormu'])->name('hekim.web-sitesi.kurulum');
        Route::post('/hekim/web-sitesi/kurulum', [\App\Http\Controllers\Frontend\HekimWebSitesiController::class, 'kurulumYap'])->name('hekim.web-sitesi.kurulum.post');
        Route::post('/hekim/web-sitesi/api-anahtari', [\App\Http\Controllers\Frontend\HekimWebSitesiController::class, 'apiAnahtariOlustur'])->name('hekim.web-sitesi.api-anahtari.post');
        Route::post('/hekim/web-sitesi/platform-gorunurluk', [\App\Http\Controllers\Frontend\HekimWebSitesiController::class, 'platformGorunurluk'])->name('hekim.web-sitesi.platform-gorunurluk');
        Route::post('/hekim/web-sitesi/dns-dogrula', [\App\Http\Controllers\Frontend\HekimWebSitesiController::class, 'dnsVerify'])->name('hekim.web-sitesi.dns.verify');
        // Pakete dahil domain (ek ücret yok)
        Route::post('/hekim/web-sitesi/domain/check', [\App\Http\Controllers\Frontend\HekimWebSitesiController::class, 'domainCheck'])->name('hekim.web-sitesi.domain.check');
        Route::post('/hekim/web-sitesi/domain/claim', [\App\Http\Controllers\Frontend\HekimWebSitesiController::class, 'domainClaim'])->name('hekim.web-sitesi.domain.claim');
    });

    // Clinic Member routes (Any doctor in a clinic)
    Route::middleware(['klinik.uye'])->group(function () {
        Route::get('/hekim/klinik/duyurular', [KlinikController::class, 'uyeDuyurular'])->name('hekim.klinik.uye.duyurular');
        Route::get('/hekim/klinik/hastalar', [KlinikController::class, 'uyeHastalar'])->name('hekim.klinik.uye.hastalar');
        Route::get('/hekim/klinik/bilgiler', [KlinikController::class, 'uyeBilgiler'])->name('hekim.klinik.uye.bilgiler');
        Route::post('/hekim/klinik/ayril', [KlinikController::class, 'uyeAyril'])->name('hekim.klinik.uye.ayril');
    });

    // Clinic Administration & Operations
    Route::middleware(['klinik.uye'])->group(function () {
        
        // Clinic Panel (Dashboard)
        Route::middleware(['klinik.yetki:yonetim_paneli'])->group(function () {
            Route::get('/hekim/klinik/yonetim', [KlinikController::class, 'yonetimPanel'])->name('hekim.klinik.yonetim');
        });

        // Klinik takvim / talepler — tüm klinik paketlerinde temel erişim
        Route::get('/hekim/klinik/randevular/takvim', [KlinikRandevuController::class, 'takvim'])->name('hekim.klinik.randevular.takvim');
        Route::get('/hekim/klinik/randevular/takvim/events', [KlinikRandevuController::class, 'takvimEvents'])->name('hekim.klinik.randevular.takvim.events');
        Route::get('/hekim/klinik/randevular/talepler', [KlinikRandevuController::class, 'talepler'])->name('hekim.klinik.randevular.talepler');

        // Toplu onay/red — paket bayrağı: toplu_randevu_mi
        Route::middleware(['klinik.paket:toplu_randevu'])->group(function () {
            Route::post('/hekim/klinik/randevular/toplu-onay', [KlinikRandevuController::class, 'topluOnay'])->name('hekim.klinik.randevular.toplu-onay');
            Route::post('/hekim/klinik/randevular/toplu-red', [KlinikRandevuController::class, 'topluRed'])->name('hekim.klinik.randevular.toplu-red');
        });

        // Doctor Management
        Route::middleware(['klinik.yetki:hekim_yonetimi'])->group(function () {
            Route::get('/hekim/klinik/doktorlar', [KlinikController::class, 'doktorlar'])->name('hekim.klinik.doktorlar');
            Route::get('/hekim/klinik/doktorlar/calisma-saatleri', [KlinikController::class, 'doktorlarCalismaSaatleri'])->name('hekim.klinik.doktorlar.calisma-saatleri');
            Route::post('/hekim/klinik/doktorlar/davet', [KlinikController::class, 'davetEt'])->name('hekim.klinik.doktorlar.davet');
            Route::delete('/hekim/klinik/davetiye/{id}', [KlinikController::class, 'davetiyeIptal'])->name('hekim.klinik.davetiye.iptal');
            Route::get('/hekim/klinik/doktorlar/{id}', [KlinikController::class, 'doktorDetay'])->name('hekim.klinik.doktorlar.detay');
            Route::get('/hekim/klinik/doktorlar/{id}/duzenle', [KlinikController::class, 'doktorDuzenleFormu'])->name('hekim.klinik.doktorlar.duzenle');
            Route::post('/hekim/klinik/doktorlar/{id}/guncelle', [KlinikController::class, 'doktorGuncelle'])->name('hekim.klinik.doktorlar.guncelle');
            Route::post('/hekim/klinik/doktorlar/{id}/durum-toggle', [KlinikController::class, 'doktorDurumToggle'])->name('hekim.klinik.doktorlar.durum-toggle');
            Route::post('/hekim/klinik/doktorlar/{id}/cikar', [KlinikController::class, 'doktorCikar'])->name('hekim.klinik.doktorlar.cikar');
        });

        // Staff Management
        Route::middleware(['klinik.yetki:personel_yonetimi'])->group(function () {
            Route::get('/hekim/klinik/personeller', [KlinikController::class, 'personeller'])->name('hekim.klinik.personeller');
            Route::post('/hekim/klinik/personeller', [KlinikController::class, 'personelEkle'])->name('hekim.klinik.personeller.store');
            Route::get('/hekim/klinik/personeller/{id}/duzenle', [KlinikController::class, 'personelDuzenleFormu'])->name('hekim.klinik.personeller.duzenle');
            Route::post('/hekim/klinik/personeller/{id}/guncelle', [KlinikController::class, 'personelGuncelle'])->name('hekim.klinik.personeller.guncelle');
            Route::post('/hekim/klinik/personeller/{id}/sifre-sifirla', [KlinikController::class, 'personelSifreSifirla'])->name('hekim.klinik.personeller.sifre-sifirla');
            Route::post('/hekim/klinik/personeller/{id}/durum', [KlinikController::class, 'personelDurum'])->name('hekim.klinik.personeller.durum');
            Route::delete('/hekim/klinik/personeller/{id}', [KlinikController::class, 'personelSil'])->name('hekim.klinik.personeller.destroy');
        });

        // Shared Patient Pool — hasta_havuzu_mi
        Route::middleware(['klinik.yetki:ortak_hasta_havuzu', 'klinik.paket:hasta_havuzu'])->group(function () {
            Route::get('/hekim/klinik/hastalar-havuzu', [KlinikHastaController::class, 'index'])->name('hekim.klinik.hastalar.index');
            Route::get('/hekim/klinik/hastalar-havuzu/{id}', [KlinikHastaController::class, 'show'])->name('hekim.klinik.hastalar.show');
            Route::post('/hekim/klinik/hastalar-havuzu/{id}/not-guncelle', [KlinikHastaController::class, 'notGuncelle'])->name('hekim.klinik.hastalar.not-guncelle');
        });

        // Clinic Finances — merkezi_finans_mi
        Route::middleware(['klinik.yetki:finans_yonetimi', 'klinik.paket:merkezi_finans'])->group(function () {
            Route::get('/hekim/klinik/giderler', [KlinikController::class, 'giderler'])->name('hekim.klinik.giderler');
            Route::post('/hekim/klinik/giderler', [KlinikController::class, 'giderEkle'])->name('hekim.klinik.giderler.store');
            Route::delete('/hekim/klinik/giderler/{id}', [KlinikController::class, 'giderSil'])->name('hekim.klinik.giderler.destroy');
            Route::get('/hekim/klinik/finans', [KlinikController::class, 'finansGenelBakis'])->name('hekim.klinik.finans');
        });

        // Reports — raporlama_mi
        Route::middleware(['klinik.yetki:finans_yonetimi', 'klinik.paket:raporlama'])->group(function () {
            Route::get('/hekim/klinik/raporlar', [KlinikController::class, 'raporlar'])->name('hekim.klinik.raporlar');
            Route::get('/hekim/klinik/raporlar/pdf', [KlinikController::class, 'raporPdf'])->name('hekim.klinik.raporlar.pdf');
        });

        // Settlements / Hakediş — merkezi finans paketinde
        Route::middleware(['klinik.yetki:hakedis_yonetimi', 'klinik.paket:merkezi_finans'])->group(function () {
            Route::get('/hekim/klinik/hakedisler', [KlinikController::class, 'hakedisler'])->name('hekim.klinik.hakedisler');
            Route::post('/hekim/klinik/hakedisler/hesapla', [KlinikController::class, 'hakedisHesapla'])->name('hekim.klinik.hakedisler.hesapla');
            Route::post('/hekim/klinik/hakedisler/{id}/durum', [KlinikController::class, 'hakedisDurumGuncelle'])->name('hekim.klinik.hakedisler.durum');
        });

        // Clinic Settings
        Route::middleware(['klinik.yetki:klinik_ayarlari'])->group(function () {
            Route::get('/hekim/klinik/ayarlar', [KlinikController::class, 'ayarlar'])->name('hekim.klinik.ayarlar');
            Route::post('/hekim/klinik/ayarlar', [KlinikController::class, 'ayarlarGuncelle'])->name('hekim.klinik.ayarlar.post');
        });

        // Klinik Web Sitesi — route + paket: klinik_web_sitesi (Kurumsal)
        Route::middleware(['klinik.yetki:klinik_ayarlari', 'klinik.paket:klinik_web_sitesi'])->group(function () {
            Route::get('/hekim/klinik/web-sitesi', [KlinikWebSitesiController::class, 'kurulumFormu'])->name('hekim.klinik.web-sitesi.kurulum');
            Route::post('/hekim/klinik/web-sitesi', [KlinikWebSitesiController::class, 'kurulumYap'])->name('hekim.klinik.web-sitesi.kurulum.post');
            Route::post('/hekim/klinik/web-sitesi/api-anahtari', [KlinikWebSitesiController::class, 'apiAnahtariYenile'])->name('hekim.klinik.web-sitesi.api-anahtari');
            Route::post('/hekim/klinik/web-sitesi/domain/check', [KlinikWebSitesiController::class, 'domainCheck'])->name('hekim.klinik.web-sitesi.domain.check');
            Route::post('/hekim/klinik/web-sitesi/domain/claim', [KlinikWebSitesiController::class, 'domainClaim'])->name('hekim.klinik.web-sitesi.domain.claim');
        });

        // Clinic Announcement Management
        Route::middleware(['klinik.yetki:duyuru_yonetimi'])->group(function () {
            Route::get('/hekim/klinik/duyurular-yonetimi', [KlinikDuyuruController::class, 'index'])->name('hekim.klinik.duyurular.index');
            Route::post('/hekim/klinik/duyurular-yonetimi', [KlinikDuyuruController::class, 'store'])->name('hekim.klinik.duyurular.store');
            Route::get('/hekim/klinik/duyurular-yonetimi/{id}/duzenle', [KlinikDuyuruController::class, 'edit'])->name('hekim.klinik.duyurular.edit');
            Route::post('/hekim/klinik/duyurular-yonetimi/{id}/guncelle', [KlinikDuyuruController::class, 'update'])->name('hekim.klinik.duyurular.update');
            Route::post('/hekim/klinik/duyurular-yonetimi/{id}/toggle', [KlinikDuyuruController::class, 'toggle'])->name('hekim.klinik.duyurular.toggle');
            Route::delete('/hekim/klinik/duyurular-yonetimi/{id}', [KlinikDuyuruController::class, 'destroy'])->name('hekim.klinik.duyurular.destroy');
        });
    });

    // Logout
    Route::post('/hekim/cikis', [HekimController::class, 'cikisYap'])->name('hekim.cikis');
});

// Invitation Accept/Reject Routes (Accessible by guest/registered doctor)
Route::get('/klinik/davet/{token}', [KlinikController::class, 'davetGoster'])->name('frontend.hekim.klinik.davet.kabul');
Route::post('/klinik/davet/{token}/kabul', [KlinikController::class, 'davetKabul'])->name('frontend.hekim.klinik.davet.kabul.post');
Route::post('/klinik/davet/{token}/reddet', [KlinikController::class, 'davetReddet'])->name('frontend.hekim.klinik.davet.reddet');

// Personel Auth Routes
Route::get('/personel/giris', [PersonelAuthController::class, 'girisFormu'])->name('personel.giris');
Route::post('/personel/giris', [PersonelAuthController::class, 'girisYap'])
    ->middleware('recaptcha:personel_giris')
    ->name('personel.giris.post');
Route::post('/personel/cikis', [PersonelAuthController::class, 'cikisYap'])->name('personel.cikis');

// Personel Dashboard & Operations (Authenticated)
Route::middleware(['auth:personel'])->group(function () {
    Route::get('/personel/sifre-degistir', [PersonelAuthController::class, 'sifreFormu'])->name('personel.sifre-degistir');
    Route::post('/personel/sifre-degistir', [PersonelAuthController::class, 'sifreGuncelle'])->name('personel.sifre-degistir.post');

    // Personel Panel (After changing password)
    Route::middleware(['klinik.personel'])->group(function () {
        Route::get('/personel/panel', [PersonelAuthController::class, 'panel'])->name('personel.panel');

        // Randevu Yönetimi (Appointments Management)
        Route::get('/personel/randevular', [PersonelRandevuController::class, 'takvim'])->name('personel.randevular');
        Route::get('/personel/randevular/events', [PersonelRandevuController::class, 'takvimEvents'])->name('personel.randevular.events');
        Route::get('/personel/randevular/doktor-veri', [PersonelRandevuController::class, 'getDoktorVeri'])->name('personel.randevular.doktor-veri');
        Route::get('/personel/randevular/hastalar-ara', [PersonelRandevuController::class, 'hastalarAra'])->name('personel.randevular.hastalar-ara');
        Route::post('/personel/randevular/hasta-ekle', [PersonelRandevuController::class, 'hastaEkle'])->name('personel.randevular.hasta-ekle');
        Route::post('/personel/randevular/kaydet', [PersonelRandevuController::class, 'randevuKaydet'])->name('personel.randevular.kaydet');
        Route::post('/personel/randevular/{id}/reschedule', [PersonelRandevuController::class, 'randevuReschedule'])->name('personel.randevular.reschedule');
        Route::post('/personel/randevular/{id}/guncelle', [PersonelRandevuController::class, 'randevuGuncelle'])->name('personel.randevular.guncelle');
        Route::post('/personel/randevular/{id}/iptal', [PersonelRandevuController::class, 'randevuIptal'])->name('personel.randevular.iptal');

        Route::get('/personel/randevu-talepleri', [PersonelRandevuController::class, 'talepler'])->name('personel.randevular.talepler');
        Route::post('/personel/randevu-talepleri/{id}/onayla', [PersonelRandevuController::class, 'talepOnayla'])->name('personel.randevular.talep-onayla');
        Route::post('/personel/randevu-talepleri/{id}/reddet', [PersonelRandevuController::class, 'talepReddet'])->name('personel.randevular.talep-reddet');

        // Hasta Yönetimi (Patients Management)
        Route::get('/personel/hastalar', [PersonelHastaController::class, 'index'])->name('personel.hastalar.index');
        Route::post('/personel/hastalar', [PersonelHastaController::class, 'store'])->name('personel.hastalar.store');
        Route::get('/personel/hastalar/{id}', [PersonelHastaController::class, 'detay'])->name('personel.hastalar.detay');

        // Ödeme İşlemleri (Payments Management)
        Route::get('/personel/odemeler', [PersonelOdemeController::class, 'index'])->name('personel.odemeler.index');
        Route::post('/personel/odemeler', [PersonelOdemeController::class, 'store'])->name('personel.odemeler.store');
        Route::delete('/personel/odemeler/{id}', [PersonelOdemeController::class, 'destroy'])->name('personel.odemeler.destroy');
    });
});

Route::post('/api/iyzico/webhook', [\App\Http\Controllers\Frontend\IyzicoWebhookController::class, 'handle'])->name('api.iyzico.webhook');

Route::get('/iller/{il_id}/ilceler', function ($il_id) {
    return Cache::remember("ilceler_il_{$il_id}", 86400, function () use ($il_id) {
        return Ilce::where('il_id', $il_id)->orderBy('ad')->get(['id', 'ad']);
    });
})->name('api.ilceler');

// Public Clinic Profile Routes
Route::get('/{il_slug}/{ilce_slug}/klinik/{klinik_slug}', [KlinikProfilController::class, 'profil'])->name('frontend.klinik.profil');
Route::get('/{il_slug}/{ilce_slug}/klinik/{klinik_slug}/doktorlar', [KlinikProfilController::class, 'doktorlar'])->name('frontend.klinik.doktorlar');
Route::get('/{il_slug}/{ilce_slug}/klinik/{klinik_slug}/hizmetler', [KlinikProfilController::class, 'hizmetler'])->name('frontend.klinik.hizmetler');
Route::get('/{il_slug}/{ilce_slug}/klinik/{klinik_slug}/iletisim', [KlinikProfilController::class, 'iletisim'])->name('frontend.klinik.iletisim');

// Hiyerarşik Dizin Rotaları (Nested SEO Directory)
// Constraint: il_slug must not match reserved route prefixes
$reservedSlugs = '^(?!yonetim|hekim|giris|kayit-ol|profil|cikis|paketler|doktorlar|egitimler|blog|iller|up|api|personel|klinik)[\w-]+$';

Route::get('/{il_slug}', [HekimController::class, 'doktorlarListesi'])->name('frontend.il.liste')->where('il_slug', $reservedSlugs);
Route::get('/{il_slug}/{ilce_slug}', [HekimController::class, 'doktorlarListesi'])->name('frontend.ilce.liste')->where('il_slug', $reservedSlugs);
Route::get('/{il_slug}/{ilce_slug}/{brans_slug}', [HekimController::class, 'doktorlarListesi'])->name('frontend.brans.liste')->where('il_slug', $reservedSlugs);
Route::get('/{il_slug}/{ilce_slug}/{brans_slug}/{doctor_slug}', [HekimController::class, 'hekimDetay'])->name('frontend.hekim.detay')->where('il_slug', $reservedSlugs);
Route::get('/{il_slug}/{ilce_slug}/{brans_slug}/{doctor_slug}/blog/{blog_slug}', [HekimController::class, 'blogDetay'])->name('frontend.hekim.blog.detay')->where('il_slug', $reservedSlugs);
Route::get('/{il_slug}/{ilce_slug}/{brans_slug}/{doctor_slug}/hizmet/{hizmet_slug}', [HekimController::class, 'hizmetDetay'])->name('frontend.hekim.hizmet.detay')->where('il_slug', $reservedSlugs);
Route::get('/{il_slug}/{ilce_slug}/{brans_slug}/{doctor_slug}/egitimler', [PublicEgitimController::class, 'liste'])->name('frontend.hekim.egitimler')->where('il_slug', $reservedSlugs);
Route::get('/{il_slug}/{ilce_slug}/{brans_slug}/{doctor_slug}/egitim/{egitim_slug}', [PublicEgitimController::class, 'detay'])->name('frontend.hekim.egitim.detay')->where('il_slug', $reservedSlugs);
