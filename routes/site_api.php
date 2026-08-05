<?php

use App\Http\Controllers\Api\SiteAsistanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Harici Hekim / Klinik Web Sitesi API
|--------------------------------------------------------------------------
| Base: /api/site/v1
|
| Kimlik doğrulama: X-Api-Key + X-Api-Secret başlıkları
| (ApiKey modeli — her hekimin/kliniğin web sitesi kurulum ekranından)
|
| Bu rota grubu CSRF gerektirmez (middleware: api + site.api).
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware('site.api')->group(function () {

    // AI Asistan
    Route::post('/asistan/mesaj', [SiteAsistanController::class, 'mesaj'])
        ->middleware('throttle:100,1440') // günde 100 istek (1440 dk)
        ->name('site.asistan.mesaj');

});
