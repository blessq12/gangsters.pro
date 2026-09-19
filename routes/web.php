<?php

use App\Http\Controllers\Front\MainController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::controller(MainController::class)->middleware([
    // 'cors',
])->name('main.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/about', 'about')->name('about');
    Route::get('/contact', 'contact')->name('contact');
    Route::get('/privacy', 'privacy')->name('privacy');
    Route::get('/offer', 'offer')->name('offer');
    Route::get('/terms', 'terms')->name('terms');
    Route::get('/pdn-consent', 'pdnConsent')->name('pdnConsent');
    Route::get('/cookies', 'cookies')->name('cookies');
    Route::get('/seller', 'seller')->name('seller');
    Route::get('/purchase-and-delivery', 'purchaseAndDelivery')->name('purchaseAndDelivery');
    Route::get('/reset-password', 'resetPassword')->name('resetPassword');
    Route::get('/attach-categories-to-products', 'attachCategoriesToProducts');
});
