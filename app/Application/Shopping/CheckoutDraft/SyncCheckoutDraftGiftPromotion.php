<?php

namespace App\Application\Shopping\CheckoutDraft;

use App\Domain\Shopping\CartRules\CartState;
use App\Domain\Shopping\CartRules\Rules\GiftPromotionRule;
use App\Domain\Shopping\Entities\ShoppingSession;

/**
 * Снимает выбор подарка из checkout_draft, если resolved-корзина не в фазе gift_applied.
 */
final class SyncCheckoutDraftGiftPromotion
{
    public function execute(ShoppingSession $session, CartState $resolved): bool
    {
        $draft = $session->getCheckoutDraft();
        if (! is_array($draft)) {
            return false;
        }

        $promotions = $draft['promotions'] ?? null;
        if (! is_array($promotions) || ! array_key_exists('free_roll_gift_product_id', $promotions)) {
            return false;
        }

        $gift = $resolved->promoState[GiftPromotionRule::PROMO_KEY] ?? null;
        $applied = is_array($gift) && ($gift['phase'] ?? '') === 'gift_applied';

        if ($applied) {
            return false;
        }

        unset($promotions['free_roll_gift_product_id']);
        if ($promotions === []) {
            unset($draft['promotions']);
        } else {
            $draft['promotions'] = $promotions;
        }

        $session->setCheckoutDraft($draft === [] ? null : $draft);

        return true;
    }
}
