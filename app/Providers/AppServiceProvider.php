<?php

namespace App\Providers;

use App\Application\Order\Contracts\CatalogItemSnapshotProvider;
use App\Application\Order\Contracts\CancelOrderContract;
use App\Application\Order\Contracts\CustomerSnapshotProvider;
use App\Application\Order\Contracts\OrderPlacementContract;
use App\Application\Order\Contracts\UpdateOrderContract;
use App\Application\YandexFood\Contracts\YandexFoodOrderMetaStore;
use App\Application\Notifications\Ports\ClientOutboundNotifier;
use App\Application\Security\UnauthorizedClientAccessNotifier;
use App\Domain\Order\Contracts\CatalogItemSnapshotProvider as DomainCatalogItemSnapshotProvider;
use App\Application\Order\Command\CancelOrderService;
use App\Infrastructure\Notifications\Client\LaravelMailClientOutboundNotifier;
use App\Infrastructure\Order\Catalog\EloquentCatalogItemSnapshotProvider;
use App\Infrastructure\Order\CustomerSnapshot\EloquentCustomerSnapshotProvider;
use App\Infrastructure\Security\EventUnauthorizedClientAccessNotifier;
use App\Infrastructure\Shared\Events\LaravelDomainEventBus;
use App\Infrastructure\Shared\Events\LaravelIntegrationEventBus;
use App\Infrastructure\YandexFood\OrderMeta\EloquentYandexFoodOrderMetaStore;
use App\Application\Order\Command\PlaceOrderService;
use App\Application\Order\Command\UpdateOrderService;
use App\Infrastructure\SystemContent\Model\SYS_Company;
use App\Services\Yandex\YaMetrikaService;
use App\Shared\Events\DomainEventBus;
use App\Shared\Events\IntegrationEventBus;
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
        $this->app->bind(IntegrationEventBus::class, LaravelIntegrationEventBus::class);
        $this->app->bind(UnauthorizedClientAccessNotifier::class, EventUnauthorizedClientAccessNotifier::class);
        $this->app->bind(CustomerSnapshotProvider::class, EloquentCustomerSnapshotProvider::class);
        $this->app->bind(CatalogItemSnapshotProvider::class, EloquentCatalogItemSnapshotProvider::class);
        $this->app->bind(DomainCatalogItemSnapshotProvider::class, EloquentCatalogItemSnapshotProvider::class);
        $this->app->bind(OrderPlacementContract::class, PlaceOrderService::class);
        $this->app->bind(UpdateOrderContract::class, UpdateOrderService::class);
        $this->app->bind(CancelOrderContract::class, CancelOrderService::class);
        $this->app->bind(YandexFoodOrderMetaStore::class, EloquentYandexFoodOrderMetaStore::class);
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
