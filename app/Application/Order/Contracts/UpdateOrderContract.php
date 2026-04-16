<?php

namespace App\Application\Order\Contracts;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\ValueObjects\CustomerSnapshot;
use App\Domain\Order\ValueObjects\DeliveryInfo;
use App\Domain\Order\ValueObjects\PaymentInfo;

interface UpdateOrderContract
{
    /**
     * @param  array<int, array{product_id: int, quantity: int}>  $items
     */
    public function update(
        Order $existing,
        ?int $clientId,
        CustomerSnapshot $customerSnapshot,
        array $items,
        ?DeliveryInfo $deliveryInfo,
        ?PaymentInfo $paymentInfo,
    ): Order;
}
