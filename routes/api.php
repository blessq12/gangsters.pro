<?php

use App\Http\Controllers\ApiClientAuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SessionIdentifierController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RawController as Raw;
use App\Http\Controllers\TelegramBotController;

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


Route::controller(ApiClientAuthController::class)->prefix('auth')->group(function () {
    Route::post('/login', 'clientLogin');
    Route::post('/register', 'clientRegister');
    Route::post('forgot-password', 'resetPassword');
    Route::post('/change-password', 'changePassword');
    Route::get('/get-user', 'getUser');
    Route::get('/get-user-data', 'getUserData');
    Route::patch('/update-user', 'updateUser');
    Route::post('/add-address', 'addAddress');
    Route::delete('/delete-address/{id}', 'deleteAddress');
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
    Route::get('/available-products', 'getAvailableProducts');
    Route::post('/add-product-to-category', 'addProductToCategory');
    Route::post('/remove-product-from-category', 'removeProductFromCategory');
});

Route::controller(\App\Http\Controllers\Api\SearchController::class)->group(function () {
    Route::get('/goods/search', 'searchGoods');
});


Route::controller(\App\Http\Controllers\Api\RemoteController::class)
    ->prefix('remote')
    ->middleware('remoteAccess')
    ->group(function () {
        Route::get('check-access', 'checkAccess');
        Route::get('schema', 'getSchema');

        Route::get('{table}', 'getRecords');
        Route::get('{table}/{id}', 'getRecord');
        Route::post('{table}', 'createRecord');
        Route::put('{table}/{id}', 'updateRecord');
        Route::delete('{table}/{id}', 'deleteRecord');
    });
