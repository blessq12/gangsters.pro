<?php

use App\Http\Controllers\ApiClientAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SessionIdentifierController;
use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;
use App\Http\Controllers\Api\RawController as Raw;
use App\Http\Controllers\TelegramBotController;
use App\Models\SessionIdentifier;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Auth routes
Route::controller(ApiClientAuthController::class)->prefix('auth')->group(function () {

    Route::post('/login', 'clientLogin');
    Route::post('/register', 'clientRegister');
    Route::post('forgot-password', 'resetPassword');
    Route::post('/change-password', 'changePassword');
    Route::get('/get-user', 'getUser');
    Route::get('/get-user-data', 'getUserData');
    Route::patch('/update-user', 'updateUser');
});

Route::controller(OrderController::class)->prefix('orders')->group(function () {
    Route::get('/get-my-orders', 'getMyOrders');
    Route::get('/get-my-coins', 'getMyCoins');
    Route::post('create', 'createOrder');
    Route::post('update', 'updateOrder');
    Route::post('check-avalibility', 'checkAvalibility');
});


Route::controller(SessionIdentifierController::class)->group(function () {
    Route::post('/session-identifiers', 'create');
    Route::post('/session-identifiers/public', 'createPublicSession');
    Route::post('/session-identifiers/assign-user', 'assignUserId');
    Route::post('/session-identifiers/update-user-id', 'updateUserId')->middleware('auth:sanctum');
});

Route::controller(Raw::class)->group(function () {
    Route::get('/get-routes', 'getLinks');
    Route::get('/get-company', 'getCompany');
    Route::get('/get-shedule', 'getShedule');
});

Route::controller(TelegramBotController::class)->prefix('telegram')->group(function () {
    Route::get('/get-company', 'getCompany');
    Route::get('/get-product-categories', 'getProductCategories');
    Route::get('/get-products', 'getProducts');
});

Route::controller(\App\Http\Controllers\Api\YandexFoodController::class)
    ->prefix('yandex-food')
    ->group(function () {
        // Auth
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


Route::controller(\App\Http\Controllers\Api\GoodsSort::class)->group(function () {
    Route::post('/update-category-order', 'updateCategoryOrder');
    Route::post('/update-product-order', 'updateProductOrder');
});
