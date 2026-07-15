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
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
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
