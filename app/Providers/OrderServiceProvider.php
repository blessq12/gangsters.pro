<?php

namespace App\Providers;

use App\Domain\Order\Port\CatalogComplementSetCandidatesPort;
use App\Domain\Order\Port\CatalogGiftCandidatesPort;
use App\Domain\Order\Port\CatalogPricingPort;
use App\Domain\Order\Port\CatalogRollMetaPort;
use App\Domain\Order\Port\CatalogSetCompositionPort;
use App\Domain\Order\Port\ClientProfilePort;
use App\Domain\Order\Repository\OrderRepository;
use App\Infrastructure\Order\Port\CatalogComplementSetCandidatesAdapter;
use App\Infrastructure\Order\Port\CatalogGiftCandidatesAdapter;
use App\Infrastructure\Order\Port\CatalogPricingAdapter;
use App\Infrastructure\Order\Port\CatalogRollMetaAdapter;
use App\Infrastructure\Order\Port\CatalogSetCompositionAdapter;
use App\Infrastructure\Order\Port\ClientProfileAdapter;
use App\Infrastructure\Order\Repository\EloquentOrderRepository;
use Illuminate\Support\ServiceProvider;

final class OrderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrderRepository::class, EloquentOrderRepository::class);
        $this->app->bind(CatalogPricingPort::class, CatalogPricingAdapter::class);
        $this->app->bind(CatalogGiftCandidatesPort::class, CatalogGiftCandidatesAdapter::class);
        $this->app->bind(CatalogComplementSetCandidatesPort::class, CatalogComplementSetCandidatesAdapter::class);
        $this->app->bind(CatalogRollMetaPort::class, CatalogRollMetaAdapter::class);
        $this->app->bind(CatalogSetCompositionPort::class, CatalogSetCompositionAdapter::class);
        $this->app->bind(ClientProfilePort::class, ClientProfileAdapter::class);
    }
}
