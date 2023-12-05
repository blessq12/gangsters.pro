<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Crm\BannerController;
use App\Http\Controllers\Crm\CompanyController;
use App\Http\Controllers\Crm\CrmController;
use App\Http\Controllers\Crm\ProductController;
use App\Http\Controllers\Crm\StoryController;
use App\Http\Controllers\Crm\VacancyController;
use App\Http\Controllers\Front\MainController;
use App\Http\Controllers\Crm\OrderController;
use App\Http\Controllers\Crm\UserController;
use App\Models\Company;
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

    Route::resource('stories', StoryController::class);
    Route::resource('banners', BannerController::class);
    Route::resource('companies', CompanyController::class);
    Route::resource('users', UserController::class);
    Route::resource('vacancies', VacancyController::class);
    Route::resource('orders', OrderController::class);
    Route::resource('products', ProductController::class);
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