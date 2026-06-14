<?php

namespace App\Providers;

use App\Application\Checkout\Handler\OnCheckoutConfirmed;
use App\Domain\Checkout\Event\CheckoutConfirmed;
use App\Domain\Checkout\Port\CatalogPricingPort;
use App\Domain\Checkout\Repository\CheckoutRepository;
use App\Infrastructure\Checkout\Port\CatalogPricingAdapter;
use App\Infrastructure\Checkout\Repository\EloquentCheckoutRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class CheckoutServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CheckoutRepository::class, EloquentCheckoutRepository::class);
        $this->app->bind(CatalogPricingPort::class, CatalogPricingAdapter::class);
    }

    public function boot(): void
    {
        Event::listen(
            CheckoutConfirmed::class,
            [OnCheckoutConfirmed::class, 'handle'],
        );
    }
}
