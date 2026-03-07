<?php

use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\YandexFoodController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RawController as Raw;

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

Route::prefix('order')->controller(OrderController::class)->group(function () {
    Route::get('/my-orders', 'getMyOrders');
    Route::get('/my-coins', 'getMyCoins');
    Route::post('/', 'createOrder');
    Route::post('/update', 'updateOrder');
    Route::post('/check-availability', 'checkAvalibility');
});

Route::prefix('product')->controller(ProductController::class)->group(function () {
    Route::get('/categories', 'categories');
    Route::get('/products', 'products');
});

Route::controller(Raw::class)->group(function () {
    Route::get('/get-routes', 'getLinks');
    Route::get('/get-company', 'getCompany');
    Route::get('/get-shedule', 'getShedule');
});

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
