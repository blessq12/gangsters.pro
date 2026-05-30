<?php

namespace App\Infrastructure\Operations\Repository;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\CartRules\Contracts\CartRuleSettingsRepository;
use App\Application\Operations\CartRules\DTO\CartRuleSettingsDTO;
use App\Infrastructure\Shopping\Model\SHP_ShoppingCartRuleSetting;

final class EloquentCartRuleSettingsRepository implements CartRuleSettingsRepository
{
    public function get(): CartRuleSettingsDTO
    {
        $row = SHP_ShoppingCartRuleSetting::query()->first();
        if ($row === null) {
            throw new ApiException('Cart rule settings not found.', 404);
        }

        return $this->map($row);
    }

    public function save(CartRuleSettingsDTO $settings): void
    {
        $row = SHP_ShoppingCartRuleSetting::query()->first();
        if ($row === null) {
            throw new ApiException('Cart rule settings not found.', 404);
        }

        SHP_ShoppingCartRuleSetting::withoutEvents(function () use ($row, $settings): void {
            $row->complement_rule_enabled = $settings->complementRuleEnabled;
            $row->gift_rule_enabled = $settings->giftRuleEnabled;
            $row->gift_threshold_kopecks = $settings->giftThresholdKopecks;
            $row->rolls_per_complement = $settings->rollsPerComplement;
            $row->complement_rule_sort = $settings->complementRuleSort;
            $row->gift_rule_sort = $settings->giftRuleSort;
            $row->save();
        });
    }

    private function map(SHP_ShoppingCartRuleSetting $row): CartRuleSettingsDTO
    {
        return new CartRuleSettingsDTO(
            complementRuleEnabled: (bool) $row->complement_rule_enabled,
            giftRuleEnabled: (bool) $row->gift_rule_enabled,
            giftThresholdKopecks: (int) $row->gift_threshold_kopecks,
            rollsPerComplement: (int) $row->rolls_per_complement,
            complementRuleSort: (int) $row->complement_rule_sort,
            giftRuleSort: (int) $row->gift_rule_sort,
        );
    }
}
