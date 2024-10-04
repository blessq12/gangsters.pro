<?php

use App\Http\Controllers\ApiClientAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;
use App\Http\Controllers\Api\RawController as Raw;

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
    // auth
    Route::post('/login', 'clientLogin');
    Route::post('/register', 'clientRegister');
    // forgot password
    Route::post('forgot-password', 'resetPassword');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('get-user', function () {
            return response()->json([
                'user' => auth('sanctum')->user(),
            ]);
        });
        Route::get('/get-user-data', function () {
            $user = auth('sanctum')->user();
            $user->dob = Carbon::parse($user->dob)->format('d-m-Y');
            return response()->json([
                'user' => $user,
            ]);
        });

        Route::patch('/update-user', 'updateUser');
    });
});

Route::controller(OrderController::class)->prefix('orders')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/get-my-orders', function () {
            $orders = auth('sanctum')->user()->orders;
            $orders->each(function ($order) {
                $order->created = Carbon::parse($order->created_at)->format('d.m.Y H:i');
                $order->status_text = \App\Models\Order::GET_STATUS[$order->status];
            });
            return response()->json($orders);
        });
        Route::get('/get-my-coins', function () {
            return response()->json(auth('sanctum')->user()->coins);
        });
    });
    Route::post('create', 'createOrder');
    Route::post('update', 'updateOrder');
});

Route::controller(Raw::class)->group(function () {
    Route::get('/get-routes', 'getLinks');
    Route::get('/get-company', 'getCompany');
    Route::get('/get-shedule', 'getShedule');
});
