<?php

namespace App\Infrastructure\Production\Provider;

use App\Domain\Core\Production\Port\ProductionOrderSnapshotPort;
use App\Domain\Core\Production\Repository\KitchenTicketRepository;
use App\Infrastructure\Production\Adapter\EloquentProductionOrderSnapshotPort;
use App\Infrastructure\Production\Repository\EloquentKitchenTicketRepository;
use Illuminate\Support\ServiceProvider;

final class ProductionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(KitchenTicketRepository::class, EloquentKitchenTicketRepository::class);
        $this->app->bind(ProductionOrderSnapshotPort::class, EloquentProductionOrderSnapshotPort::class);
    }
}
