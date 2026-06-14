<?php

namespace App\Providers;

use App\Domain\Delivery\Repository\DeliveryConfigurationRepository;
use App\Infrastructure\Delivery\Repository\EloquentDeliveryConfigurationRepository;
use Illuminate\Support\ServiceProvider;

final class DeliveryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            DeliveryConfigurationRepository::class,
            EloquentDeliveryConfigurationRepository::class,
        );
    }
}
