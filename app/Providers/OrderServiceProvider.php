<?php

namespace App\Providers;

use App\Application\Order\Handler\OnCheckoutConfirmed;
use App\Domain\Checkout\Event\CheckoutConfirmed;
use App\Domain\Order\Repository\OrderRepository;
use App\Infrastructure\Order\Repository\EloquentOrderRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class OrderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrderRepository::class, EloquentOrderRepository::class);
    }

    public function boot(): void
    {
        Event::listen(
            CheckoutConfirmed::class,
            [OnCheckoutConfirmed::class, 'handle'],
        );
    }
}
