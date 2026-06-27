<?php

use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\MarketingContentController;
use App\Http\Controllers\Api\IngressController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderDraftController;
use App\Http\Controllers\Api\StorefrontController;
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

Route::get('/storefront/bootstrap', [StorefrontController::class, 'bootstrap']);
Route::get('/storefront/bootstrap/critical', [StorefrontController::class, 'bootstrapCritical']);
Route::get('/storefront/bootstrap/deferred', [StorefrontController::class, 'bootstrapDeferred']);

Route::post('/order-drafts/preview', [OrderDraftController::class, 'preview']);
Route::post('/orders', [OrderDraftController::class, 'store']);

Route::get('/catalog', [CatalogController::class, 'show']);
Route::prefix('marketing')->group(function (): void {
    Route::get('/', [MarketingContentController::class, 'show']);
    Route::get('/banners', [MarketingContentController::class, 'banners']);
    Route::get('/promotions', [MarketingContentController::class, 'promotions']);
});

Route::get('/delivery', [DeliveryController::class, 'show']);

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

Route::post('ingress/{partner}/orders', [IngressController::class, 'store']);
