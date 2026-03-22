<?php

use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\YandexFoodController;
use App\Http\Controllers\Api\YandexFoodTempController;
use Illuminate\Support\Facades\Route;
// Импорты ниже относятся только к другим доменам (order, product, system, интеграции).

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Основные слои: client, order, product.
|
*/

Route::prefix('client')->controller(ClientController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::post('/forgot-password', 'forgotPassword');
    Route::post('/change-password', 'changePassword');
    Route::get('/profile', 'profile');
    Route::patch('/profile', 'updateProfile');
    Route::post('/addresses', 'addAddress');
    Route::delete('/addresses/{id}', 'deleteAddress');
});

Route::middleware('auth:sanctum')
    ->prefix('order')
    ->controller(OrderController::class)
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
    });

Route::get('/catalog', [CatalogController::class, 'tree']);

Route::controller(YandexFoodController::class)
    ->prefix('yandex-food')
    ->group(function () {
        Route::post('/security/oauth/token', 'login');
        Route::get('/menu/{id}/composition', 'getMenuComposition');
        Route::get('/menu/{id}/availability', 'getMenuAvailability');
        Route::get('/menu/{id}/promos', 'getMenuPromos');
        Route::post('/order', 'createOrder');
        Route::get('/order/{id}', 'getOrderById');
        Route::get('/order/{id}/status', 'getOrderStatus');
        Route::put('/order/{id}/', 'updateOrder');
        Route::delete('/order/{id}/', 'deleteOrder');
        Route::get('/restaurants', 'getRestaurants');
    });

Route::controller(YandexFoodTempController::class)
    ->prefix('yandex-food-temp')
    ->group(function () {
        Route::post('/security/oauth/token', 'login');
        Route::get('/menu/{id}/composition', 'getMenuComposition');
        Route::get('/menu/{id}/availability', 'getMenuAvailability');
        Route::get('/menu/{id}/promos', 'getMenuPromos');
        Route::post('/order', 'createOrder');
        Route::get('/order/{id}', 'getOrderById');
        Route::get('/order/{id}/status', 'getOrderStatus');
        Route::put('/order/{id}/', 'updateOrder');
        Route::delete('/order/{id}/', 'deleteOrder');
        Route::get('/restaurants', 'getRestaurants');
    });


// Системные данные (баннеры, акции и т.п.)
Route::prefix('system')->group(function () {
    Route::get('/banners', [BannerController::class, 'index']);
    Route::get('/promotions', [PromotionController::class, 'index']);
});
