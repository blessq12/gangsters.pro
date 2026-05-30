<?php

namespace App\Application\Operations\CartRules\Query;

use App\Application\Operations\CartRules\Contracts\CartRuleSettingsRepository;
use App\Support\Money;

final class GetAdminCartRuleSettingsQuery
{
    public function __construct(
        private readonly CartRuleSettingsRepository $settings,
    ) {
    }

    public function execute(): array
    {
        $dto = $this->settings->get();

        return [
            'complement_rule_enabled' => $dto->complementRuleEnabled,
            'gift_rule_enabled' => $dto->giftRuleEnabled,
            'gift_threshold_kopecks' => $dto->giftThresholdKopecks,
            'gift_threshold_rubles' => Money::kopecksToApiRubles($dto->giftThresholdKopecks),
            'rolls_per_complement' => $dto->rollsPerComplement,
            'complement_rule_sort' => $dto->complementRuleSort,
            'gift_rule_sort' => $dto->giftRuleSort,
        ];
    }
}
