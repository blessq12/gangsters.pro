<?php

namespace App\Infrastructure\Order\Provider;

use App\Application\Order\Handler\OnOrderCreated;
use App\Domain\Order\Event\OrderCreated;
use App\Domain\Order\Port\FrontpadOrderExporter;
use App\Domain\Order\Repository\OrderRepository;
use App\Infrastructure\Order\Frontpad\HttpFrontpadOrderExporter;
use App\Infrastructure\Order\Repository\EloquentOrderRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class OrderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrderRepository::class, EloquentOrderRepository::class);
        $this->app->bind(FrontpadOrderExporter::class, HttpFrontpadOrderExporter::class);
    }

    public function boot(): void
    {
        Event::listen(
            OrderCreated::class,
            [OnOrderCreated::class, 'handle'],
        );
    }
}
