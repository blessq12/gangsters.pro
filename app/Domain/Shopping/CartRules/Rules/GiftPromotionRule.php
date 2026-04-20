<?php

namespace App\Domain\Shopping\CartRules\Rules;

use App\Domain\Shopping\CartRules\CartLineItem;
use App\Domain\Shopping\CartRules\CartLineOrigin;
use App\Domain\Shopping\CartRules\CartState;
use App\Domain\Shopping\CartRules\RuleEvaluationContext;
use App\Domain\Shopping\CartRules\ShoppingCartRuleInterface;

/**
 * Порог суммы по пользовательским строкам → выбор подарка; выбранный товар как system line с ценой 0 (см. CartPricing).
 */
final class GiftPromotionRule implements ShoppingCartRuleInterface
{
    public const LINE_KEY = 'gift:free_roll';

    public const PROMO_KEY = 'gift_promotion';

    public function __construct(
        private readonly array $options = [],
    ) {}

    public function apply(CartState $state, RuleEvaluationContext $context): CartState
    {
        $threshold = (int) ($this->options['threshold_kopecks'] ?? 0);
        if ($threshold < 1) {
            return $this->clearGift($state);
        }

        $userSubtotal = $this->userSubtotalKopecks($state, $context);
        $eligible = $userSubtotal >= $threshold;

        if (! $eligible) {
            return $this->clearGift($state);
        }

        $candidates = $context->giftCandidateProductIds;
        $selected = $context->selectedGiftProductId;
        $selectedValid = $selected !== null
            && $selected > 0
            && in_array($selected, $candidates, true)
            && $context->product($selected) !== null;

        $kept = $this->systemLinesWithoutGift($state->systemLines);
        $promo = [
            'eligible' => true,
            'threshold_kopecks' => $threshold,
            'user_subtotal_kopecks' => $userSubtotal,
            'candidate_product_ids' => array_values($candidates),
            'selected_product_id' => $selectedValid ? $selected : null,
            'phase' => $selectedValid ? 'gift_applied' : 'select_gift',
        ];

        if (! $selectedValid) {
            return $state->with(
                systemLines: $kept,
                promoState: array_replace($state->promoState, [self::PROMO_KEY => $promo]),
            );
        }

        $kept[] = new CartLineItem(
            $selected,
            1,
            CartLineOrigin::System,
            self::LINE_KEY,
        );

        return $state->with(
            systemLines: $kept,
            promoState: array_replace($state->promoState, [self::PROMO_KEY => $promo]),
        );
    }

    private function clearGift(CartState $state): CartState
    {
        $promo = $state->promoState;
        unset($promo[self::PROMO_KEY]);

        return $state->with(
            systemLines: $this->systemLinesWithoutGift($state->systemLines),
            promoState: $promo,
        );
    }

    /**
     * @param  CartLineItem[]  $systemLines
     * @return CartLineItem[]
     */
    private function systemLinesWithoutGift(array $systemLines): array
    {
        return array_values(array_filter(
            $systemLines,
            static fn (CartLineItem $l) => $l->lineKey !== self::LINE_KEY,
        ));
    }

    private function userSubtotalKopecks(CartState $state, RuleEvaluationContext $context): int
    {
        $sum = 0;
        foreach ($state->userLines as $line) {
            $view = $context->product($line->productId);
            if ($view !== null) {
                $sum += $view->priceKopecks * $line->quantity;
            }
        }

        return $sum;
    }
}
