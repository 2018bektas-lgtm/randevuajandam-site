<?php

use App\Http\Middleware\KlinikLimitMiddleware;
use App\Http\Middleware\KlinikPaketOzellikMiddleware;
use App\Http\Middleware\KlinikPersonelMiddleware;
use App\Http\Middleware\KlinikSahibiMiddleware;
use App\Http\Middleware\KlinikUyeMiddleware;
use App\Http\Middleware\UyelikKontrol;
use App\Http\Middleware\PaketYetkiKontrol;
use App\Http\Middleware\KlinikYetkiMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/frontend.php'));

            // Mobil hasta uygulaması JSON API
            Route::middleware('api')
                ->prefix('api/mobile')
                ->group(base_path('routes/mobile.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'uyelik.kontrol' => UyelikKontrol::class,
            'klinik.sahip' => KlinikSahibiMiddleware::class,
            'klinik.uye' => KlinikUyeMiddleware::class,
            'klinik.personel' => KlinikPersonelMiddleware::class,
            'klinik.limit' => KlinikLimitMiddleware::class,
            'paket.yetki' => PaketYetkiKontrol::class,
            'klinik.yetki' => KlinikYetkiMiddleware::class,
            'klinik.paket' => KlinikPaketOzellikMiddleware::class,
            'hasta.mobile' => \App\Http\Middleware\HastaMobileToken::class,
            'doktor.mobile' => \App\Http\Middleware\DoktorMobileToken::class,
            'personel.mobile' => \App\Http\Middleware\PersonelMobileToken::class,
            'recaptcha' => \App\Http\Middleware\VerifyRecaptcha::class,
        ]);

        $middleware->append(\App\Http\Middleware\ForceHttps::class);

        // Public doctor-site + panel APIs live in the separate `api/` project.
        // Iyzico webhook stays on the main site (frontend route).
        $middleware->validateCsrfTokens(except: [
            'api/iyzico/webhook',
            'api/mobile/*',
        ]);

        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('yonetim') || $request->is('yonetim/*')) {
                return route('yonetim.giris');
            }
            if ($request->is('hekim') || $request->is('hekim/*')) {
                return route('frontend.hekim.giris');
            }

            return route('frontend.hasta.giris');
        });

        $middleware->redirectUsersTo(function () {
            if (auth('yonetici')->check()) {
                return route('yonetim.panel');
            }
            if (auth('doktor')->check()) {
                return route('hekim.panel');
            }

            return '/';
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
