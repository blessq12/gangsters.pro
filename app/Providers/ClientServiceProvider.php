<?php

namespace App\Providers;

use App\Domain\Client\Repository\ClientRepository as ClientRepositoryContract;
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
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    }
}

