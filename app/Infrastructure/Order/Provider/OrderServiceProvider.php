<?php

namespace App\Infrastructure\Order\Provider;

use App\Domain\Order\Port\OrderCatalogPort;
use App\Domain\Order\Port\OrderClientLookupPort;
use App\Domain\Order\Port\OrderCompanyPort;
use App\Domain\Order\Port\OrderDeliveryPort;
use App\Domain\Order\Port\OrderPromotionPort;
use App\Domain\Order\Repository\OrderRepository;
use App\Infrastructure\Order\Port\OrderCatalogAdapter;
use App\Infrastructure\Order\Port\OrderClientLookupAdapter;
use App\Infrastructure\Order\Port\OrderCompanyAdapter;
use App\Infrastructure\Order\Port\OrderDeliveryAdapter;
use App\Infrastructure\Order\Port\OrderPromotionAdapter;
use App\Infrastructure\Order\Repository\EloquentOrderRepository;
use Illuminate\Support\ServiceProvider;

final class OrderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrderRepository::class, EloquentOrderRepository::class);
        $this->app->bind(OrderCatalogPort::class, OrderCatalogAdapter::class);
        $this->app->bind(OrderClientLookupPort::class, OrderClientLookupAdapter::class);
        $this->app->bind(OrderPromotionPort::class, OrderPromotionAdapter::class);
        $this->app->bind(OrderDeliveryPort::class, OrderDeliveryAdapter::class);
        $this->app->bind(OrderCompanyPort::class, OrderCompanyAdapter::class);
    }
}
