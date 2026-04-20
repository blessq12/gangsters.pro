<?php

namespace App\Application\Shopping\CartRules;

use App\Domain\Shopping\CartRules\CartLineItem;
use App\Domain\Shopping\CartRules\CartLineOrigin;
use App\Domain\Shopping\CartRules\CartState;
use App\Domain\Shopping\Entities\ShoppingSession;

final class CartStateFactory
{
    public function fromSession(ShoppingSession $session): CartState
    {
        $userLines = [];
        foreach ($session->getCartLines() as $line) {
            $userLines[] = new CartLineItem(
                $line->productId,
                $line->quantity,
                CartLineOrigin::User,
                'user:'.$line->productId,
            );
        }

        return new CartState($userLines, [], [], 0, 0, 0);
    }
}
