<?php

namespace App\Domain\Shopping\CartRules;

interface ShoppingCartRuleInterface
{
    public function apply(CartState $state, RuleEvaluationContext $context): CartState;
}
