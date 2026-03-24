<?php

namespace App\Providers;

use App\Application\Client\Query\ClientSummaryReader;
use App\Domain\Client\Factory\ClientFactory;
use App\Domain\Client\Repository\ClientRepository as ClientRepositoryContract;
use App\Infrastructure\Client\Query\EloquentClientSummaryReader;
use App\Infrastructure\Client\Repository\ClientRepository as EloquentClientRepository;
use Illuminate\Support\ServiceProvider;

class ClientServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ClientRepositoryContract::class, EloquentClientRepository::class);
        $this->app->bind(ClientSummaryReader::class, EloquentClientSummaryReader::class);
        $this->app->singleton(ClientFactory::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    }
}

