<?php

use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ShoppingController;
use App\Http\Controllers\Api\SystemContentController;
use App\Http\Controllers\Api\YandexFoodController;
use Illuminate\Support\Facades\Route;

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
    Route::post('/forgot-password', 'forgotPassword')->middleware('throttle:5,1');
    Route::post('/change-password', 'changePassword');
    Route::get('/profile', 'profile');
    Route::patch('/profile', 'updateProfile');
    Route::post('/addresses', 'addAddress');
    Route::delete('/addresses/{id}', 'deleteAddress');
});

Route::middleware(['attempt.sanctum', 'shopping.session', 'throttle:guest-order'])
    ->post('/order', [OrderController::class, 'store']);

Route::middleware(['attempt.sanctum', 'shopping.session'])->prefix('shopping')->group(function () {
    Route::get('/state', [ShoppingController::class, 'state']);
    Route::post('/cart/items', [ShoppingController::class, 'upsertCartLine']);
    Route::delete('/cart/items/{productId}', [ShoppingController::class, 'removeCartLine']);
    Route::delete('/cart', [ShoppingController::class, 'clearCart']);
    Route::post('/cart/recalculate', [ShoppingController::class, 'recalculate']);
    Route::post('/favorites/{productId}', [ShoppingController::class, 'toggleFavorite']);
    Route::delete('/favorites/{productId}', [ShoppingController::class, 'removeFavorite']);
    Route::patch('/checkout-draft', [ShoppingController::class, 'patchCheckoutDraft']);
    Route::post('/migrate', [ShoppingController::class, 'migrate']);
    Route::post('/logout', [ShoppingController::class, 'logout']);
});

Route::middleware(['attempt.sanctum', 'shopping.session', 'auth:sanctum'])
    ->post('/shopping/merge', [ShoppingController::class, 'merge']);

Route::middleware('auth:sanctum')
    ->get('/order', [OrderController::class, 'index']);

Route::middleware('internal.token')
    ->post('/internal/orders/{id}/pay', [OrderController::class, 'markPaid']);

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

// Системные данные (баннеры, акции и т.п.)
Route::controller(SystemContentController::class)
    ->prefix('system')
    ->group(function () {
        Route::get('/banners', 'banners');
        Route::get('/promotions', 'promotions');
        Route::get('/company', 'company');
        Route::get('/company-legal', 'companyLegal');
        Route::get('/documents', 'documents');
    });
