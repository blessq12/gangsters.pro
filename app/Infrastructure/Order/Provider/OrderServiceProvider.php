<?php

namespace App\Infrastructure\Order\Provider;

use App\Domain\Order\Repository\OrderRepository;
use App\Infrastructure\Order\Repository\EloquentOrderRepository;
use Illuminate\Support\ServiceProvider;

final class OrderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrderRepository::class, EloquentOrderRepository::class);
    }
}
