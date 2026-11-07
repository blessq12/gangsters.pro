<?php

use App\Http\Controllers\Api\BackofficeCatalogController;
use App\Http\Controllers\Api\BackofficeClientController;
use App\Http\Controllers\Api\BackofficeCompanyController;
use App\Http\Controllers\Api\BackofficeDeliverySettingsController;
use App\Http\Controllers\Api\BackofficeMarketingController;
use App\Http\Controllers\Api\BackofficeOperatorController;
use App\Http\Controllers\Api\BackofficeOrderController;
use App\Http\Controllers\Api\BackofficePromotionPolicyController;
use App\Http\Controllers\Api\BackofficeRouteSheetController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Middleware\EnsureBackofficeEnabled;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\FrontpadWebhookController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\YandexFoodController;
use Illuminate\Support\Facades\Route;

Route::prefix('backoffice')->middleware(EnsureBackofficeEnabled::class)->group(function (): void {
    Route::get('orders', [BackofficeOrderController::class, 'index']);
    Route::post('orders', [BackofficeOrderController::class, 'store']);
    Route::post('orders/{order}/status', [BackofficeOrderController::class, 'updateStatus'])
        ->whereNumber('order');
    Route::get('route-sheets', [BackofficeRouteSheetController::class, 'index']);
    Route::post('route-sheets', [BackofficeRouteSheetController::class, 'store']);
    Route::post('route-sheets/{sheet}/take', [BackofficeRouteSheetController::class, 'take'])
        ->whereNumber('sheet');
    Route::post('route-sheets/{sheet}/stops/{order}', [BackofficeRouteSheetController::class, 'settle'])
        ->whereNumber('sheet')
        ->whereNumber('order');
    Route::post('route-sheets/{sheet}/complete', [BackofficeRouteSheetController::class, 'complete'])
        ->whereNumber('sheet');
    Route::get('clients', [BackofficeClientController::class, 'index']);
    Route::get('catalog', [BackofficeCatalogController::class, 'index']);
    Route::post('catalog/categories', [BackofficeCatalogController::class, 'storeCategory']);
    Route::put('catalog/categories/{category}', [BackofficeCatalogController::class, 'updateCategory'])
        ->whereNumber('category');
    Route::post('catalog/tags', [BackofficeCatalogController::class, 'storeTag']);
    Route::put('catalog/tags/{tag}', [BackofficeCatalogController::class, 'updateTag'])
        ->whereNumber('tag');
    Route::post('catalog/products', [BackofficeCatalogController::class, 'storeProduct']);
    Route::put('catalog/products/{product}', [BackofficeCatalogController::class, 'updateProduct'])
        ->whereNumber('product');
    Route::get('catalog/products/{product}/images', [BackofficeCatalogController::class, 'productImages'])
        ->whereNumber('product');
    Route::post('catalog/products/{product}/images', [BackofficeCatalogController::class, 'storeProductImage'])
        ->whereNumber('product');
    Route::post('catalog/sets', [BackofficeCatalogController::class, 'storeSet']);
    Route::put('catalog/sets/{set}', [BackofficeCatalogController::class, 'updateSet'])
        ->whereNumber('set');
    Route::get('company', [BackofficeCompanyController::class, 'show']);
    Route::put('company', [BackofficeCompanyController::class, 'update']);
    Route::get('company/documents', [BackofficeCompanyController::class, 'documents']);
    Route::post('company/documents', [BackofficeCompanyController::class, 'storeDocument']);
    Route::put('company/documents/{document}', [BackofficeCompanyController::class, 'updateDocument'])
        ->whereNumber('document');
    Route::get('operators', [BackofficeOperatorController::class, 'index']);
    Route::post('operators', [BackofficeOperatorController::class, 'store']);
    Route::put('operators/{operator}', [BackofficeOperatorController::class, 'update'])
        ->whereNumber('operator');
    Route::delete('operators/{operator}', [BackofficeOperatorController::class, 'destroy'])
        ->whereNumber('operator');
    Route::get('delivery-settings', [BackofficeDeliverySettingsController::class, 'show']);
    Route::put('delivery-settings', [BackofficeDeliverySettingsController::class, 'update']);
    Route::get('promotion-policy', [BackofficePromotionPolicyController::class, 'show']);
    Route::put('promotion-policy', [BackofficePromotionPolicyController::class, 'update']);
    Route::get('marketing', [BackofficeMarketingController::class, 'index']);
    Route::post('marketing/banners', [BackofficeMarketingController::class, 'storeBanner']);
    Route::post('marketing/banners/{banner}', [BackofficeMarketingController::class, 'updateBanner'])
        ->whereNumber('banner');
    Route::post('marketing/promotions', [BackofficeMarketingController::class, 'storePromotion']);
    Route::post('marketing/promotions/{promotion}', [BackofficeMarketingController::class, 'updatePromotion'])
        ->whereNumber('promotion');
});

Route::get('/content', [ContentController::class, 'show']);
Route::get('/catalog', [CatalogController::class, 'show']);

Route::prefix('client')->group(function (): void {
    Route::post('register', [ClientController::class, 'register']);
    Route::post('login', [ClientController::class, 'login']);
    Route::post('forgot-password', [ClientController::class, 'forgotPassword'])
        ->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('profile', [ClientController::class, 'profile']);
        Route::patch('profile', [ClientController::class, 'updateProfile']);
        Route::post('addresses', [ClientController::class, 'addAddress']);
        Route::delete('addresses/{addressId}', [ClientController::class, 'deleteAddress']);
        Route::get('orders', [ClientController::class, 'orderHistory']);
        Route::get('orders/{orderId}/repeatable-lines', [ClientController::class, 'repeatableLines'])
            ->whereNumber('orderId');

        Route::get('favorites', [ClientController::class, 'favorites']);
        Route::post('favorites/merge', [ClientController::class, 'mergeFavorites']);
        Route::post('favorites/{productId}', [ClientController::class, 'toggleFavorite'])
            ->whereNumber('productId');
        Route::delete('favorites/{productId}', [ClientController::class, 'removeFavorite'])
            ->whereNumber('productId');
    });
});

Route::prefix('order')->group(function (): void {
    Route::post('quote', [OrderController::class, 'quote']);
    Route::post('/', [OrderController::class, 'place']);
    // Frontpad hook_url: панель шлёт probe любым методом и ждёт HTTP 200
    Route::any('update', [FrontpadWebhookController::class, 'handle']);
});

// Alias: старый path из export (orders plural)
Route::any('/orders/update', [FrontpadWebhookController::class, 'handle']);

Route::prefix('yandex-food')->group(function (): void {
    Route::post('/security/oauth/token', [YandexFoodController::class, 'login']);

    Route::middleware('yandex.food.auth')->group(function (): void {
        Route::get('/menu/{id}/composition', [YandexFoodController::class, 'getMenuComposition']);
        Route::get('/menu/{id}/availability', [YandexFoodController::class, 'getMenuAvailability']);
        Route::get('/menu/{id}/promos', [YandexFoodController::class, 'getMenuPromos']);
        Route::get('/restaurants', [YandexFoodController::class, 'getRestaurants']);
    });
});
