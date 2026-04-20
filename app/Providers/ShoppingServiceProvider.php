<?php

namespace App\Providers;

use App\Domain\Shopping\CartRules\Contracts\ProductRuleViewProviderInterface;
use App\Domain\Shopping\CartRules\ShoppingCartRuleEngine;
use App\Domain\Shopping\CartRules\ShoppingCartRuleInterface;
use App\Domain\Shopping\Repositories\ShoppingSessionRepositoryInterface;
use App\Infrastructure\Shopping\CartRules\EloquentProductRuleViewProvider;
use App\Infrastructure\Shopping\CartRules\ShoppingCartRuleConfigRegistrar;
use App\Infrastructure\Shopping\Repository\ShoppingSessionRepository;
use Illuminate\Support\ServiceProvider;

class ShoppingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ShoppingSessionRepositoryInterface::class, ShoppingSessionRepository::class);
        $this->app->bind(ProductRuleViewProviderInterface::class, EloquentProductRuleViewProvider::class);

        $this->app->singleton(ShoppingCartRuleEngine::class, function ($app): ShoppingCartRuleEngine {
            $entries = (array) config('shopping_cart_rules.rules', []);
            usort($entries, static fn (array $a, array $b): int => ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0));
            $rules = [];
            foreach ($entries as $entry) {
                if (! ($entry['enabled'] ?? true)) {
                    continue;
                }
                $class = $entry['class'] ?? null;
                if (! is_string($class) || ! is_a($class, ShoppingCartRuleInterface::class, true)) {
                    continue;
                }
                $options = isset($entry['options']) && is_array($entry['options']) ? $entry['options'] : [];
                $rules[] = $app->make($class, ['options' => $options]);
            }

            return new ShoppingCartRuleEngine($rules);
        });
    }

    public function boot(): void
    {
        ShoppingCartRuleConfigRegistrar::apply();
    }
}
