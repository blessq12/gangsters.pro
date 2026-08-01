<?php

use App\Http\Controllers\Api\AppBootstrapController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderDraftController;
use App\Http\Controllers\Api\YandexFoodController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
*/

Route::get('/bootstrap', [AppBootstrapController::class, 'bootstrap']);
Route::get('/bootstrap/critical', [AppBootstrapController::class, 'bootstrapCritical']);
Route::get('/bootstrap/deferred', [AppBootstrapController::class, 'bootstrapDeferred']);

Route::get('/content/bootstrap', [ContentController::class, 'bootstrap']);

Route::post('/order-drafts/preview', [OrderDraftController::class, 'preview']);
Route::post('/orders', [OrderDraftController::class, 'store']);

Route::get('/catalog', [CatalogController::class, 'show']);

Route::prefix('client')->group(function (): void {
    Route::post('register', [ClientController::class, 'register']);
    Route::post('login', [ClientController::class, 'login']);
    Route::post('forgot-password', [ClientController::class, 'forgotPassword']);
    Route::post('change-password', [ClientController::class, 'changePassword']);

    Route::middleware('auth.client')->group(function (): void {
        Route::get('profile', [ClientController::class, 'profile']);
        Route::patch('profile', [ClientController::class, 'updateProfile']);
        Route::post('addresses', [ClientController::class, 'addAddress']);
        Route::delete('addresses/{addressId}', [ClientController::class, 'deleteAddress']);
        Route::get('favorites', [ClientController::class, 'favorites']);
        Route::post('favorites/merge', [ClientController::class, 'mergeGuestFavorites']);
        Route::post('favorites/{productId}', [ClientController::class, 'toggleFavorite']);
        Route::delete('favorites/{productId}', [ClientController::class, 'removeFavorite']);
    });
});

Route::middleware('auth.client')->group(function (): void {
    Route::get('order', [OrderController::class, 'index']);
    Route::get('order/{orderId}', [OrderController::class, 'show']);
    Route::get('order/{orderId}/repeatable-lines', [OrderController::class, 'repeatableLines']);
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
