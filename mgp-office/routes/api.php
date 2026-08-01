<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Les routes Sanctum pour l'app mobile (auth par token) sont définies ici.
| Les webhooks VBG et les routes géographie cascade sont dans routes/web.php.
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (\Illuminate\Http\Request $request) {
        return $request->user();
    });
});
