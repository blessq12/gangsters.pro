<?php

namespace App\Infrastructure\Shopping\CartRules;

use App\Infrastructure\Shopping\Model\SHP_ShoppingCartRuleSetting;
use Illuminate\Support\Facades\Schema;

/**
 * Подмешивает настройки правил корзины из БД в runtime config (после загрузки файла).
 */
final class ShoppingCartRuleConfigRegistrar
{
    public static function apply(): void
    {
        if (! Schema::hasTable('SHP_shopping_cart_rule_settings')) {
            return;
        }

        $row = SHP_ShoppingCartRuleSetting::query()->first();
        if ($row === null) {
            return;
        }

        $rules = config('shopping_cart_rules.rules', []);
        foreach ($rules as $i => $rule) {
            $id = (string) ($rule['id'] ?? '');
            if ($id === 'complement') {
                $rules[$i]['enabled'] = (bool) $row->complement_rule_enabled;
                $rules[$i]['sort'] = (int) $row->complement_rule_sort;
                $baseOpts = isset($rule['options']) && is_array($rule['options']) ? $rule['options'] : [];
                $rules[$i]['options'] = array_replace($baseOpts, [
                    'rolls_per_complement' => max(1, (int) $row->rolls_per_complement),
                ]);
            }
            if ($id === 'gift_promotion') {
                $rules[$i]['enabled'] = (bool) $row->gift_rule_enabled;
                $rules[$i]['sort'] = (int) $row->gift_rule_sort;
                $baseOpts = isset($rule['options']) && is_array($rule['options']) ? $rule['options'] : [];
                $rules[$i]['options'] = array_replace($baseOpts, [
                    'threshold_kopecks' => max(1, (int) $row->gift_threshold_kopecks),
                ]);
            }
        }

        config(['shopping_cart_rules.rules' => $rules]);
    }
}
