<?php

use App\Facades\Frontpad;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Crm\CrmController;
use App\Http\Controllers\Front\MainController;
use App\Models\Order;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegisterMail;
use App\Http\Middleware\TelescopeOverride;
use Illuminate\Support\Facades\Log;
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
    'cors',
])->name('main.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/about', 'about')->name('about');
    Route::get('/vacancy', 'vacancy')->name('vacancy');
    Route::get('/contact', 'contact')->name('contact');
    Route::get('/privacy', 'privacy')->name('privacy');
    Route::get('/loyalty', 'loyalty')->name('loyalty');
    Route::get('/purchase-and-delivery', 'purchaseAndDelivery')->name('purchaseAndDelivery');

    Route::get('/reset-password', 'resetPassword')->name('resetPassword');
});
