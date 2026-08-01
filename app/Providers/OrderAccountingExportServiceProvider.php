<?php

namespace App\Providers;

use App\Application\OrderAccountingExport\Handler\OnOrderCreated;
use App\Application\OrderAccountingExport\Port\AccountingSystemAdapter;
use App\Application\OrderAccountingExport\Services\AccountingAdapterRegistry;
use App\Domain\Order\Event\OrderCreated;
use App\Domain\OrderAccountingExport\Repository\AccountingProductBindingRepository;
use App\Domain\OrderAccountingExport\Repository\ExportAttemptRepository;
use App\Infrastructure\OrderAccountingExport\Adapter\FrontpadAccountingSystemAdapter;
use App\Infrastructure\OrderAccountingExport\Adapter\StubAccountingSystemAdapter;
use App\Infrastructure\OrderAccountingExport\Repository\ConfigAccountingProductBindingRepository;
use App\Infrastructure\OrderAccountingExport\Repository\EloquentExportAttemptRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class OrderAccountingExportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ExportAttemptRepository::class, EloquentExportAttemptRepository::class);
        $this->app->bind(AccountingProductBindingRepository::class, ConfigAccountingProductBindingRepository::class);

        $this->app->singleton(AccountingAdapterRegistry::class, function ($app): AccountingAdapterRegistry {
            return new AccountingAdapterRegistry([
                $app->make(StubAccountingSystemAdapter::class),
                $app->make(FrontpadAccountingSystemAdapter::class),
            ]);
        });

        $this->app->tag([
            StubAccountingSystemAdapter::class,
            FrontpadAccountingSystemAdapter::class,
        ], AccountingSystemAdapter::class);
    }

    public function boot(): void
    {
        Event::listen(
            OrderCreated::class,
            [OnOrderCreated::class, 'handle'],
        );
    }
}
