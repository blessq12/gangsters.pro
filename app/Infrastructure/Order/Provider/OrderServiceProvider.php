<?php

namespace App\Infrastructure\Order\Provider;

use App\Domain\Order\Port\PromotionDeliveryPricingPort;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\Repository\PromotionPolicyRepository;
use App\Infrastructure\Order\Port\PromotionDeliveryPricingAdapter;
use App\Infrastructure\Order\Repository\EloquentOrderRepository;
use App\Infrastructure\Order\Repository\EloquentPromotionPolicyRepository;
use Illuminate\Support\ServiceProvider;

final class OrderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrderRepository::class, EloquentOrderRepository::class);
        $this->app->bind(PromotionPolicyRepository::class, EloquentPromotionPolicyRepository::class);
        $this->app->bind(PromotionDeliveryPricingPort::class, PromotionDeliveryPricingAdapter::class);
    }
}
