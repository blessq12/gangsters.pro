<?php

use App\Domain\Shopping\CartRules\Rules\ComplementRule;
use App\Domain\Shopping\CartRules\Rules\GiftPromotionRule;

/**
 * Правила корзины: порядок по sort (asc).
 * «Ролл», комплект и подарок — флаги на товаре (PRD_products), не теги витрины.
 *
 * @see App\Domain\Shopping\CartRules\ShoppingCartRuleEngine
 */
return [
    'rules' => [
        [
            'id' => 'complement',
            'class' => ComplementRule::class,
            'enabled' => (bool) env('SHOPPING_RULE_COMPLEMENT_ENABLED', true),
            'sort' => 10,
            'options' => [
                'rolls_per_complement' => (int) env('SHOPPING_RULE_ROLLS_PER_COMPLEMENT', 2),
            ],
        ],
        [
            'id' => 'gift_promotion',
            'class' => GiftPromotionRule::class,
            'enabled' => (bool) env('SHOPPING_RULE_GIFT_ENABLED', true),
            'sort' => 20,
            'options' => [
                'threshold_kopecks' => (int) env('SHOPPING_RULE_GIFT_THRESHOLD_KOPECKS', 180_000),
            ],
        ],
    ],
];
