<?php

namespace App\Application\Order\Query;

use App\Services\Order\ComplimentaryItemsResolver;

final class PreviewComplimentaryItemsUseCase
{
    public function __construct(
        private readonly ComplimentaryItemsResolver $resolver,
    ) {
    }

    /**
     * @param array<int, array{product_id: int, quantity: int}> $items
     * @return array{items: array<int, array{rule_id: int, product_id: int, name: string, quantity: int, list_price: int}>}
     */
    public function execute(array $items): array
    {
        return [
            'items' => $this->resolver->resolvePreview($items),
        ];
    }
}
