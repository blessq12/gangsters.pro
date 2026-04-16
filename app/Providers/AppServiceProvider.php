<?php

namespace App\Providers;

use App\Application\Order\Contracts\CancelOrderContract;
use App\Application\Order\Contracts\CustomerSnapshotProvider;
use App\Application\Order\Contracts\MarkOrderPaidContract;
use App\Application\Order\Contracts\OrderApplicationFacadeContract;
use App\Application\Order\Contracts\OrderPlacementContract;
use App\Application\Order\Contracts\UpdateOrderContract;
use App\Application\Order\OrderApplicationFacade;
use App\Application\YandexFood\Contracts\YandexFoodOrderMetaStore;
use App\Application\Notifications\Ports\ClientOutboundNotifier;
use App\Application\Security\UnauthorizedClientAccessNotifier;
use App\Domain\Order\Contracts\CatalogItemSnapshotProvider as DomainCatalogItemSnapshotProvider;
use App\Application\Order\Command\CancelOrderService;
use App\Application\Catalog\Contracts\CatalogYandexReadModelContract;
use App\Application\Catalog\Query\CatalogYandexReadModel;
use App\Infrastructure\Notifications\Client\LaravelMailClientOutboundNotifier;
use App\Infrastructure\Order\Catalog\EloquentCatalogItemSnapshotProvider;
use App\Infrastructure\Order\CustomerSnapshot\EloquentCustomerSnapshotProvider;
use App\Infrastructure\Security\EventUnauthorizedClientAccessNotifier;
use App\Infrastructure\Shared\Events\LaravelDomainEventBus;
use App\Infrastructure\Shared\Events\LaravelIntegrationEventBus;
use App\Infrastructure\YandexFood\OrderMeta\EloquentYandexFoodOrderMetaStore;
use App\Application\Order\Command\PlaceOrderService;
use App\Application\Order\Command\UpdateOrderService;
use App\Application\Order\Command\MarkOrderPaidService;
use App\Application\SystemContent\Query\GetSystemCompanyUseCase;
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
        $this->app->bind(DomainCatalogItemSnapshotProvider::class, EloquentCatalogItemSnapshotProvider::class);
        $this->app->bind(OrderPlacementContract::class, PlaceOrderService::class);
        $this->app->bind(UpdateOrderContract::class, UpdateOrderService::class);
        $this->app->bind(CancelOrderContract::class, CancelOrderService::class);
        $this->app->bind(MarkOrderPaidContract::class, MarkOrderPaidService::class);
        $this->app->bind(OrderApplicationFacadeContract::class, OrderApplicationFacade::class);
        $this->app->bind(CatalogYandexReadModelContract::class, CatalogYandexReadModel::class);
        $this->app->bind(YandexFoodOrderMetaStore::class, EloquentYandexFoodOrderMetaStore::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        View::composer(['errors::*', 'error.*'], function ($view) {
            $companyData = app(GetSystemCompanyUseCase::class)->execute()['data'] ?? null;
            $view->with('company', $companyData !== null ? (object) $companyData : null);
        });
    }
}
