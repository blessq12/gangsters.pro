<?php

namespace App\Application\Shopping\CartRules;

use App\Domain\Shopping\CartRules\CartPricing;
use App\Domain\Shopping\CartRules\CartState;
use App\Domain\Shopping\CartRules\ShoppingCartRuleEngine;
use App\Domain\Shopping\Entities\ShoppingSession;

final class ResolveShoppingCartUseCase
{
    public function __construct(
        private readonly CartStateFactory $cartStateFactory,
        private readonly RuleEvaluationContextBuilder $contextBuilder,
        private readonly ShoppingCartRuleEngine $ruleEngine,
    ) {}

    public function execute(ShoppingSession $session): CartState
    {
        $state = $this->cartStateFactory->fromSession($session);
        $context = $this->contextBuilder->build($session, $state);
        $afterRules = $this->ruleEngine->apply($state, $context);

        return CartPricing::apply($afterRules, $context);
    }
}
