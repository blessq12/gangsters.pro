<?php

namespace App\Domain\Order\Event;

/**
 * Заказ оформлен. Несёт условия оплаты из чекаута — их владелец Payment, не Order.
 */
final readonly class OrderPlaced
{
    public function __construct(
        public int $orderId,
        public int $amountKopecks,
        public string $paymentMethod,
        public ?int $changeFromRubles,
    ) {}
}
