<?php

namespace App\Providers;

use App\Events\RandevuDurumuDegisti;
use App\Events\RandevuOlusturuldu;
use App\Listeners\RandevuBildirimleriniGonder;
use App\Listeners\RandevuFinansKaydet;
use App\Listeners\RandevuLogKaydet;
use App\Models\Blog;
use App\Models\Hizmet;
use App\Models\Klinik;
use App\Models\Randevu;
use App\Policies\BlogPolicy;
use App\Policies\HizmetPolicy;
use App\Policies\KlinikPolicy;
use App\Policies\RandevuPolicy;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Policies
        Gate::policy(Blog::class, BlogPolicy::class);
        Gate::policy(Hizmet::class, HizmetPolicy::class);
        Gate::policy(Randevu::class, RandevuPolicy::class);
        Gate::policy(Klinik::class, KlinikPolicy::class);

        // Event Listeners
        Event::listen(RandevuOlusturuldu::class, [RandevuLogKaydet::class, 'olusturuldu']);
        Event::listen(RandevuOlusturuldu::class, [RandevuBildirimleriniGonder::class, 'olusturuldu']);
        Event::listen(RandevuDurumuDegisti::class, [RandevuLogKaydet::class, 'durumDegisti']);
        Event::listen(RandevuDurumuDegisti::class, [RandevuBildirimleriniGonder::class, 'durumDegisti']);
        Event::listen(RandevuDurumuDegisti::class, [RandevuFinansKaydet::class, 'durumDegisti']);

        // Footer: popüler branşlar (gerçek slug + uzmanlık adı ile filtre)
        View::composer('frontend.layouts.partials.footer', function ($view) {
            $footerBranslar = Cache::remember('footer:populer_branslar', now()->addMinutes(30), function () {
                $withDoctors = \App\Models\Brans::query()
                    ->select(['id', 'ad', 'slug'])
                    ->withCount(['doktorlar' => function ($q) {
                        $q->where('aktif_mi', true);
                    }])
                    ->whereHas('doktorlar', function ($q) {
                        $q->where('aktif_mi', true);
                    })
                    ->orderByDesc('doktorlar_count')
                    ->limit(4)
                    ->get();

                if ($withDoctors->isNotEmpty()) {
                    return $withDoctors;
                }

                // Henüz aktif hekim yoksa bilinen popüler branşları slug ile bul
                $preferredSlugs = [
                    'psikoloji',
                    'beslenme-ve-diyetetik',
                    'dis-hekimligi',
                    'kadin-hastaliklari-ve-dogum',
                    'dermatoloji-cildiye',
                    'aile-hekimligi',
                    'kardiyoloji',
                ];

                $found = \App\Models\Brans::query()
                    ->select(['id', 'ad', 'slug'])
                    ->whereIn('slug', $preferredSlugs)
                    ->get()
                    ->sortBy(fn ($b) => array_search($b->slug, $preferredSlugs, true))
                    ->values()
                    ->take(4);

                if ($found->isNotEmpty()) {
                    return $found;
                }

                return \App\Models\Brans::query()
                    ->select(['id', 'ad', 'slug'])
                    ->orderBy('ad')
                    ->limit(4)
                    ->get();
            });

            $view->with('footerBranslar', $footerBranslar);
        });

        // Production safety rails (log critical misconfiguration)
        if ($this->app->environment('production')) {
            if (config('app.debug')) {
                \Illuminate\Support\Facades\Log::critical('APP_DEBUG=true production ortamında açık — kapatın!');
            }
            // PayTR: .env veya site_ayarlari (yönetim paneli) — ikisinden biri yeterli
            $paytrId = (string) config('services.paytr.merchant_id', '');
            $paytrKey = (string) config('services.paytr.merchant_key', '');
            $paytrSalt = (string) config('services.paytr.merchant_salt', '');
            $paytrTest = (bool) config('services.paytr.test_mode', true);
            try {
                $sa = \App\Models\SiteAyari::query()->first();
                if ($sa) {
                    if (filled($sa->paytr_merchant_id)) {
                        $paytrId = (string) $sa->paytr_merchant_id;
                    }
                    if (filled($sa->paytr_merchant_key)) {
                        $paytrKey = (string) $sa->paytr_merchant_key;
                    }
                    if (filled($sa->paytr_merchant_salt)) {
                        $paytrSalt = (string) $sa->paytr_merchant_salt;
                    }
                    if ($sa->paytr_test_mode !== null) {
                        $paytrTest = (bool) $sa->paytr_test_mode;
                    }
                }
            } catch (\Throwable) {
                // boot sırasında DB yoksa env ile devam
            }
            if ($paytrId === '' || $paytrKey === '' || $paytrSalt === '') {
                \Illuminate\Support\Facades\Log::critical('PAYTR merchant bilgileri production ortamında eksik.');
            }
            if (config('sms.driver') === 'log') {
                \Illuminate\Support\Facades\Log::critical('SMS_DRIVER=log production ortamında — gerçek SMS sürücüsü ayarlayın.');
            }
            if ((bool) config('services.iyzico.enabled', false)) {
                \Illuminate\Support\Facades\Log::warning('IYZICO_ENABLED=true — platform PayTR-only; iyzico kapatılmalı.');
            }
            if ($paytrTest) {
                \Illuminate\Support\Facades\Log::critical('PAYTR_TEST_MODE=true production’da kapalı olmalı.');
            }
        }
    }
}
