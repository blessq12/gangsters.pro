<?php

namespace App\Application\Order\Contracts;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Enums\OrderSource;
use App\Domain\Order\ValueObjects\CustomerSnapshot;
use App\Domain\Order\ValueObjects\DeliveryInfo;
use App\Domain\Order\ValueObjects\PaymentInfo;

interface OrderPlacementContract
{
    /**
     * @param  array<int, array{product_id: int, quantity: int, final_price_kopecks?: int|null}>  $items
     */
    public function place(
        ?int $clientId,
        CustomerSnapshot $customerSnapshot,
        array $items,
        DeliveryInfo $deliveryInfo,
        PaymentInfo $paymentInfo,
        OrderSource $source = OrderSource::Site,
        int $deliveryFeeKopecks = 0,
        ?array $deliveryPricingSnapshot = null,
    ): Order;
}
