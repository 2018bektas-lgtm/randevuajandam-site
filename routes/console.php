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
Schedule::command('abonelik:yenile')->dailyAt('07:00');
