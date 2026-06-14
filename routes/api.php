<?php

use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\DeliveryController;
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
Route::get('/delivery', [DeliveryController::class, 'show']);
