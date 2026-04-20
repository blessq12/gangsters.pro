<?php

namespace Tests\Unit\Shopping\CartRules;

use App\Domain\Shopping\CartRules\CartLineItem;
use App\Domain\Shopping\CartRules\CartLineOrigin;
use App\Domain\Shopping\CartRules\CartState;
use App\Domain\Shopping\CartRules\ProductRuleView;
use App\Domain\Shopping\CartRules\RuleEvaluationContext;
use App\Domain\Shopping\CartRules\Rules\ComplementRule;
use PHPUnit\Framework\TestCase;

final class ComplementRuleTest extends TestCase
{
    public function test_adds_complement_when_two_rolls_and_product_configured(): void
    {
        $state = new CartState(
            [
                new CartLineItem(10, 2, CartLineOrigin::User, 'user:10'),
            ],
            [],
            [],
            0,
            0,
            0,
        );
        $ctx = new RuleEvaluationContext([
            10 => new ProductRuleView(10, 500_00, true, false, false),
            99 => new ProductRuleView(99, 50_00, false, false, true),
        ], [99], null, []);

        $rule = new ComplementRule([
            'rolls_per_complement' => 2,
        ]);

        $out = $rule->apply($state, $ctx);

        $this->assertCount(1, $out->systemLines);
        $this->assertSame(99, $out->systemLines[0]->productId);
        $this->assertSame(1, $out->systemLines[0]->quantity);
        $this->assertSame(ComplementRule::LINE_KEY.':99', $out->systemLines[0]->lineKey);
    }

    public function test_adds_all_marked_complement_products(): void
    {
        $state = new CartState(
            [new CartLineItem(10, 4, CartLineOrigin::User, 'user:10')],
            [],
            [],
            0,
            0,
            0,
        );
        $ctx = new RuleEvaluationContext([
            10 => new ProductRuleView(10, 500_00, true, false, false),
            99 => new ProductRuleView(99, 50_00, false, false, true),
            100 => new ProductRuleView(100, 70_00, false, false, true),
        ], [99, 100], null, []);

        $rule = new ComplementRule(['rolls_per_complement' => 2]);

        $out = $rule->apply($state, $ctx);

        $this->assertCount(2, $out->systemLines);
        $this->assertSame([99, 100], array_values(array_map(
            static fn (CartLineItem $line) => $line->productId,
            $out->systemLines,
        )));
        $this->assertSame([2, 2], array_values(array_map(
            static fn (CartLineItem $line) => $line->quantity,
            $out->systemLines,
        )));
    }

    public function test_disabled_when_no_complement_products_in_context(): void
    {
        $state = new CartState(
            [new CartLineItem(10, 4, CartLineOrigin::User, 'user:10')],
            [new CartLineItem(99, 9, CartLineOrigin::System, ComplementRule::LINE_KEY)],
            [],
            0,
            0,
            0,
        );
        $ctx = new RuleEvaluationContext([
            10 => new ProductRuleView(10, 100, true, false, false),
        ], [], null, []);

        $rule = new ComplementRule(['rolls_per_complement' => 2]);

        $out = $rule->apply($state, $ctx);

        $this->assertSame([], $out->systemLines);
    }
}
