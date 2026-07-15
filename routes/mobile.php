<?php

use App\Http\Controllers\Api\MobilePatientController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobil hasta uygulaması (React Native) — ana site
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [MobilePatientController::class, 'login'])->middleware('throttle:12,1');
    Route::post('/auth/register', [MobilePatientController::class, 'register'])->middleware('throttle:8,1');
    Route::post('/auth/social', [MobilePatientController::class, 'socialLogin'])->middleware('throttle:12,1');

    Route::get('/meta/filters', [MobilePatientController::class, 'filtersMeta'])->middleware('throttle:60,1');

    Route::get('/doctors', [MobilePatientController::class, 'doctors'])->middleware('throttle:60,1');
    Route::get('/doctors/{id}', [MobilePatientController::class, 'doctorShow'])->whereNumber('id')->middleware('throttle:60,1');
    Route::get('/doctors/{id}/slots', [MobilePatientController::class, 'slots'])->whereNumber('id')->middleware('throttle:60,1');

    Route::get('/clinics', [MobilePatientController::class, 'clinics'])->middleware('throttle:60,1');
    Route::get('/clinics/{id}', [MobilePatientController::class, 'clinicShow'])->whereNumber('id')->middleware('throttle:60,1');

    Route::get('/map/pins', [MobilePatientController::class, 'mapPins'])->middleware('throttle:60,1');
    Route::get('/blogs', [MobilePatientController::class, 'blogs'])->middleware('throttle:60,1');
    Route::get('/blogs/{id}', [MobilePatientController::class, 'blogShow'])->whereNumber('id')->middleware('throttle:60,1');
    Route::get('/services', [MobilePatientController::class, 'services'])->middleware('throttle:60,1');
    Route::get('/services/{id}', [MobilePatientController::class, 'serviceShow'])->whereNumber('id')->middleware('throttle:60,1');

    Route::middleware('hasta.mobile')->group(function () {
        Route::get('/auth/me', [MobilePatientController::class, 'me']);
        Route::post('/auth/logout', [MobilePatientController::class, 'logout']);
        Route::put('/auth/profile', [MobilePatientController::class, 'updateProfile']);
        Route::put('/auth/password', [MobilePatientController::class, 'updatePassword']);
        Route::get('/appointments', [MobilePatientController::class, 'myAppointments']);
        Route::post('/appointments', [MobilePatientController::class, 'book'])->middleware('throttle:15,1');
        Route::post('/appointments/{id}/cancel', [MobilePatientController::class, 'cancel'])->whereNumber('id');
    });
});
