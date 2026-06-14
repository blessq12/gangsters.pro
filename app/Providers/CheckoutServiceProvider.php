<?php

namespace App\Providers;

use App\Domain\Checkout\Port\CatalogPricingPort;
use App\Domain\Checkout\Repository\CheckoutRepository;
use App\Infrastructure\Checkout\Port\CatalogPricingAdapter;
use App\Infrastructure\Checkout\Repository\EloquentCheckoutRepository;
use Illuminate\Support\ServiceProvider;

final class CheckoutServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CheckoutRepository::class, EloquentCheckoutRepository::class);
        $this->app->bind(CatalogPricingPort::class, CatalogPricingAdapter::class);
    }
}
