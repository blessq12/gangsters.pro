<?php

namespace App\Providers;

use App\Application\AggregatorIngress\Port\IngressPartnerAdapter;
use App\Application\AggregatorIngress\Port\IngressPartnerAuthenticator;
use App\Application\AggregatorIngress\Services\IngressPartnerAdapterRegistry;
use App\Domain\AggregatorIngress\Repository\IngressAuditRepository;
use App\Domain\AggregatorIngress\Repository\PartnerCatalogBindingRepository;
use App\Infrastructure\AggregatorIngress\Adapter\ChibbisIngressPartnerAdapter;
use App\Infrastructure\AggregatorIngress\Adapter\KuperIngressPartnerAdapter;
use App\Infrastructure\AggregatorIngress\Adapter\StubIngressPartnerAdapter;
use App\Infrastructure\AggregatorIngress\Adapter\YandexEdaIngressPartnerAdapter;
use App\Infrastructure\AggregatorIngress\Auth\ConfigIngressPartnerAuthenticator;
use App\Infrastructure\AggregatorIngress\Repository\EloquentIngressAuditRepository;
use App\Infrastructure\AggregatorIngress\Repository\EloquentPartnerCatalogBindingRepository;
use Illuminate\Support\ServiceProvider;

final class AggregatorIngressServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(IngressAuditRepository::class, EloquentIngressAuditRepository::class);
        $this->app->bind(PartnerCatalogBindingRepository::class, EloquentPartnerCatalogBindingRepository::class);
        $this->app->bind(IngressPartnerAuthenticator::class, ConfigIngressPartnerAuthenticator::class);

        $this->app->singleton(IngressPartnerAdapterRegistry::class, function ($app): IngressPartnerAdapterRegistry {
            return new IngressPartnerAdapterRegistry([
                $app->make(StubIngressPartnerAdapter::class),
                $app->make(YandexEdaIngressPartnerAdapter::class),
                $app->make(ChibbisIngressPartnerAdapter::class),
                $app->make(KuperIngressPartnerAdapter::class),
            ]);
        });

        $this->app->tag([
            StubIngressPartnerAdapter::class,
            YandexEdaIngressPartnerAdapter::class,
            ChibbisIngressPartnerAdapter::class,
            KuperIngressPartnerAdapter::class,
        ], IngressPartnerAdapter::class);
    }

    public function boot(): void
    {
        //
    }
}
