<?php

namespace App\Domain\Core\Order\Event;

/**
 * Заказ подтверждён и назначен на филиал.
 * Production создаёт KitchenTicket со снимком позиций; Payment — поручение.
 *
 * @phpstan-type ProductionLineSnapshot array{product_id: int, name: string, quantity: int}
 */
final readonly class OrderConfirmed
{
    /**
     * @param  list<ProductionLineSnapshot>  $productionLines
     */
    public function __construct(
        public int $orderId,
        public int $branchId,
        public int $amountKopecks,
        public string $paymentMethod,
        public ?int $changeFromRubles,
        public array $productionLines,
    ) {}
}
