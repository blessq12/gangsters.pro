<?php

namespace App\Infrastructure\Delivery\Provider;

use App\Domain\Core\Delivery\Routing\Port\DeliveryOrderStopSnapshotPort;
use App\Domain\Core\Delivery\Routing\Port\DeliveryRouteOrderEligibilityPort;
use App\Domain\Core\Delivery\Tariff\Repository\DeliveryConfigurationRepository;
use App\Domain\Core\Delivery\Routing\Repository\RouteSheetRepository;
use App\Infrastructure\Delivery\Adapter\EloquentDeliveryOrderStopSnapshotPort;
use App\Infrastructure\Delivery\Adapter\EloquentDeliveryRouteOrderEligibilityPort;
use App\Infrastructure\Delivery\Repository\EloquentDeliveryConfigurationRepository;
use App\Infrastructure\Delivery\Repository\EloquentRouteSheetRepository;
use Illuminate\Support\ServiceProvider;

final class DeliveryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RouteSheetRepository::class, EloquentRouteSheetRepository::class);
        $this->app->bind(DeliveryConfigurationRepository::class, EloquentDeliveryConfigurationRepository::class);
        $this->app->bind(DeliveryOrderStopSnapshotPort::class, EloquentDeliveryOrderStopSnapshotPort::class);
        $this->app->bind(DeliveryRouteOrderEligibilityPort::class, EloquentDeliveryRouteOrderEligibilityPort::class);
    }
}
