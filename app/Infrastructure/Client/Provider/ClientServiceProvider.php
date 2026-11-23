<?php

namespace App\Infrastructure\Client\Provider;

use App\Domain\Supporting\Client\Port\ClientOrdersPort;
use App\Domain\Supporting\Client\Repository\ClientRepository;
use App\Infrastructure\Client\Adapter\EloquentClientOrdersPort;
use App\Infrastructure\Client\Repository\EloquentClientRepository;
use Illuminate\Support\ServiceProvider;

final class ClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ClientRepository::class, EloquentClientRepository::class);
        $this->app->bind(ClientOrdersPort::class, EloquentClientOrdersPort::class);
    }
}
