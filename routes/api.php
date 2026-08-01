<?php

use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\OrderDraftController;
use App\Http\Controllers\Api\YandexFoodController;
use Illuminate\Support\Facades\Route;

Route::get('/content', [ContentController::class, 'show']);

Route::get('/catalog', [CatalogController::class, 'show']);

Route::prefix('yandex-food')->group(function (): void {
    Route::post('/security/oauth/token', [YandexFoodController::class, 'login']);

    Route::middleware('yandex.food.auth')->group(function (): void {
        Route::get('/menu/{id}/composition', [YandexFoodController::class, 'getMenuComposition']);
        Route::get('/menu/{id}/availability', [YandexFoodController::class, 'getMenuAvailability']);
        Route::get('/menu/{id}/promos', [YandexFoodController::class, 'getMenuPromos']);
        Route::get('/restaurants', [YandexFoodController::class, 'getRestaurants']);
    });
});
