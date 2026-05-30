<?php

namespace App\Filament\Operations\Support;

use App\Application\Operations\CartRules\DTO\CartRuleSettingsDTO;
use App\Support\Money;

final class FilamentCartRuleSettingsFormMapper
{
    /**
     * @param  array<string, mixed>  $detail
     * @return array<string, mixed>
     */
    public static function toFormState(array $detail): array
    {
        return [
            'complement_rule_enabled' => (bool) ($detail['complement_rule_enabled'] ?? false),
            'gift_rule_enabled' => (bool) ($detail['gift_rule_enabled'] ?? false),
            'gift_threshold_rubles' => $detail['gift_threshold_rubles'] ?? 0,
            'rolls_per_complement' => (int) ($detail['rolls_per_complement'] ?? 0),
            'complement_rule_sort' => (int) ($detail['complement_rule_sort'] ?? 0),
            'gift_rule_sort' => (int) ($detail['gift_rule_sort'] ?? 0),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function toDto(array $data): CartRuleSettingsDTO
    {
        return new CartRuleSettingsDTO(
            complementRuleEnabled: (bool) ($data['complement_rule_enabled'] ?? false),
            giftRuleEnabled: (bool) ($data['gift_rule_enabled'] ?? false),
            giftThresholdKopecks: Money::apiRublesToKopecks((float) ($data['gift_threshold_rubles'] ?? 0)) ?? 0,
            rollsPerComplement: (int) ($data['rolls_per_complement'] ?? 0),
            complementRuleSort: (int) ($data['complement_rule_sort'] ?? 0),
            giftRuleSort: (int) ($data['gift_rule_sort'] ?? 0),
        );
    }
}
