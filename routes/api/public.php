<?php

use App\Http\Controllers\Api\PublicApi\CatalogController;
use App\Http\Controllers\Api\PublicApi\ClientController;
use App\Http\Controllers\Api\PublicApi\ContentController;
use App\Http\Controllers\Api\PublicApi\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('public')->group(function (): void {
    Route::apiSingleton('content', ContentController::class)->only(['show']);
    Route::apiSingleton('catalog', CatalogController::class)->only(['show']);

    Route::prefix('client')->group(function (): void {
        Route::post('register', [ClientController::class, 'register']);
        Route::post('login', [ClientController::class, 'login']);
        Route::post('forgot-password', [ClientController::class, 'forgotPassword'])
            ->middleware('throttle:5,1');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('profile', [ClientController::class, 'profile']);
            Route::patch('profile', [ClientController::class, 'updateProfile']);
            Route::post('addresses', [ClientController::class, 'addAddress']);
            Route::delete('addresses/{addressId}', [ClientController::class, 'deleteAddress']);
            Route::get('orders', [ClientController::class, 'orderHistory']);
            Route::get('orders/{orderId}/repeatable-lines', [ClientController::class, 'repeatableLines'])
                ->whereNumber('orderId');

            Route::get('favorites', [ClientController::class, 'favorites']);
            Route::post('favorites/merge', [ClientController::class, 'mergeFavorites']);
            Route::post('favorites/{productId}', [ClientController::class, 'toggleFavorite'])
                ->whereNumber('productId');
            Route::delete('favorites/{productId}', [ClientController::class, 'removeFavorite'])
                ->whereNumber('productId');
        });
    });

    Route::prefix('order')->group(function (): void {
        Route::post('quote', [OrderController::class, 'quote']);
        Route::post('/', [OrderController::class, 'place']);
    });
});
