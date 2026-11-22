<?php

namespace App\Domain\Core\Order\Event;

/**
 * Филиал заказа переназначен. Production отменяет старый тикет и ставит новый.
 *
 * @phpstan-type ProductionLineSnapshot array{product_id: int, name: string, quantity: int}
 */
final readonly class BranchReassigned
{
    /**
     * @param  list<ProductionLineSnapshot>  $productionLines
     */
    public function __construct(
        public int $orderId,
        public int $previousBranchId,
        public int $branchId,
        public array $productionLines,
    ) {}
}
