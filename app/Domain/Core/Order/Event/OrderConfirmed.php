<?php

namespace App\Domain\Core\Order\Event;

/**
 * Заказ подтверждён и назначен на филиал.
 * Production создаёт KitchenTicket; Payment — платёжное поручение.
 */
final readonly class OrderConfirmed
{
    public function __construct(
        public int $orderId,
        public int $branchId,
        public int $amountKopecks,
        public string $paymentMethod,
        public ?int $changeFromRubles,
    ) {}
}
