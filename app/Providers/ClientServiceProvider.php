<?php

namespace App\Providers;

use App\Application\Reporting\Query\ClientOrderSummaryReader;
use App\Domain\Client\Factory\ClientFactory;
use App\Domain\Client\Repository\ClientRepository as ClientRepositoryContract;
use App\Infrastructure\Client\Repository\ClientRepository as EloquentClientRepository;
use App\Infrastructure\Reporting\Query\EloquentClientOrderSummaryReader;
use Illuminate\Support\ServiceProvider;

class ClientServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ClientRepositoryContract::class, EloquentClientRepository::class);
        $this->app->bind(ClientOrderSummaryReader::class, EloquentClientOrderSummaryReader::class);
        $this->app->singleton(ClientFactory::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    }
}

