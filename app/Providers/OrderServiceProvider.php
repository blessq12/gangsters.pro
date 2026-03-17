<?php

namespace App\Providers;

use App\Domain\Order\Repositories\OrderRepositoryInterface as OrderRepositoryContract;
use App\Domain\Order\Services\OrderIdGenerator;
use App\Domain\Order\Integrations\FrontpadOrderGateway;
use App\Infrastructure\Order\Repository\OrderRepository as EloquentOrderRepository;
use App\Infrastructure\Order\Service\RandomOrderIdGenerator;
use App\Infrastructure\Order\Integrations\FrontpadOrderGatewayImpl;
use Illuminate\Support\ServiceProvider;

class OrderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(OrderRepositoryContract::class, EloquentOrderRepository::class);
        $this->app->bind(OrderIdGenerator::class, RandomOrderIdGenerator::class);
        $this->app->bind(FrontpadOrderGateway::class, FrontpadOrderGatewayImpl::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    }
}
