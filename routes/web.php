<?php

use App\Http\Controllers\BransController;
use App\Http\Controllers\DoktorController;
use App\Http\Controllers\Frontend\AnasayfaController;
use App\Http\Controllers\PaketController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\UnvanController;
use App\Http\Controllers\YoneticiController;
use App\Http\Controllers\YonetimController;
use App\Http\Controllers\YorumController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AnasayfaController::class, 'index']);

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::prefix('yonetim')->name('yonetim.')->group(function () {
    // Giriş yapmamış yöneticiler için
    Route::middleware('guest:yonetici')->group(function () {
        Route::get('/giris', [YonetimController::class, 'girisFormu'])->name('giris');
        Route::post('/giris', [YonetimController::class, 'girisYap'])
            ->middleware('recaptcha:yonetici_giris')
            ->name('giris.post');
    });

    // Giriş yapmış yöneticiler için
    Route::middleware('auth:yonetici')->group(function () {
        Route::get('/', [YonetimController::class, 'panel'])->name('panel');
        Route::post('/cikis', [YonetimController::class, 'cikisYap'])->name('cikis');

        // Platform operasyon (salt okunur özetler)
        Route::get('/randevular', [YonetimController::class, 'randevular'])->name('randevular');
        Route::get('/hastalar', [YonetimController::class, 'hastalar'])->name('hastalar');
        Route::get('/uyelikler', [YonetimController::class, 'uyelikler'])->name('uyelikler');

        // 2FA kurulum
        Route::get('/2fa', [\App\Http\Controllers\TwoFactorController::class, 'setupForm'])->name('two-factor');
        Route::post('/2fa/onayla', [\App\Http\Controllers\TwoFactorController::class, 'setupConfirm'])->name('two-factor.confirm');
        Route::post('/2fa/kapat', [\App\Http\Controllers\TwoFactorController::class, 'disable'])->name('two-factor.disable');
        Route::post('/2fa/yedek-kodlar', [\App\Http\Controllers\TwoFactorController::class, 'regenerateRecovery'])->name('two-factor.recovery');

        // Yönetici Yönetimi
        Route::prefix('yoneticiler')->name('yoneticiler.')->group(function () {
            Route::get('/', [YoneticiController::class, 'index'])->name('index');
            Route::get('/ekle', [YoneticiController::class, 'create'])->name('ekle');
            Route::post('/ekle', [YoneticiController::class, 'store'])->name('store');
            Route::get('/duzenle/{id}', [YoneticiController::class, 'edit'])->name('duzenle');
            Route::post('/duzenle/{id}', [YoneticiController::class, 'update'])->name('update');
            Route::post('/sil/{id}', [YoneticiController::class, 'destroy'])->name('sil');
            Route::post('/durum/{id}', [YoneticiController::class, 'toggleDurum'])->name('durum');
        });

        // Doktor Yönetimi
        Route::prefix('doktorlar')->name('doktorlar.')->group(function () {
            Route::get('/', [DoktorController::class, 'index'])->name('index');
            Route::get('/meslek-kuyruk', [DoktorController::class, 'meslekKuyruk'])->name('meslek-kuyruk');
            Route::get('/duzenle/{id}', [DoktorController::class, 'edit'])->name('duzenle');
            Route::post('/duzenle/{id}', [DoktorController::class, 'update'])->name('update');
            Route::post('/{id}/meslek-dogrula', [DoktorController::class, 'meslekDogrula'])->name('meslek-dogrula');
            Route::get('/{id}/meslek-belge', [DoktorController::class, 'meslekBelgeGoster'])->name('meslek-belge');
            Route::post('/sil/{id}', [DoktorController::class, 'destroy'])->name('sil');
            Route::post('/durum/{id}', [DoktorController::class, 'toggleDurum'])->name('durum');
        });

        Route::get('/edevlet-loglari', [DoktorController::class, 'edevletLoglari'])->name('edevlet-loglari');
        Route::get('/faturalar', [DoktorController::class, 'faturalar'])->name('faturalar');
        Route::post('/faturalar/{id}', [DoktorController::class, 'faturaDurumGuncelle'])->name('faturalar.guncelle');

        // Klinik Yönetimi
        Route::prefix('klinikler')->name('klinikler.')->group(function () {
            Route::get('/', [\App\Http\Controllers\KlinikYonetimController::class, 'index'])->name('index');
            Route::get('/duzenle/{id}', [\App\Http\Controllers\KlinikYonetimController::class, 'edit'])->name('duzenle');
            Route::post('/duzenle/{id}', [\App\Http\Controllers\KlinikYonetimController::class, 'update'])->name('update');
            Route::post('/sil/{id}', [\App\Http\Controllers\KlinikYonetimController::class, 'destroy'])->name('sil');
            Route::post('/durum/{id}', [\App\Http\Controllers\KlinikYonetimController::class, 'toggleDurum'])->name('durum');
            Route::post('/{klinik_id}/doktor-cikar/{doktor_id}', [\App\Http\Controllers\KlinikYonetimController::class, 'cikar'])->name('doktor-cikar');
        });

        // Paket Yönetimi
        Route::prefix('paketler')->name('paketler.')->group(function () {
            Route::get('/', [PaketController::class, 'index'])->name('index');
            Route::get('/ekle', [PaketController::class, 'create'])->name('ekle');
            Route::post('/ekle', [PaketController::class, 'store'])->name('store');
            Route::get('/duzenle/{id}', [PaketController::class, 'edit'])->name('duzenle');
            Route::post('/duzenle/{id}', [PaketController::class, 'update'])->name('update');
            Route::post('/sil/{id}', [PaketController::class, 'destroy'])->name('sil');
            Route::post('/durum/{id}', [PaketController::class, 'toggleDurum'])->name('durum');
        });

        // Branş Yönetimi
        Route::prefix('branslar')->name('branslar.')->group(function () {
            Route::get('/', [BransController::class, 'index'])->name('index');
            Route::get('/ekle', [BransController::class, 'create'])->name('ekle');
            Route::post('/ekle', [BransController::class, 'store'])->name('store');
            Route::get('/duzenle/{id}', [BransController::class, 'edit'])->name('duzenle');
            Route::post('/duzenle/{id}', [BransController::class, 'update'])->name('update');
            Route::post('/sil/{id}', [BransController::class, 'destroy'])->name('sil');
        });

        // Unvan Yönetimi
        Route::prefix('unvanlar')->name('unvanlar.')->group(function () {
            Route::get('/', [UnvanController::class, 'index'])->name('index');
            Route::get('/ekle', [UnvanController::class, 'create'])->name('ekle');
            Route::post('/ekle', [UnvanController::class, 'store'])->name('store');
            Route::get('/duzenle/{id}', [UnvanController::class, 'edit'])->name('duzenle');
            Route::post('/duzenle/{id}', [UnvanController::class, 'update'])->name('update');
            Route::post('/sil/{id}', [UnvanController::class, 'destroy'])->name('sil');
        });

        // Yorum Moderasyonu
        Route::prefix('yorumlar')->name('yorumlar.')->group(function () {
            Route::get('/', [YorumController::class, 'index'])->name('index');
            Route::post('/{id}/onayla', [YorumController::class, 'onayla'])->name('onayla');
            Route::post('/{id}/reddet', [YorumController::class, 'reddet'])->name('reddet');
            Route::post('/{id}/sil', [YorumController::class, 'sil'])->name('sil');
        });

        // Hizmet Yönetimi
        Route::prefix('hizmetler')->name('hizmetler.')->group(function () {
            Route::get('/', [\App\Http\Controllers\YonetimIcerikController::class, 'hizmetler'])->name('index');
            Route::post('/sil/{id}', [\App\Http\Controllers\YonetimIcerikController::class, 'hizmetSil'])->name('sil');
            Route::post('/durum/{id}', [\App\Http\Controllers\YonetimIcerikController::class, 'hizmetDurum'])->name('durum');
        });

        // Blog Yönetimi
        Route::prefix('bloglar')->name('bloglar.')->group(function () {
            Route::get('/', [\App\Http\Controllers\YonetimIcerikController::class, 'bloglar'])->name('index');
            Route::post('/sil/{id}', [\App\Http\Controllers\YonetimIcerikController::class, 'blogSil'])->name('sil');
        });

        // SSS (FAQ) Yönetimi
        Route::prefix('faqs')->name('faqs.')->group(function () {
            Route::get('/', [\App\Http\Controllers\YonetimIcerikController::class, 'faqs'])->name('index');
            Route::post('/sil/{id}', [\App\Http\Controllers\YonetimIcerikController::class, 'faqSil'])->name('sil');
            Route::post('/durum/{id}', [\App\Http\Controllers\YonetimIcerikController::class, 'faqDurum'])->name('durum');
        });

        // Galeri Yönetimi
        Route::prefix('galeriler')->name('galeriler.')->group(function () {
            Route::get('/', [\App\Http\Controllers\YonetimIcerikController::class, 'galeriler'])->name('index');
            Route::post('/sil/{id}', [\App\Http\Controllers\YonetimIcerikController::class, 'galeriSil'])->name('sil');
        });

        // SEO Settings
        Route::get('/seo-ayarlari', [YonetimController::class, 'seoFormu'])->name('seo');
        Route::post('/seo-ayarlari', [YonetimController::class, 'seoGuncelle'])->name('seo.post');

        // Payment settings and bank-transfer membership approvals
        Route::get('/odeme-ayarlari', [YonetimController::class, 'odemeAyarlariFormu'])->name('odeme-ayarlari');
        Route::post('/odeme-ayarlari', [YonetimController::class, 'odemeAyarlariGuncelle'])->name('odeme-ayarlari.post');
        Route::get('/uyelik-odemeleri', [\App\Http\Controllers\UyelikOdemeController::class, 'index'])->name('uyelik-odemeleri.index');
        Route::post('/uyelik-odemeleri/{id}/onayla', [\App\Http\Controllers\UyelikOdemeController::class, 'onayla'])->name('uyelik-odemeleri.onayla');
        Route::get('/referanslar', [\App\Http\Controllers\YonetimReferansController::class, 'index'])->name('referanslar.index');
        Route::post('/referanslar/{id}/iptal', [\App\Http\Controllers\YonetimReferansController::class, 'iptal'])->name('referanslar.iptal');
    });
});
