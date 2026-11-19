<?php

namespace App\Infrastructure\Promotion\Provider;

use App\Domain\Core\Order\Repository\ProductPromotionRoleRepository;
use App\Domain\Core\Order\Repository\PromotionPolicyRepository;
use App\Domain\Core\Delivery\Tariff\Repository\PromotionZoneRepository;
use App\Infrastructure\Promotion\Repository\EloquentProductPromotionRoleRepository;
use App\Infrastructure\Promotion\Repository\EloquentPromotionPolicyRepository;
use App\Infrastructure\Promotion\Repository\EloquentPromotionZoneRepository;
use Illuminate\Support\ServiceProvider;

final class PromotionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PromotionPolicyRepository::class, EloquentPromotionPolicyRepository::class);
        $this->app->bind(ProductPromotionRoleRepository::class, EloquentProductPromotionRoleRepository::class);
        $this->app->bind(PromotionZoneRepository::class, EloquentPromotionZoneRepository::class);
    }
}
