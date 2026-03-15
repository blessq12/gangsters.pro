<?php

namespace App\Providers;

use App\Domain\Order\Repositories\OrderRepositoryInterface as OrderRepositoryContract;
use App\Infrastructure\Order\Repository\OrderRepository as EloquentOrderRepository;
use Illuminate\Support\ServiceProvider;

class OrderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(OrderRepositoryContract::class, EloquentOrderRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    }
}
