<?php

namespace App\Providers;

use App\Application\Operations\CartRules\Contracts\CartRuleSettingsRepository;
use App\Application\Operations\CartRules\Contracts\UpdateProductCartRuleFlagsContract;
use App\Application\Operations\CartRules\Command\UpdateProductCartRuleFlagsUseCase;
use App\Application\Operations\Order\Contracts\AdminOrderReadRepository;
use App\Infrastructure\Operations\Repository\EloquentAdminOrderReadRepository;
use App\Infrastructure\Operations\Repository\EloquentCartRuleSettingsRepository;
use Illuminate\Support\ServiceProvider;

class OperationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AdminOrderReadRepository::class, EloquentAdminOrderReadRepository::class);
        $this->app->bind(CartRuleSettingsRepository::class, EloquentCartRuleSettingsRepository::class);
        $this->app->bind(UpdateProductCartRuleFlagsContract::class, UpdateProductCartRuleFlagsUseCase::class);
    }
}
