<?php

namespace App\Providers;

use App\Models\Company;
use App\Shared\Events\DomainEventBus;
use App\Infrastructure\Shared\Events\LaravelDomainEventBus;
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
        $this->app->bind(DomainEventBus::class, LaravelDomainEventBus::class);
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
