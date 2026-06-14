<?php

namespace App\Providers;

use App\Domain\Promotion\Repository\PromotionPolicyRepository;
use App\Infrastructure\Promotion\Repository\EloquentPromotionPolicyRepository;
use Illuminate\Support\ServiceProvider;

final class PromotionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            PromotionPolicyRepository::class,
            EloquentPromotionPolicyRepository::class,
        );
    }
}
