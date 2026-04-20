<?php

namespace App\Domain\Shopping\CartRules;

/**
 * Неизменяемый контекст оценки: каталог и выбор пользователя из черновика.
 *
 * @param  array<int, ProductRuleView>  $productsById
 * @param  int[]  $complementProductIds
 * @param  int[]  $giftCandidateProductIds
 */
final readonly class RuleEvaluationContext
{
    /**
     * @param  array<int, ProductRuleView>  $productsById
     * @param  int[]  $complementProductIds
     * @param  int[]  $giftCandidateProductIds
     */
    public function __construct(
        public array $productsById,
        public array $complementProductIds,
        public ?int $selectedGiftProductId,
        public array $giftCandidateProductIds,
    ) {}

    public function product(int $productId): ?ProductRuleView
    {
        return $this->productsById[$productId] ?? null;
    }
}
