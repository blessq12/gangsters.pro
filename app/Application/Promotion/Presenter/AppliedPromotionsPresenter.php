<?php

namespace App\Application\Promotion\Presenter;

final class AppliedPromotionsPresenter
{
    /**
     * @param  array{
     *     gift: array{product_id: int, name: string, price_kopecks: int}|null,
     *     complements: list<array{product_id: int, name: string, quantity: int, price_kopecks: int}>,
     *     free_delivery: bool,
     *     delivery_discount_kopecks: int,
     *     roll_count: int,
     *     complement_entitled: int
     * }  $applied
     * @return array<string, mixed>
     */
    public function present(array $applied): array
    {
        return [
            'gift' => $applied['gift'],
            'complements' => $applied['complements'],
            'free_delivery' => $applied['free_delivery'],
            'delivery_discount_kopecks' => $applied['delivery_discount_kopecks'],
            'roll_count' => $applied['roll_count'],
            'complement_entitled' => $applied['complement_entitled'],
        ];
    }
}
