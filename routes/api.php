<?php

use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\YandexFoodController;
use Illuminate\Support\Facades\Route;

Route::get('/content', [ContentController::class, 'show']);

Route::get('/catalog', [CatalogController::class, 'show']);

Route::prefix('client')->group(function (): void {
    Route::post('register', [ClientController::class, 'register']);
    Route::post('login', [ClientController::class, 'login']);

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

Route::prefix('yandex-food')->group(function (): void {
    Route::post('/security/oauth/token', [YandexFoodController::class, 'login']);

    Route::middleware('yandex.food.auth')->group(function (): void {
        Route::get('/menu/{id}/composition', [YandexFoodController::class, 'getMenuComposition']);
        Route::get('/menu/{id}/availability', [YandexFoodController::class, 'getMenuAvailability']);
        Route::get('/menu/{id}/promos', [YandexFoodController::class, 'getMenuPromos']);
        Route::get('/restaurants', [YandexFoodController::class, 'getRestaurants']);
    });
});
