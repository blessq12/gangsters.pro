<?php

namespace App\Providers;

use App\Application\Notifications\Ports\ClientOutboundNotifier;
use App\Application\Security\UnauthorizedClientAccessNotifier;
use App\Infrastructure\Notifications\Client\LaravelMailClientOutboundNotifier;
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
        // Пока только почта. TG/SMS — заглушки без реальной доставки; композит закомментирован.
        // Раскомментируй при готовых адаптерах:
        // use App\Infrastructure\Notifications\Client\CompositeClientOutboundNotifier;
        // use App\Infrastructure\Notifications\Client\StubSmsClientOutboundNotifier;
        // use App\Infrastructure\Notifications\Client\StubTelegramClientOutboundNotifier;
        // $this->app->singleton(LaravelMailClientOutboundNotifier::class);
        // $this->app->singleton(StubTelegramClientOutboundNotifier::class);
        // $this->app->singleton(StubSmsClientOutboundNotifier::class);
        // $this->app->bind(ClientOutboundNotifier::class, function ($app) {
        //     return new CompositeClientOutboundNotifier(
        //         $app->make(LaravelMailClientOutboundNotifier::class),
        //         $app->make(StubTelegramClientOutboundNotifier::class),
        //         $app->make(StubSmsClientOutboundNotifier::class),
        //     );
        // });
        $this->app->bind(ClientOutboundNotifier::class, LaravelMailClientOutboundNotifier::class);
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
