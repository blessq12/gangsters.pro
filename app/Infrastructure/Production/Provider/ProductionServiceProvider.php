<?php

namespace App\Infrastructure\Production\Provider;

use App\Domain\Production\Repository\KitchenTicketRepository;
use App\Infrastructure\Production\Repository\EloquentKitchenTicketRepository;
use Illuminate\Support\ServiceProvider;

final class ProductionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(KitchenTicketRepository::class, EloquentKitchenTicketRepository::class);
    }
}
