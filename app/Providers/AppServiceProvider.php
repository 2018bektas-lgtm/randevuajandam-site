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
                    ->limit(5)
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
                    ->take(5);

                if ($found->isNotEmpty()) {
                    return $found;
                }

                return \App\Models\Brans::query()
                    ->select(['id', 'ad', 'slug'])
                    ->orderBy('ad')
                    ->limit(5)
                    ->get();
            });

            $view->with('footerBranslar', $footerBranslar);
        });

        // Production safety rails (log critical misconfiguration)
        if ($this->app->environment('production')) {
            if (config('app.debug')) {
                \Illuminate\Support\Facades\Log::critical('APP_DEBUG=true production ortamında açık — kapatın!');
            }
            if (! config('services.iyzico.api_key') || ! config('services.iyzico.secret_key')) {
                \Illuminate\Support\Facades\Log::critical('IYZICO API anahtarları production ortamında eksik.');
            }
            if (! config('services.iyzico.webhook_secret')) {
                \Illuminate\Support\Facades\Log::critical('IYZICO_WEBHOOK_SECRET production ortamında eksik.');
            }
            if (config('sms.driver') === 'log') {
                \Illuminate\Support\Facades\Log::critical('SMS_DRIVER=log production ortamında — gerçek SMS sürücüsü ayarlayın.');
            }
            if (str_contains((string) config('services.iyzico.base_url'), 'sandbox')) {
                \Illuminate\Support\Facades\Log::critical('Iyzico base_url sandbox — production için https://api.iyzipay.com kullanın.');
            }
        }
    }
}
