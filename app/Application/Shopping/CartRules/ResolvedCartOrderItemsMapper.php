<?php

namespace App\Application\Shopping\CartRules;

use App\Domain\Shopping\CartRules\CartLineItem;
use App\Domain\Shopping\CartRules\CartState;

final class ResolvedCartOrderItemsMapper
{
    /**
     * Строки для оформления заказа: пользовательские + системные, с финальной ценой за единицу.
     *
     * @return array<int, array{product_id: int, quantity: int, final_price_kopecks: int}>
     */
    public static function toOrderPlacementRows(CartState $state): array
    {
        $rows = [];
        foreach (self::allLines($state) as $line) {
            $rows[] = [
                'product_id' => $line->productId,
                'quantity' => $line->quantity,
                'final_price_kopecks' => $line->finalUnitPriceKopecks ?? 0,
            ];
        }

        return $rows;
    }

    /**
     * @return CartLineItem[]
     */
    private static function allLines(CartState $state): array
    {
        return array_merge($state->userLines, $state->systemLines);
    }
}
