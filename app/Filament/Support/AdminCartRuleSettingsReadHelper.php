<?php

namespace App\Filament\Support;

use App\Application\Common\Exceptions\ApiException;
use App\Infrastructure\Shopping\Model\SHP_ShoppingCartRuleSetting;
use App\Support\Money;

final class AdminCartRuleSettingsReadHelper
{
    /**
     * @return array<string, mixed>
     */
    public function settingsState(): array
    {
        $row = SHP_ShoppingCartRuleSetting::query()->first();
        if ($row === null) {
            throw new ApiException('Cart rule settings not found.', 404);
        }

        return [
            'complement_rule_enabled' => (bool) $row->complement_rule_enabled,
            'gift_rule_enabled' => (bool) $row->gift_rule_enabled,
            'gift_threshold_kopecks' => (int) $row->gift_threshold_kopecks,
            'gift_threshold_rubles' => Money::kopecksToApiRubles((int) $row->gift_threshold_kopecks),
            'rolls_per_complement' => (int) $row->rolls_per_complement,
            'complement_rule_sort' => (int) $row->complement_rule_sort,
            'gift_rule_sort' => (int) $row->gift_rule_sort,
        ];
    }
}
