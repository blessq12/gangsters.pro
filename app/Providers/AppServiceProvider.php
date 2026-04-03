<?php

namespace App\Providers;

use App\Application\Client\Ports\ClientPasswordResetMailer;
use App\Application\Security\UnauthorizedClientAccessNotifier;
use App\Infrastructure\Client\Mail\LaravelClientPasswordResetMailer;
use App\Infrastructure\Security\EventUnauthorizedClientAccessNotifier;
use App\Infrastructure\Shared\Events\LaravelDomainEventBus;
use App\Infrastructure\SystemContent\Model\SYS_Company;
use App\Services\Order\Complimentary\PerOrderByCategoryPolicy;
use App\Services\Order\ComplimentaryItemsResolver;
use App\Services\Yandex\YaMetrikaService;
use App\Shared\Events\DomainEventBus;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ClientPasswordResetMailer::class, LaravelClientPasswordResetMailer::class);
        $this->app->bind(DomainEventBus::class, LaravelDomainEventBus::class);
        $this->app->bind(UnauthorizedClientAccessNotifier::class, EventUnauthorizedClientAccessNotifier::class);
        $this->app->singleton(ComplimentaryItemsResolver::class, function ($app) {
            return new ComplimentaryItemsResolver([
                $app->make(PerOrderByCategoryPolicy::class),
            ]);
        });
        $this->app->singleton(YaMetrikaService::class, function ($app) {
            return new YaMetrikaService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        View::composer(['errors::*', 'error.*'], function ($view) {
            $view->with('company', SYS_Company::query()->first());
        });
    }
}
