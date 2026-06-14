<?php

use App\Http\Controllers\Api\CatalogController;
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

Route::prefix('company')->group(function (): void {
    Route::get('/main', [CompanyController::class, 'main']);
    Route::get('/legals', [CompanyController::class, 'legals']);
    Route::get('/documents', [CompanyController::class, 'documents']);
});
