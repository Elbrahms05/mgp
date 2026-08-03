<?php

use App\Http\Controllers\Api\Mobile\AuthController;
use App\Http\Controllers\Api\Mobile\ComplaintStatusController;
use App\Http\Controllers\Api\Mobile\FormController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - app mobile terrain (auth par jeton Sanctum)
|--------------------------------------------------------------------------
|
| ⚠️ À exposer derrière TLS 1.3 + certificat client des appareils (mTLS) en production.
| Les webhooks VBG/SMS et la cascade géographique sont dans routes/web.php.
|
*/

Route::prefix('mobile')->name('api.mobile.')->group(function () {

    // Authentification appareil (émission de jeton)
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me',      [AuthController::class, 'me'])->name('me');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        // Distribution des formulaires XForm F01–F07 (F03/VBG gaté)
        Route::get('forms',         [FormController::class, 'index'])->name('forms.index');
        Route::get('forms/{code}',  [FormController::class, 'show'])->name('forms.show');

        // Remontée d'état d'une plainte non sensible
        Route::get('complaints/{registrationNumber}/status', [ComplaintStatusController::class, 'show'])
            ->name('complaints.status');
    });
});

// Compat : /api/user (jeton Sanctum)
Route::get('/user', fn (\Illuminate\Http\Request $request) => $request->user())
    ->middleware('auth:sanctum');
