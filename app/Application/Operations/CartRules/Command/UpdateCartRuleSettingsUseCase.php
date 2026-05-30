<?php

namespace App\Application\Operations\CartRules\Command;

use App\Application\Operations\CartRules\Contracts\CartRuleSettingsRepository;
use App\Application\Operations\CartRules\DTO\CartRuleSettingsDTO;
use App\Application\Operations\CartRules\Query\GetAdminCartRuleSettingsQuery;
use App\Domain\Shopping\CartRules\ShoppingCartRuleEngine;
use App\Infrastructure\Shopping\CartRules\ShoppingCartRuleConfigRegistrar;

final class UpdateCartRuleSettingsUseCase
{
    public function __construct(
        private readonly CartRuleSettingsRepository $settings,
        private readonly GetAdminCartRuleSettingsQuery $settingsQuery,
    ) {
    }

    public function execute(CartRuleSettingsDTO $dto): array
    {
        $this->settings->save($dto);

        ShoppingCartRuleConfigRegistrar::apply();
        if (app()->resolved(ShoppingCartRuleEngine::class)) {
            app()->forgetInstance(ShoppingCartRuleEngine::class);
        }

        return $this->settingsQuery->execute();
    }
}
