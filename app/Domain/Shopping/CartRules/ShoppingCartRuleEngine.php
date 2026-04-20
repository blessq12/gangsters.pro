<?php

namespace App\Domain\Shopping\CartRules;

/**
 * Конвейер правил: последовательное применение без ветвления по типам товаров.
 */
final class ShoppingCartRuleEngine
{
    /**
     * @param  iterable<int, ShoppingCartRuleInterface>  $rules
     */
    public function __construct(
        private readonly iterable $rules,
    ) {}

    public function apply(CartState $state, RuleEvaluationContext $context): CartState
    {
        $current = $state;
        foreach ($this->rules as $rule) {
            $current = $rule->apply($current, $context);
        }

        return $current;
    }
}
