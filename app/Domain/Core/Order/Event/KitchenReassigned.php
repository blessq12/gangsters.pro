<?php

namespace App\Domain\Core\Order\Event;

/**
 * Кухня заказа переназначена. Production отменяет старый тикет и ставит новый.
 */
final readonly class KitchenReassigned
{
    public function __construct(
        public int $orderId,
        public int $previousKitchenId,
        public int $kitchenId,
    ) {}
}
