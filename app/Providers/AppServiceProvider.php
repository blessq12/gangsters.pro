<?php

namespace App\Providers;

use App\Models\Company;
use App\Services\NotificationService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Services\Yandex\YaMetrikaService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('notification.service', function ($app) {
            return new NotificationService();
        });

        $this->app->singleton(YaMetrikaService::class, function ($app) {
            return new YaMetrikaService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // $this->loadViewsFrom('','');
        Paginator::useBootstrapFive();
        View::composer(['errors::*', 'error.*'], function ($view) {
            $view->with('company', Company::first());
        });
    }
}
