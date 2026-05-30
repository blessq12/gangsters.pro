<?php

namespace App\Application\Operations\CartRules\Contracts;

use App\Application\Operations\CartRules\DTO\CartRuleSettingsDTO;

interface CartRuleSettingsRepository
{
    public function get(): CartRuleSettingsDTO;

    public function save(CartRuleSettingsDTO $settings): void;
}
