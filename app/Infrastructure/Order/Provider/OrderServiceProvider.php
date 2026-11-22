<?php

namespace App\Infrastructure\Order\Provider;

use App\Application\Order\Checkout\CheckoutQuote;
use App\Application\Order\Query\QuoteOrderUseCase;
use App\Domain\Core\Order\Repository\OrderRepository;
use App\Infrastructure\Order\Repository\EloquentOrderRepository;
use Illuminate\Support\ServiceProvider;

final class OrderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrderRepository::class, EloquentOrderRepository::class);
        $this->app->bind(CheckoutQuote::class, QuoteOrderUseCase::class);
    }
}
