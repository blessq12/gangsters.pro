<?php

namespace App\Application\Shopping\CartRules;

use App\Domain\Shopping\CartRules\CartState;
use App\Domain\Shopping\CartRules\Contracts\ProductRuleViewProviderInterface;
use App\Domain\Shopping\CartRules\RuleEvaluationContext;
use App\Domain\Shopping\Entities\ShoppingSession;

/**
 * Собирает ID товаров для правил и строит неизменяемый контекст каталога.
 */
final class RuleEvaluationContextBuilder
{
    public function __construct(
        private readonly ProductRuleViewProviderInterface $productViews,
    ) {}

    public function build(ShoppingSession $session, CartState $state): RuleEvaluationContext
    {
        $ids = [];
        foreach ($state->userLines as $line) {
            $ids[] = $line->productId;
        }

        $complementProductIds = [];
        foreach ($this->enabledRuleDefinitions() as $def) {
            if (($def['id'] ?? '') === 'complement') {
                $complementProductIds = $this->productViews->findActiveComplementSetProductIds();
                foreach ($complementProductIds as $productId) {
                    $ids[] = $productId;
                }
                break;
            }
        }

        foreach ($this->enabledRuleDefinitions() as $def) {
            if (($def['id'] ?? '') !== 'gift_promotion') {
                continue;
            }
            foreach ($this->productViews->findActiveGiftCandidateProductIds() as $pid) {
                $ids[] = $pid;
            }
            break;
        }

        $draft = $session->getCheckoutDraft() ?? [];
        $promotions = isset($draft['promotions']) && is_array($draft['promotions']) ? $draft['promotions'] : [];
        $selectedGift = isset($promotions['free_roll_gift_product_id'])
            ? (int) $promotions['free_roll_gift_product_id']
            : 0;
        if ($selectedGift > 0) {
            $ids[] = $selectedGift;
        }

        $ids = array_values(array_unique(array_filter($ids, static fn (int $i) => $i > 0)));
        $views = $this->productViews->getViewsByProductIds($ids);

        $giftCandidateIds = [];
        foreach ($this->enabledRuleDefinitions() as $def) {
            if (($def['id'] ?? '') !== 'gift_promotion') {
                continue;
            }
            $giftCandidateIds = $this->productViews->findActiveGiftCandidateProductIds();
            break;
        }

        $giftCandidateIds = array_values(array_unique(array_filter($giftCandidateIds, static fn (int $i) => $i > 0)));
        $giftCandidateIds = array_values(array_intersect($giftCandidateIds, array_keys($views)));

        return new RuleEvaluationContext(
            $views,
            $complementProductIds,
            $selectedGift > 0 ? $selectedGift : null,
            $giftCandidateIds,
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function enabledRuleDefinitions(): array
    {
        $out = [];
        foreach ((array) config('shopping_cart_rules.rules', []) as $def) {
            if (! ($def['enabled'] ?? true)) {
                continue;
            }
            $out[] = $def;
        }

        return $out;
    }
}
