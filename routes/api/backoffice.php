<?php

use App\Http\Controllers\Api\Backoffice\AuthController;
use App\Http\Middleware\EnsureBackofficeEnabled;
use Illuminate\Support\Facades\Route;

/*
| Backoffice API skeleton: auth + ping only.
| Business resources intentionally removed.
*/

Route::prefix('backoffice')->middleware(EnsureBackofficeEnabled::class)->group(function (): void {
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword'])
        ->middleware('throttle:5,1');
    Route::get('auth/me', [AuthController::class, 'me']);

    Route::middleware('backoffice.auth')->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::get('ping', static function () {
            return response()->json(['ok' => true]);
        });
    });
});
