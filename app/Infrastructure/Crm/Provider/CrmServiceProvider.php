<?php

namespace App\Infrastructure\Crm\Provider;

use App\Domain\Crm\Repository\ClientRepository;
use App\Domain\Crm\Repository\OrderHistoryRepository;
use App\Infrastructure\Crm\Repository\EloquentClientRepository;
use App\Infrastructure\Crm\Repository\EloquentOrderHistoryRepository;
use Illuminate\Support\ServiceProvider;

final class CrmServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ClientRepository::class, EloquentClientRepository::class);
        $this->app->bind(OrderHistoryRepository::class, EloquentOrderHistoryRepository::class);
    }
}
