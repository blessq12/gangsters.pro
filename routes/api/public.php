<?php

use App\Http\Controllers\Api\PublicApi\AuthController;
use App\Http\Controllers\Api\PublicApi\CatalogController;
use App\Http\Controllers\Api\PublicApi\ClientController;
use App\Http\Controllers\Api\PublicApi\CompanyController;
use App\Http\Controllers\Api\PublicApi\DeliveryController;
use App\Http\Controllers\Api\PublicApi\MarketingController;
use App\Http\Controllers\Api\PublicApi\OrderController;
use App\Http\Controllers\Api\PublicApi\PromotionController;
use Illuminate\Support\Facades\Route;

/*
| Public API: auth stubs + catalog/company/delivery/promotions + client CRM.
*/

Route::prefix('public')->group(function (): void {
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword'])
        ->middleware('throttle:5,1');
    Route::get('auth/me', [AuthController::class, 'me']);

    Route::get('catalog', [CatalogController::class, 'show']);
    Route::get('company', [CompanyController::class, 'show']);
    Route::get('delivery', [DeliveryController::class, 'show']);
    Route::get('promotions', [PromotionController::class, 'show']);
    Route::get('marketing', [MarketingController::class, 'show']);

    Route::post('order/quote', [OrderController::class, 'quote']);
    Route::post('order', [OrderController::class, 'store']);

    Route::post('client/register', [ClientController::class, 'register']);
    Route::post('client/login', [ClientController::class, 'login']);
    Route::post('client/forgot-password', [ClientController::class, 'forgotPassword'])
        ->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::get('ping', static function () {
            return response()->json(['ok' => true]);
        });

        Route::post('client/logout', [ClientController::class, 'logout']);
        Route::get('client/profile', [ClientController::class, 'profile']);
        Route::patch('client/profile', [ClientController::class, 'updateProfile']);
        Route::post('client/addresses', [ClientController::class, 'addAddress']);
        Route::delete('client/addresses/{id}', [ClientController::class, 'deleteAddress']);
        Route::get('client/favorites', [ClientController::class, 'favorites']);
        Route::post('client/favorites/merge', [ClientController::class, 'mergeFavorites']);
        Route::post('client/favorites/{productId}', [ClientController::class, 'toggleFavorite']);
        Route::delete('client/favorites/{productId}', [ClientController::class, 'removeFavorite']);
        Route::get('client/orders', [ClientController::class, 'orders']);
        Route::get('client/orders/{orderId}/repeatable-lines', [ClientController::class, 'repeatableLines']);
    });
});
