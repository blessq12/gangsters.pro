<?php

namespace Tests\Unit\Shopping\CartRules;

use App\Domain\Shopping\CartRules\CartLineItem;
use App\Domain\Shopping\CartRules\CartLineOrigin;
use App\Domain\Shopping\CartRules\CartPricing;
use App\Domain\Shopping\CartRules\CartState;
use App\Domain\Shopping\CartRules\ProductRuleView;
use App\Domain\Shopping\CartRules\RuleEvaluationContext;
use App\Domain\Shopping\CartRules\Rules\GiftPromotionRule;
use PHPUnit\Framework\TestCase;

final class CartPricingTest extends TestCase
{
    public function test_gift_line_has_zero_unit_price(): void
    {
        $state = new CartState(
            [new CartLineItem(1, 1, CartLineOrigin::User, 'user:1')],
            [new CartLineItem(7, 1, CartLineOrigin::System, GiftPromotionRule::LINE_KEY)],
            [],
            0,
            0,
            0,
        );
        $ctx = new RuleEvaluationContext([
            1 => new ProductRuleView(1, 100_000, false, false, false),
            7 => new ProductRuleView(7, 400_00, false, false, false),
        ], [], null, []);

        $priced = CartPricing::apply($state, $ctx);

        $this->assertSame(0, $priced->systemLines[0]->finalUnitPriceKopecks);
        $this->assertSame(100_000, $priced->grandTotalKopecks);
    }

    public function test_complement_line_has_zero_unit_price(): void
    {
        $state = new CartState(
            [new CartLineItem(1, 1, CartLineOrigin::User, 'user:1')],
            [new CartLineItem(9, 2, CartLineOrigin::System, 'complement:set:9')],
            [],
            0,
            0,
            0,
        );
        $ctx = new RuleEvaluationContext([
            1 => new ProductRuleView(1, 100_000, false, false, false),
            9 => new ProductRuleView(9, 50_00, false, false, true),
        ], [9], null, []);

        $priced = CartPricing::apply($state, $ctx);

        $this->assertSame(0, $priced->systemLines[0]->finalUnitPriceKopecks);
        $this->assertSame(100_000, $priced->grandTotalKopecks);
    }
}
