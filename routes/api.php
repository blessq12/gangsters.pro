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
    // авторизованные маршруты
    Route::get('/get-user', 'getUser');
    Route::get('/get-user-data', 'getUserData');
    Route::patch('/update-user', 'updateUser');
});

// Order routes
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


Route::controller(NotificationController::class)->group(function () {
    Route::get('/notifications', 'index');
    Route::patch('/notifications/{notification}/read', 'markAsRead');
    Route::post('/notifications/broadcast', 'createForAllUsers');
});

Route::controller(Raw::class)->group(function () {
    Route::get('/get-routes', 'getLinks');
    Route::get('/get-company', 'getCompany');
    Route::get('/get-shedule', 'getShedule');
});

Route::controller(TelegramBotController::class)->prefix('telegram')->group(function () {
    Route::get('/get-company', 'getCompany');
    // Продуктовка
    Route::get('/get-product-categories', 'getProductCategories');
    Route::get('/get-products', 'getProducts');
});
