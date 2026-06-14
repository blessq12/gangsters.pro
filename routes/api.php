<?php

use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\MarketingContentController;
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

Route::get('/catalog', [CatalogController::class, 'show']);
Route::prefix('marketing')->group(function (): void {
    Route::get('/', [MarketingContentController::class, 'show']);
    Route::get('/banners', [MarketingContentController::class, 'banners']);
    Route::get('/promotions', [MarketingContentController::class, 'promotions']);
});

Route::get('/delivery', [DeliveryController::class, 'show']);

Route::prefix('checkout')->group(function (): void {
    Route::post('/', [CheckoutController::class, 'store']);
    Route::patch('{checkoutId}/cart', [CheckoutController::class, 'updateCart']);
    Route::patch('{checkoutId}/client', [CheckoutController::class, 'setClient']);
    Route::patch('{checkoutId}/delivery', [CheckoutController::class, 'setDelivery']);
    Route::patch('{checkoutId}/payment', [CheckoutController::class, 'setPayment']);
    Route::post('{checkoutId}/confirm', [CheckoutController::class, 'confirm']);
});

Route::prefix('company')->group(function (): void {
    Route::get('/main', [CompanyController::class, 'main']);
    Route::get('/legals', [CompanyController::class, 'legals']);
    Route::get('/documents', [CompanyController::class, 'documents']);
});

Route::prefix('client')->group(function (): void {
    Route::post('register', [ClientController::class, 'register']);
    Route::post('login', [ClientController::class, 'login']);
    Route::post('forgot-password', [ClientController::class, 'forgotPassword']);
    Route::post('change-password', [ClientController::class, 'changePassword']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('profile', [ClientController::class, 'profile']);
        Route::patch('profile', [ClientController::class, 'updateProfile']);
        Route::post('addresses', [ClientController::class, 'addAddress']);
        Route::delete('addresses/{addressId}', [ClientController::class, 'deleteAddress']);
    });
});
