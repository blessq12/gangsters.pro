<?php

namespace App\Application\Operations\CartRules\DTO;

final readonly class CartRuleSettingsDTO
{
    public function __construct(
        public bool $complementRuleEnabled,
        public bool $giftRuleEnabled,
        public int $giftThresholdKopecks,
        public int $rollsPerComplement,
        public int $complementRuleSort,
        public int $giftRuleSort,
    ) {
    }
}
