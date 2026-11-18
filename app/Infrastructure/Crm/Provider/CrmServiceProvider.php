<?php

namespace App\Infrastructure\Crm\Provider;

use App\Domain\Crm\Repository\ClientRepository;
use App\Infrastructure\Crm\Repository\EloquentClientRepository;
use Illuminate\Support\ServiceProvider;

final class CrmServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ClientRepository::class, EloquentClientRepository::class);
    }
}
