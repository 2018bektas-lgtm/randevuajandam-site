<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

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
