<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Crm\CrmController;
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

Route::controller(MainController::class)->name('main.')->group(function(){
    Route::get('/', 'index')->name('index');
    Route::get('/about', 'about')->name('about');
    Route::get('/contact', 'contact')->name('contact');
});

Route::controller(CrmController::class)->middleware(['auth','can:admin'])->prefix('crm')->name('crm.')->group(function(){
    Route::get('/', 'index')->name('index');
    
});

Route::controller(AuthController::class)->prefix('auth')->name('auth.')->group(function(){
    Route::middleware('guest')->group(function(){
        Route::get('/login', 'loginPage')->name('login-page');
        Route::post('/login', 'userLogin')->name('user-login');
        
        Route::get('/register', 'registerPage')->name('register-page');
        Route::post('/register', 'userRegister')->name('user-register');
    });
    Route::get('/logout', 'userLogout')->name('user-logout')->middleware('auth');
});