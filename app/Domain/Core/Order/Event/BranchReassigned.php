<?php

namespace App\Domain\Core\Order\Event;

/**
 * Филиал заказа переназначен. Production отменяет старый тикет и ставит новый.
 */
final readonly class BranchReassigned
{
    public function __construct(
        public int $orderId,
        public int $previousBranchId,
        public int $branchId,
    ) {}
}
