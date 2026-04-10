<?php

namespace App\Application\Order\Query;

use App\Services\Order\ComplimentaryItemsResolver;
use App\Support\Money;

final class PreviewComplimentaryItemsUseCase
{
    public function __construct(
        private readonly ComplimentaryItemsResolver $resolver,
    ) {}

    /**
     * @param  array<int, array{product_id: int, quantity: int}>  $items
     * @return array{items: array<int, array{rule_id: int, product_id: int, name: string, quantity: int, list_price: float}>}
     */
    public function execute(array $items): array
    {
        $raw = $this->resolver->resolvePreview($items);
        $itemsOut = array_map(
            static fn (array $row): array => [
                ...$row,
                'list_price' => Money::kopecksToApiRubles((int) $row['list_price']),
            ],
            $raw,
        );

        return ['items' => $itemsOut];
    }
}
