<?php

namespace App\Domain\Core\Order\Event;

/**
 * Состав позиций заказа изменён — Production пересобирает тикет по снимку.
 *
 * @phpstan-type ProductionLineSnapshot array{product_id: int, name: string, quantity: int}
 */
final readonly class OrderLinesChanged
{
    /**
     * @param  list<ProductionLineSnapshot>  $productionLines
     */
    public function __construct(
        public int $orderId,
        public array $productionLines,
    ) {}
}
