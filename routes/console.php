<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

/*
 * Kuyruk isleyici.
 *
 * Bildirimlerin 24'u ShouldQueue; QUEUE_CONNECTION=sync iken bunlar HTTP
 * istegi icinde calisiyor ve SMTP/SMS/WhatsApp cagrilari randevu formunu
 * blokluyordu. Ayri bir daemon worker kurmak yerine, zaten calisan
 * `schedule:run` cron'u ile kuyrugu her dakika bosaltiyoruz.
 *
 * --stop-when-empty : kuyruk bosalinca cik (daemon degil)
 * --max-time=50     : bir sonraki dakikaya sarkma
 * withoutOverlapping: ust uste calismasin (kilit 5 dk sonra duser)
 */
Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3 --backoff=30')
    ->everyMinute()
    ->withoutOverlapping(5)
    ->runInBackground();

// Basarisiz islerin kaydi sonsuza kadar birikmesin (7 gun)
Schedule::command('queue:prune-failed --hours=168')->daily();

Schedule::command('randevu:hatirlat')->everyFiveMinutes();
Schedule::command('klinik:davet-suresi-kontrol')->daily();
Schedule::command('klinik:gider-tekrarla')->monthlyOn(1, '01:00');
Schedule::command('klinik:uyelik-hatirlat')->dailyAt('09:00');
Schedule::command('doktor:uyelik-hatirlat')->dailyAt('09:15');
// Excel: 90 gün hareketsiz ücretsiz vitrin profillerini gizle
Schedule::command('doktor:vitrin-temizlik')->dailyAt('03:30');
// PayTR kayıtlı kart (utoken/ctoken) Non3D yenileme — PAYTR_RECURRING_ENABLED gerekir
Schedule::command('abonelik:yenile')->dailyAt('07:00');
// KVKK: silme talebi 30 gunu gecen hasta kayitlarini imha et
Schedule::command('hasta:imha')->dailyAt('03:00');
// Google Takvim push notification kanallari (7 gunde bir dolar) — gunluk yenile
Schedule::command('google-calendar:renew-channels')->dailyAt('04:15');
