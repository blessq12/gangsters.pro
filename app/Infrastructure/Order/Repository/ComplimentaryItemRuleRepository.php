<?php

namespace App\Infrastructure\Order\Repository;

use App\Infrastructure\Order\Model\ComplimentaryItemRule;
use Illuminate\Support\Facades\DB;

class ComplimentaryItemRuleRepository
{
    /**
     * @param array<int, int> $triggerCategoryIds
     * @return array<int, array{rule_id: int, trigger_category_id: int, gift_product_id: int, priority: int}>
     */
    public function findActiveByTriggerCategoryIds(array $triggerCategoryIds): array
    {
        if ($triggerCategoryIds === []) {
            return [];
        }

        return ComplimentaryItemRule::query()
            ->select([
                'complimentary_item_rules.id as rule_id',
                DB::raw('COALESCE(complimentary_item_rule_categories.category_id, complimentary_item_rules.trigger_category_id) as trigger_category_id'),
                'complimentary_item_rules.gift_product_id',
                'complimentary_item_rules.priority',
            ])
            ->leftJoin(
                'complimentary_item_rule_categories',
                'complimentary_item_rule_categories.rule_id',
                '=',
                'complimentary_item_rules.id',
            )
            ->where('complimentary_item_rules.is_active', true)
            ->where(function ($query) use ($triggerCategoryIds): void {
                $query
                    ->whereIn('complimentary_item_rule_categories.category_id', $triggerCategoryIds)
                    ->orWhereIn('complimentary_item_rules.trigger_category_id', $triggerCategoryIds);
            })
            ->orderByDesc('priority')
            ->orderBy('rule_id')
            ->get()
            ->map(static fn (object $rule): array => [
                'rule_id' => (int) $rule->rule_id,
                'trigger_category_id' => (int) $rule->trigger_category_id,
                'gift_product_id' => (int) $rule->gift_product_id,
                'priority' => (int) $rule->priority,
            ])
            ->all();
    }
}
