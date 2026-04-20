<?php

namespace App\Domain\Shopping\CartRules;

/**
 * Минимальное представление товара для правил корзины (без тегов витрины).
 */
final readonly class ProductRuleView
{
    public function __construct(
        public int $id,
        public int $priceKopecks,
        public bool $countsAsRollUnit,
        public bool $giftCandidate,
        public bool $isComplementSetProduct,
    ) {}
}
