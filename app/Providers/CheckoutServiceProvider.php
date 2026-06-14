<?php

namespace App\Providers;

use App\Domain\Checkout\Port\CatalogComplementSetCandidatesPort;
use App\Domain\Checkout\Port\CatalogGiftCandidatesPort;
use App\Domain\Checkout\Port\CatalogPricingPort;
use App\Domain\Checkout\Port\CatalogRollMetaPort;
use App\Domain\Checkout\Port\ClientProfilePort;
use App\Domain\Checkout\Repository\CheckoutRepository;
use App\Infrastructure\Checkout\Port\CatalogComplementSetCandidatesAdapter;
use App\Infrastructure\Checkout\Port\CatalogGiftCandidatesAdapter;
use App\Infrastructure\Checkout\Port\CatalogPricingAdapter;
use App\Infrastructure\Checkout\Port\CatalogRollMetaAdapter;
use App\Infrastructure\Checkout\Port\ClientProfileAdapter;
use App\Infrastructure\Checkout\Repository\EloquentCheckoutRepository;
use Illuminate\Support\ServiceProvider;

final class CheckoutServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CheckoutRepository::class, EloquentCheckoutRepository::class);
        $this->app->bind(CatalogPricingPort::class, CatalogPricingAdapter::class);
        $this->app->bind(CatalogGiftCandidatesPort::class, CatalogGiftCandidatesAdapter::class);
        $this->app->bind(CatalogComplementSetCandidatesPort::class, CatalogComplementSetCandidatesAdapter::class);
        $this->app->bind(CatalogRollMetaPort::class, CatalogRollMetaAdapter::class);
        $this->app->bind(ClientProfilePort::class, ClientProfileAdapter::class);
    }
}
