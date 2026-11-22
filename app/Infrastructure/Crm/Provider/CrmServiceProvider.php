<?php

namespace App\Infrastructure\Crm\Provider;

use App\Domain\Supporting\Customer\Port\CrmClientOrdersPort;
use App\Domain\Supporting\Customer\Repository\ClientRepository;
use App\Infrastructure\Crm\Adapter\EloquentCrmClientOrdersPort;
use App\Infrastructure\Crm\Repository\EloquentClientRepository;
use Illuminate\Support\ServiceProvider;

final class CrmServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ClientRepository::class, EloquentClientRepository::class);
        $this->app->bind(CrmClientOrdersPort::class, EloquentCrmClientOrdersPort::class);
    }
}
