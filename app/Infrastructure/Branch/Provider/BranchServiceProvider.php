<?php

namespace App\Infrastructure\Branch\Provider;

use App\Domain\Supporting\Branch\Port\BranchCommercialRulesClonePort;
use App\Domain\Supporting\Branch\Port\BranchDeliveryConfigClonePort;
use App\Domain\Supporting\Branch\Repository\BranchCommercialRulesRepository;
use App\Domain\Supporting\Branch\Repository\BranchRepository;
use App\Domain\Supporting\Branch\Repository\DeliveryConfigurationRepository;
use App\Infrastructure\Branch\Adapter\EloquentBranchCommercialRulesClonePort;
use App\Infrastructure\Branch\Adapter\EloquentBranchDeliveryConfigClonePort;
use App\Infrastructure\Branch\Repository\EloquentBranchCommercialRulesRepository;
use App\Infrastructure\Branch\Repository\EloquentBranchRepository;
use App\Infrastructure\Branch\Repository\EloquentDeliveryConfigurationRepository;
use Illuminate\Support\ServiceProvider;

final class BranchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BranchRepository::class, EloquentBranchRepository::class);
        $this->app->bind(BranchCommercialRulesRepository::class, EloquentBranchCommercialRulesRepository::class);
        $this->app->bind(DeliveryConfigurationRepository::class, EloquentDeliveryConfigurationRepository::class);
        $this->app->bind(BranchCommercialRulesClonePort::class, EloquentBranchCommercialRulesClonePort::class);
        $this->app->bind(BranchDeliveryConfigClonePort::class, EloquentBranchDeliveryConfigClonePort::class);
    }
}
