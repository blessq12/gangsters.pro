<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('home');
    $router->resource('companies', CompanyController::class);
    $router->resource('product-categories', ProductCategoryController::class);
    $router->resource('products', ProductController::class);
    $router->resource('users', ClientController::class);
    $router->resource('orders', OrderController::class);
    $router->resource('stories', StoryController::class);
    $router->resource('banners', BannerController::class);
});
