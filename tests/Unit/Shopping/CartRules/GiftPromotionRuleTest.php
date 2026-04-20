<?php

namespace Tests\Unit\Shopping\CartRules;

use App\Domain\Shopping\CartRules\CartLineItem;
use App\Domain\Shopping\CartRules\CartLineOrigin;
use App\Domain\Shopping\CartRules\CartState;
use App\Domain\Shopping\CartRules\ProductRuleView;
use App\Domain\Shopping\CartRules\RuleEvaluationContext;
use App\Domain\Shopping\CartRules\Rules\GiftPromotionRule;
use PHPUnit\Framework\TestCase;

final class GiftPromotionRuleTest extends TestCase
{
    public function test_eligible_and_applies_selected_gift(): void
    {
        $state = new CartState(
            [new CartLineItem(1, 2, CartLineOrigin::User, 'user:1')],
            [],
            [],
            0,
            0,
            0,
        );
        $ctx = new RuleEvaluationContext([
            1 => new ProductRuleView(1, 100_000, false, false, false),
            7 => new ProductRuleView(7, 400_00, false, true, false),
        ], [], 7, [7]);

        $rule = new GiftPromotionRule(['threshold_kopecks' => 180_000]);

        $out = $rule->apply($state, $ctx);

        $this->assertSame('gift_applied', $out->promoState[GiftPromotionRule::PROMO_KEY]['phase']);
        $this->assertCount(1, $out->systemLines);
        $this->assertSame(7, $out->systemLines[0]->productId);
        $this->assertSame(GiftPromotionRule::LINE_KEY, $out->systemLines[0]->lineKey);
    }

    public function test_below_threshold_clears_promo(): void
    {
        $state = new CartState(
            [new CartLineItem(1, 1, CartLineOrigin::User, 'user:1')],
            [new CartLineItem(7, 1, CartLineOrigin::System, GiftPromotionRule::LINE_KEY)],
            [GiftPromotionRule::PROMO_KEY => ['eligible' => true]],
            0,
            0,
            0,
        );
        $ctx = new RuleEvaluationContext([
            1 => new ProductRuleView(1, 100_00, false, false, false),
            7 => new ProductRuleView(7, 400_00, false, true, false),
        ], [], 7, [7]);

        $rule = new GiftPromotionRule(['threshold_kopecks' => 180_000]);

        $out = $rule->apply($state, $ctx);

        $this->assertArrayNotHasKey(GiftPromotionRule::PROMO_KEY, $out->promoState);
        $this->assertSame([], $out->systemLines);
    }

    public function test_select_gift_phase_when_no_valid_selection(): void
    {
        $state = new CartState(
            [new CartLineItem(1, 2, CartLineOrigin::User, 'user:1')],
            [],
            [],
            0,
            0,
            0,
        );
        $ctx = new RuleEvaluationContext([
            1 => new ProductRuleView(1, 100_000, false, false, false),
            7 => new ProductRuleView(7, 400_00, false, true, false),
        ], [], null, [7]);

        $rule = new GiftPromotionRule(['threshold_kopecks' => 180_000]);

        $out = $rule->apply($state, $ctx);

        $this->assertSame('select_gift', $out->promoState[GiftPromotionRule::PROMO_KEY]['phase']);
        $this->assertSame([], $out->systemLines);
    }
}
